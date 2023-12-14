<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\Swagger;

use InPost\InPostPay\Model\Registry\SwaggerRegistry;
use Magento\Webapi\Model\Rest\Swagger\Generator;

class SwaggerGetListOfServicesPlugin
{
    /**
     * @param SwaggerRegistry $swaggerRegistry
     */
    public function __construct(
        private readonly SwaggerRegistry $swaggerRegistry,
    ) {
    }

    /**
     * @param Generator $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetListOfServices(Generator $subject): void
    {
        $this->swaggerRegistry->setIsAllowed(true);
    }
}
