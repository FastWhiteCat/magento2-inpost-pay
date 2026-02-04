<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model;

use InPost\InPostPay\Api\Data\InPostPayCheckoutAgreementStoreInterface;
use Magento\Framework\Model\AbstractModel;

class InPostPayCheckoutAgreementStore extends AbstractModel implements InPostPayCheckoutAgreementStoreInterface
{
    protected $_eventPrefix = InPostPayCheckoutAgreementStoreInterface::ENTITY_NAME;
    protected $_eventObject = InPostPayCheckoutAgreementStoreInterface::ENTITY_NAME;

    public function _construct(): void
    {
        $this->_init(\InPost\InPostPay\Model\ResourceModel\InPostPayCheckoutAgreementStore::class);
    }

    /**
     * @return int
     */
    public function getAgreementId(): int
    {
        return is_scalar($this->getData(self::AGREEMENT_ID)) ? (int) $this->getData(self::AGREEMENT_ID) : 0;
    }

    /**
     * @param int $agreementId
     * @return void
     */
    public function setAgreementId(int $agreementId): void
    {
        $this->setData(self::AGREEMENT_ID, $agreementId);
    }

    /**
     * @return int
     */
    public function getStoreId(): int
    {
        return is_scalar($this->getData(self::STORE_ID)) ? (int) $this->getData(self::STORE_ID) : 0;
    }

    /**
     * @param int $storeId
     * @return void
     */
    public function setStoreId(int $storeId): void
    {
        $this->setData(self::STORE_ID, $storeId);
    }
}
