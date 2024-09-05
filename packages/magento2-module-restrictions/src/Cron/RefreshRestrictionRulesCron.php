<?php

declare(strict_types=1);

namespace InPost\Restrictions\Cron;

use InPost\Restrictions\Provider\Config\RestrictionsConfigProvider;
use InPost\Restrictions\Service\RefreshRestrictionRulesService;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class RefreshRestrictionRulesCron
{
    /**
     * @param RefreshRestrictionRulesService $refreshRestrictionRulesService
     * @param RestrictionsConfigProvider $restrictionsConfigProvider
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly RefreshRestrictionRulesService $refreshRestrictionRulesService,
        private readonly RestrictionsConfigProvider $restrictionsConfigProvider,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        if (!$this->restrictionsConfigProvider->isEnabled()) {
            return;
        }

        try {
            $this->logger->debug('CRON Refreshing Restricted Products is about to start...');
            $this->refreshRestrictionRulesService->execute();
            $this->logger->debug('CRON Refreshing Restricted Products has finished!');
        } catch (LocalizedException $e) {
            $this->logger->error(sprintf('CRON Refreshing Restricted Products has failed: %s', $e->getMessage()));
        }
    }
}
