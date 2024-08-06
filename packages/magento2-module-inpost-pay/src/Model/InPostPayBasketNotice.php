<?php
declare(strict_types=1);

namespace InPost\InPostPay\Model;

use InPost\InPostPay\Api\Data\InPostPayBasketNoticeInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;

class InPostPayBasketNotice extends AbstractModel implements InPostPayBasketNoticeInterface
{
    public function _construct(): void
    {
        $this->_init(ResourceModel\InPostPayBasketNotice::class);
    }

    public function getBasketNoticeId(): ?int
    {
        $id = ($this->hasData(self::BASKET_NOTICE_ID)) ? $this->getData(self::BASKET_NOTICE_ID) : null;

        return ($id && is_scalar($id)) ? (int)$id : null;
    }

    public function setBasketNoticeId(int $basketNoticeId): InPostPayBasketNoticeInterface
    {
        return $this->setData(self::BASKET_NOTICE_ID, $basketNoticeId);
    }

    public function getInPostPayQuoteId(): int
    {
        $id = ($this->hasData(self::INPOST_PAY_QUOTE_ID)) ? $this->getData(self::INPOST_PAY_QUOTE_ID) : null;

        if ($id && is_scalar($id)) {
            return (int)$id;
        }

        throw new LocalizedException(__('Invalid InPost Pay Quote ID value.'));
    }

    public function setInPostPayQuoteId(int $inPostPayQuoteId): InPostPayBasketNoticeInterface
    {
        return $this->setData(self::INPOST_PAY_QUOTE_ID, $inPostPayQuoteId);
    }

    public function getType(): string
    {
        $type = ($this->hasData(self::TYPE)) ? $this->getData(self::TYPE) : '';

        return ($type && is_scalar($type)) ? (string)$type : 'null';
    }

    public function setType(string $type): InPostPayBasketNoticeInterface
    {
        return $this->setData(self::TYPE, $type);
    }

    public function getDescription(): string
    {
        $description = ($this->hasData(self::DESCRIPTION)) ? $this->getData(self::DESCRIPTION) : '';

        return ($description && is_scalar($description)) ? (string)$description : 'null';
    }

    public function setDescription(string $description): InPostPayBasketNoticeInterface
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    public function getIsSent(): bool
    {
        return (bool)$this->getData(self::IS_SENT);
    }

    public function setIsSent(bool $isSent): InPostPayBasketNoticeInterface
    {
        return $this->setData(self::IS_SENT, $isSent);
    }

    public function getCreatedAt(): string
    {
        $createdAt = $this->getData(self::CREATED_AT);

        if ($createdAt && is_scalar($createdAt)) {
            return (string)$createdAt;
        }

        throw new LocalizedException(__('Invalid InPost Pay Quote created at value.'));
    }

    public function getUpdatedAt(): string
    {
        $updatedAt = $this->getData(self::UPDATED_AT);

        if ($updatedAt && is_scalar($updatedAt)) {
            return (string)$updatedAt;
        }

        throw new LocalizedException(__('Invalid InPost Pay Quote updated at value.'));
    }
}
