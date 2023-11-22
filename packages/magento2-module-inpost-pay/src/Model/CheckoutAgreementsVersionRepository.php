<?php
declare(strict_types=1);

namespace InPost\InPostPay\Model;

use InPost\InPostPay\Api\CheckoutAgreementsVersionRepositoryInterface;
use Magento\Framework\App\ResourceConnection;

class CheckoutAgreementsVersionRepository implements CheckoutAgreementsVersionRepositoryInterface
{
    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(private readonly ResourceConnection $resourceConnection)
    {
    }

    /**
     * @inheritdoc
     */
    public function getCheckoutAgreementVersion(int $agreementId): int
    {
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()->from(
            'inpost_pay_checkout_agreement_version',
            'version'
        )->where('agreement_id = ?', $agreementId);

        return (int)$connection->fetchOne($select);
    }

    /**
     * @inheritdoc
     */
    public function save(array $data): void
    {
        $connection = $this->resourceConnection->getConnection();

        $connection->insertOnDuplicate(
            'inpost_pay_checkout_agreement_version',
            $data,
            ['version']
        );
    }

    /**
     * @inheritdoc
     */
    public function getList(array $ids): array
    {
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()->from(
            'inpost_pay_checkout_agreement_version',
            ['agreement_id', 'version']
        );

        if ($ids) {
            $select->where('agreement_id in (?)', $ids);
        }

        return $connection->fetchPairs($select);
    }
}
