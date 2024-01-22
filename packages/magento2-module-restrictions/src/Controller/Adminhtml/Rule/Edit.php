<?php

declare(strict_types=1);

namespace InPost\Restrictions\Controller\Adminhtml\Rule;

use InPost\Restrictions\Controller\Adminhtml\RestrictionsController;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;
use InPost\Restrictions\Api\Data\RestrictionsRuleInterface;
use InPost\Restrictions\Api\Data\RestrictionsRuleInterfaceFactory;
use InPost\Restrictions\Api\RestrictionsRuleRepositoryInterface;
use Magento\Framework\App\RequestInterface;

class Edit extends RestrictionsController implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'InPost_Restrictions::rule_save';

    /**
     * @param PageFactory $pageFactory
     * @param RedirectFactory $redirectFactory
     * @param AuthorizationInterface $authorization
     * @param RequestInterface $request
     * @param ManagerInterface $messageManager
     * @param RestrictionsRuleRepositoryInterface $restrictionsRuleRepository
     * @param RestrictionsRuleInterfaceFactory $restrictionsRuleFactory
     */
    public function __construct(
        PageFactory $pageFactory,
        RedirectFactory $redirectFactory,
        AuthorizationInterface $authorization,
        RequestInterface $request,
        ManagerInterface $messageManager,
        private readonly RestrictionsRuleRepositoryInterface $restrictionsRuleRepository,
        private readonly RestrictionsRuleInterfaceFactory $restrictionsRuleFactory
    ) {
        parent::__construct($pageFactory, $redirectFactory, $authorization, $request, $messageManager);
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $ruleId = $this->getRequest()->getParam(RestrictionsRuleInterface::RULE_ID);
        $ruleId = (is_scalar($ruleId)) ? (int)$ruleId : 0;

        try {
            $restrictionsRule = $this->restrictionsRuleRepository->get($ruleId);
        } catch (LocalizedException $e) {
            $restrictionsRule = $this->restrictionsRuleFactory->create();
        }

        $isNew = $restrictionsRule->getRuleId() !== null;

        /** @var Page $resultPage */
        $resultPage = $this->pageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            __('Rule')->render(),
            $isNew ? __('New')->render() : __('Edit')->render()
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Restrictions Rule')->render());
        $resultPage->getConfig()->getTitle()->prepend(
            $isNew ? __('Edit Restriction Rule %1', $ruleId)->render() : __('New Restrictions Rule')->render()
        );

        return $resultPage;
    }
}
