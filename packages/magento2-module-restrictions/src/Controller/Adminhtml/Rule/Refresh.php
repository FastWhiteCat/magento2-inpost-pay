<?php

declare(strict_types=1);

namespace InPost\Restrictions\Controller\Adminhtml\Rule;

use Exception;
use InPost\Restrictions\Api\Data\RestrictionsRuleInterface;
use InPost\Restrictions\Controller\Adminhtml\RestrictionsController;
use InPost\Restrictions\Model\RestrictionsRuleRepository;
use InPost\Restrictions\Service\ReloadRestrictionRulesProducts;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Refresh extends RestrictionsController implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'InPost_Restrictions::rule_save';

    private const RULES_TYPES_TO_REFRESH = [
        0,
        RestrictionsRuleInterface::APPLIES_TO_COURIER,
        RestrictionsRuleInterface::APPLIES_TO_APM,
        RestrictionsRuleInterface::APPLIES_TO_BOTH,
    ];

    public function __construct(
        PageFactory $pageFactory,
        RedirectFactory $redirectFactory,
        AuthorizationInterface $authorization,
        RequestInterface $request,
        ManagerInterface $messageManager,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly RestrictionsRuleRepository $restrictionsRuleRepository,
        private readonly ReloadRestrictionRulesProducts $reloadRestrictionRulesProducts
    ) {
        parent::__construct($pageFactory, $redirectFactory, $authorization, $request, $messageManager);
    }

    public function execute(): ResultInterface
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->redirectFactory->create();

        try {
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $restrictionsRules = $this->restrictionsRuleRepository->getList($searchCriteria);
            $websiteIds = [];
            foreach ($restrictionsRules->getItems() as $restrictionsRule) {
                if ($restrictionsRule instanceof RestrictionsRuleInterface) {
                    $websiteIds[] = $restrictionsRule->getWebsiteId();
                    $this->reloadRestrictionRulesProducts->execute($restrictionsRule);
                }
            }

            if (!empty($websiteIds)) {
                $websiteIds = array_unique($websiteIds);
                foreach ($websiteIds as $websiteId) {
                    foreach (self::RULES_TYPES_TO_REFRESH as $appliesTo) {
                        $this->reloadRestrictionRulesProducts
                            ->warmUpSystemCacheForRestrictionsList($websiteId, $appliesTo);
                    }
                }
            }

            $this->messageManager->addSuccessMessage(
                __('You have refreshed the InPost Restriction Rules.')->render()
            );

            return $resultRedirect->setPath('*/*/');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Something went wrong while refreshing Restrictions Rules.')->render()
            );
        }

        return $resultRedirect->setPath('*/*/');
    }
}
