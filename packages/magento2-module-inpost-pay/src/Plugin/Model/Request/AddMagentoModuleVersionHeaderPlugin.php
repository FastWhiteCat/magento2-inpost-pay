<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\Model\Request;

use InPost\InPostPay\Api\ApiConnector\RequestInterface;
use InPost\InPostPay\Model\Request;
use InPost\InPostPay\Provider\MagentoModuleVersionProvider;

class AddMagentoModuleVersionHeaderPlugin
{
    /**
     * @param MagentoModuleVersionProvider $magentoModuleVersionProvider
     */
    public function __construct(
        private readonly MagentoModuleVersionProvider $magentoModuleVersionProvider
    ) {
    }

    /**
     * @param Request $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetHeaders(Request $subject, array $result): array
    {
        $result[RequestInterface::PLUGIN_VERSION] = $this->magentoModuleVersionProvider->getVersion();

        return $result;
    }
}
