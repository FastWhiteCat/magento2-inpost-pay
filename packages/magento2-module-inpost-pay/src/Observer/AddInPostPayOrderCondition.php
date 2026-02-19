<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer;

use InPost\InPostPay\Model\Rule\Condition\IsInPostPayOrder;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AddInPostPayOrderCondition implements ObserverInterface
{
    /**
     * Add the InPost Pay Order condition to the list of available conditions
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $additional = $observer->getData('additional');

        if ($additional instanceof DataObject) {
            $conditions = (array)($additional->getData('conditions') ?: []);

            $conditions[] = [
                'label' => __('InPost Pay'),
                'value' => [
                    [
                        'value' => IsInPostPayOrder::class,
                        'label' => __('Only orders placed with InPost Pay Mobile App'),
                    ],
                ],
            ];

            $additional->setData('conditions', $conditions);
        }
    }
}
