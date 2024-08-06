<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\Swagger;

use InPost\InPostPay\Model\Registry\SwaggerRegistry;
use InPost\InPostPay\Plugin\Authorization\SignatureValidationPolicyPlugin;
use Magento\Framework\Webapi\Authorization;

class SwaggerAllowAclResourcePlugin
{
    /**
     * @param SwaggerRegistry $swaggerRegistry
     */
    public function __construct(
        private readonly SwaggerRegistry $swaggerRegistry,
    ) {
    }

    /**
     * @param Authorization $subject
     * @param string[] $aclResources
     * @param bool $result
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsAllowed(Authorization $subject, bool $result, array $aclResources): bool
    {
        if ($result === false && $this->swaggerRegistry->isAllowed()) {
            if (in_array(SignatureValidationPolicyPlugin::INPOST_PAY_SIGNATURE_VALIDATED_RESOURCE, $aclResources)) {
                $result = true;
            }
        }

        return $result;
    }
}
