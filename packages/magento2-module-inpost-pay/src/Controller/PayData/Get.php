<?php
declare(strict_types=1);

namespace InPost\InPostPay\Controller\PayData;

use InPost\InPostPay\Service\ApiConnector\BindingBasket;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Base64Json;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Psr\Log\LoggerInterface;

class Get implements HttpPostActionInterface
{
    public const DEFAULT_DATE_FORMAT = "Y-m-d\TH:i:s.000\Z";

    private readonly ManagerInterface $messageManager;
    private readonly RequestInterface $request;

    public function __construct(
        Context $context,
        private readonly BindingBasket $bindingBasket,
        private readonly CheckoutSession $checkoutSession,
        private readonly Validator $formKeyValidator,
        private readonly SerializerInterface $serializer,
        private readonly Base64Json $base64serializer,
        private readonly CartRepositoryInterface $quoteRepository,
        private readonly TimezoneInterface $localeDate,
        private readonly JsonFactory $jsonFactory,
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
                $this->quoteRepository->getActive((int)$quote->getId());

                $params = $this->serializer->unserialize($this->request->getContent());

                if (isset($params['browser']) && isset($params['binding_place'])) {
                    $browser = $this->base64serializer->unserialize($params['browser']);
                    $browserData = [];
                    if (is_array($browser)) {
                        $browserData = $this->prepareBrowserData($browser);
                    }

                    $result = $this->bindingBasket->bindBasket(
                        (int)$quote->getId(),
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
            "data_time" => $this->localeDate->date()->format(self::DEFAULT_DATE_FORMAT),
            "location" => "-",
            "customer_ip" => $this->request->getClientIp(),
            "port" => $this->request->getServer('SERVER_PORT')
        ];
    }
}
