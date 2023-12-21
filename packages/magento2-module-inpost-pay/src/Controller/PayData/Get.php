<?php
declare(strict_types=1);

namespace InPost\InPostPay\Controller\PayData;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Enum\InPostBasketStatus;
use InPost\InPostPay\Service\ApiConnector\BasketBindingCheck;
use InPost\InPostPay\Service\ApiConnector\BasketBindingCreate;
use InPost\InPostPay\Service\ApiConnector\CreateOrUpdateBasket;
use InPost\InPostPay\Service\GetBasketId;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Base64Json;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Get implements HttpPostActionInterface
{
    private readonly ManagerInterface $messageManager;
    private readonly RequestInterface $request;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        private readonly BasketBindingCreate $basketBindingCreate,
        private readonly CheckoutSession $checkoutSession,
        private readonly Validator $formKeyValidator,
        private readonly SerializerInterface $serializer,
        private readonly Base64Json $base64serializer,
        private readonly CartRepositoryInterface $quoteRepository,
        private readonly TimezoneInterface $localeDate,
        private readonly JsonFactory $jsonFactory,
        private readonly CreateOrUpdateBasket $createOrUpdateBasket,
        private readonly CookieManagerInterface $cookieManager,
        private readonly GetBasketId $getBasketId,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly BasketBindingCheck $basketBindingCheck,
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        private readonly LoggerInterface $logger
    ) {
        $this->messageManager = $context->getMessageManager();
        $this->request = $context->getRequest();
    }

    public function execute(): \Magento\Framework\Controller\Result\Json
    {
        if (!$this->formKeyValidator->validate($this->request)) {
            $this->messageManager->addErrorMessage(
                __('Your session has expired')->render()
            );
            $data = ['errorMessage' => __('Your session has expired')];

            return $this->jsonFactory->create()->setData($data);
        }

        $data = [];
        try {
            $quote = $this->checkoutSession->getQuote();
            if ($quote->getId()) {
                $quoteId = is_scalar($quote->getId()) ? (int)$quote->getId() : 0;
                $this->quoteRepository->getActive($quoteId);

                // @phpstan-ignore-next-line
                $params = $this->serializer->unserialize($this->request->getContent());

                if (isset($params['browser']) && isset($params['binding_place'])) {

                    if ($basketId = $this->tryBindExistingBasket($quoteId)) {
                        return $this->jsonFactory->create()->setData(['basket_id' => $basketId]);
                    }

                    $browser = $this->base64serializer->unserialize($params['browser']);
                    $browserData = [];
                    if (is_array($browser)) {
                        $browserData = $this->prepareBrowserData($browser);
                    }

                    $result = $this->basketBindingCreate->execute(
                        $quoteId,
                        $params['binding_place'],
                        $browserData,
                        $params['prefix'] ?? null,
                        $params['number'] ?? null
                    );

                    $data = $result->getData();
                }
            }
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
        }

        return $this->jsonFactory->create()->setData($data);
    }

    private function prepareBrowserData(array $browser): array
    {
        return [
            "user_agent" => $browser['user_agent'] ?? '',
            "description" => $browser['description'] ?? '',
            "platform" => $browser['platform'] ?? '',
            "architecture" => $browser['architecture'] ?? '',
            "data_time" => $this->localeDate->date()->format(BasketInterface::INPOST_DATE_FORMAT),
            "location" => "-",
            "customer_ip" => $this->getCustomerIPAddress(),
            // @phpstan-ignore-next-line
            "port" => $this->request->getServer('SERVER_PORT')
        ];
    }

    private function getCustomerIPAddress(): string
    {
        // @phpstan-ignore-next-line
        return current(explode(',', str_replace(' ', '', $this->request->getClientIp())));
    }

    /**
     * @param string $basketId
     * @return InPostPayQuoteInterface
     * @throws LocalizedException
     */
    private function getInPostPayQuoteByBasketId(string $basketId): InPostPayQuoteInterface
    {
        try {
            return $this->inPostPayQuoteRepository->getByBasketId($basketId);
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage());

            throw $e;
        }
    }

    private function tryBindExistingBasket(int $quoteId): ?string
    {
        try {
            if ($browserId = $this->cookieManager->getCookie('BrowserId')) {
                $basketId = $this->getBasketId->get($quoteId, true);
                $quote = $this->cartRepository->get($quoteId);

                if ($quote instanceof Quote && $basketId) {
                    $binding = $this->basketBindingCheck->execute($quoteId);
                    $binding['browser_trusted'] = true;
                    if ($binding && isset($binding['browser_trusted']) && $binding['browser_trusted']) {
                        $this->createOrUpdateBasket->execute($quote, $browserId, $basketId);
                        $binding = $this->basketBindingCheck->execute($quoteId);
                        if ($binding && isset($binding['client_details'])) {
                            if (!isset($binding['status']) || !$binding['status']) {
                                $binding['status'] = InPostBasketStatus::SUCCESS->value;
                            }

                            $this->UpdateInpostPayQuote($browserId, $basketId, $binding);

                            return $basketId;
                        }
                    }
                }
            }
        } catch (LocalizedException $e) {
            $this->logger->error(__('There was a problem pairing basket with browserId'));
        }

        return null;
    }

    private function UpdateInpostPayQuote(string $browserId, string $basketId, array $binding) {
        $inPostPayQuote = $this->getInPostPayQuoteByBasketId($basketId);

        $status = $binding['status'];
        $inpostBasketId = $binding['inpost_basket_id'] ?? '';
        $maskedPhoneNumber = $binding['client_details']['masked_phone_number'] ?? '';
        $phone = $binding['client_details']['phone_number']['phone'] ?? '';
        $countryPrefix = $binding['client_details']['phone_number']['country_prefix'] ?? '';
        $name = $binding['client_details']['name'] ?? '';
        $surname = $binding['client_details']['surname'] ?? '';
        $browserTrusted = $binding['browser_trusted'] ?? false;

        $inPostPayQuote->setStatus($status);
        $inPostPayQuote->setInpostBasketId($inpostBasketId);
        $inPostPayQuote->setMaskedPhoneNumber($maskedPhoneNumber);
        $inPostPayQuote->setPhone($phone);
        $inPostPayQuote->setCountryPrefix($countryPrefix);
        $inPostPayQuote->setName($name);
        $inPostPayQuote->setSurname($surname);
        $inPostPayQuote->setBrowserId($browserId);
        $inPostPayQuote->setBrowserTrusted($browserTrusted);

        $this->inPostPayQuoteRepository->save($inPostPayQuote);
    }
}
