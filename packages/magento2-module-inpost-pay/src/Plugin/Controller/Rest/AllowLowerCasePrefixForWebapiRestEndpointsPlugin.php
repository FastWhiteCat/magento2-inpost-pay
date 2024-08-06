<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\Controller\Rest;

use Magento\Framework\Webapi\Rest\Request;
use Magento\Webapi\Controller\Rest\SynchronousRequestProcessor;

class AllowLowerCasePrefixForWebapiRestEndpointsPlugin
{
    private const IZI_API_PROCESSOR_PATH = '/^\\/v\\d+\\/izi/';

    /**
     * Plugin explanation:
     * Magento native rest API processors require endpoint URIs with uppercase V and digit prefix like: V1/
     * InPostPay requires merchant API to handle lower case v1/ prefix.
     * It is however undesirable to handle every v1 endpoint in magento so patter in this plugin matches v1/izi/
     *
     * @param SynchronousRequestProcessor $subject
     * @param bool $result
     * @param Request $request
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCanProcess(SynchronousRequestProcessor $subject, bool $result, Request $request): bool
    {
        if (!$result) {
            if (preg_match(self::IZI_API_PROCESSOR_PATH, $request->getPathInfo()) === 1) {
                $result = true;
            }
        }

        return $result;
    }
}
