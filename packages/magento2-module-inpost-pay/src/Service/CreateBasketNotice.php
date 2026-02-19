<?php
declare(strict_types=1);

namespace InPost\InPostPay\Service;

use InPost\InPostPay\Api\Data\InPostPayBasketNoticeInterfaceFactory;
use InPost\InPostPay\Api\Data\InPostPayBasketNoticeInterface;
use InPost\InPostPay\Api\InPostPayBasketNoticeRepositoryInterface;
use InPost\InPostPay\Model\ResourceModel\InPostPayQuote;

class CreateBasketNotice
{
    public function __construct(
        private readonly InPostPayBasketNoticeInterfaceFactory $inPostPayBasketNoticeInterfaceFactory,
        private readonly InPostPayBasketNoticeRepositoryInterface $inPostPayBasketNoticeRepository,
        private readonly InPostPayQuote $inPostPayQuoteRepository
    ) {
    }

    public function execute(string $basketId, string $type, string $description): void
    {
        /** @var InPostPayBasketNoticeInterface $notice */
        $notice = $this->inPostPayBasketNoticeInterfaceFactory->create();
        $inPostPayQuoteId = $this->inPostPayQuoteRepository->getInPostPayQuoteIdByBasketId($basketId);
        $notice->setInPostPayQuoteId($inPostPayQuoteId);
        $notice->setType($type);
        $notice->setDescription($description);
        $this->inPostPayBasketNoticeRepository->save($notice);
    }
}
