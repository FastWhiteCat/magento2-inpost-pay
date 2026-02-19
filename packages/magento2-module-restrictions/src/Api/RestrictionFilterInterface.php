<?php

declare(strict_types=1);

namespace InPost\Restrictions\Api;

use Magento\Framework\App\ScopeInterface;
use Magento\Framework\DB\Select;

interface RestrictionFilterInterface
{
    /**
     * Apply restriction filter to existing collection select object
     *
     * @param Select $select
     * @param ScopeInterface $scope
     * @return void
     */
    public function apply(Select $select, ScopeInterface $scope): void;
}
