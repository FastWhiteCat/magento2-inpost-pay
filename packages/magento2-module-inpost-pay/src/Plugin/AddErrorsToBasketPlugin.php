<?php
namespace InPost\InPostPay\Plugin;

use InPost\InPostPay\Api\Data\Merchant\Basket\Summary\NoticeInterface;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\Summary\NoticeInterfaceFactory;
use InPost\InPostPay\Model\ResourceModel\InPostPayBasketNotice as InPostPayBasketNoticeResource;
use InPost\InPostPay\Model\ResourceModel\InPostPayQuote as InPostPayQuoteResource;
use InPost\InPostPay\Service\DataTransfer\QuoteToBasketDataTransfer;
use Magento\Quote\Model\Quote;

class AddErrorsToBasketPlugin
{
    public function __construct(
        private readonly InPostPayBasketNoticeResource $inPostPayBasketNotice,
        private readonly InPostPayQuoteResource $inPostPayQuote,
        private readonly NoticeInterfaceFactory $noticeFactory
    ) {
    }

    /**
     * @param QuoteToBasketDataTransfer $subject
     * @param null $result
     * @param Quote $quote
     * @param BasketInterface $basket
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterTransfer(
        QuoteToBasketDataTransfer $subject,
        $result,
        Quote $quote,
        BasketInterface $basket
    ): void {
        $inPostPayQuoteId = $this->inPostPayQuote->getInPostPayQuoteIdByBasketId((string)$basket->getBasketId());
        $errors = $this->inPostPayBasketNotice->getBasketNoticesByInPostPayQuoteId($inPostPayQuoteId);

        if ($errors) {
            $summary = $basket->getSummary();
            $description = array_unique(array_column($errors, 'description'));
            $description = implode(PHP_EOL, $description);

            /** @var NoticeInterface $notice */
            $notice = $this->noticeFactory->create();
            $notice->setType($errors[0]['type']);
            $notice->setDescription($description);

            $summary->setBasketNotice($notice);

            $this->inPostPayBasketNotice->setNoticeAsSent(
                array_column($errors, 'basket_notice_id'),
                $inPostPayQuoteId
            );
        }
    }
}
