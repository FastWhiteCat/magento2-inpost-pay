<?php

declare(strict_types=1);

namespace InPost\Restrictions\Controller\Adminhtml\Rule;

use Exception;
use InPost\Restrictions\Api\Data\RestrictionsRuleInterface;
use InPost\Restrictions\Controller\Adminhtml\RestrictionsController;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Controller\ResultInterface;
use InPost\Restrictions\Api\RestrictionsRuleRepositoryInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;

class Delete extends RestrictionsController implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'InPost_Restrictions::rule_delete';

    /**
     * @param PageFactory $pageFactory
     * @param RedirectFactory $redirectFactory
     * @param AuthorizationInterface $authorization
     * @param RequestInterface $request
     * @param ManagerInterface $messageManager
     * @param RestrictionsRuleRepositoryInterface $restrictionsRuleRepository
     */
    public function __construct(
        PageFactory $pageFactory,
        RedirectFactory $redirectFactory,
        AuthorizationInterface $authorization,
        RequestInterface $request,
        ManagerInterface $messageManager,
        private readonly RestrictionsRuleRepositoryInterface $restrictionsRuleRepository
    ) {
        parent::__construct($pageFactory, $redirectFactory, $authorization, $request, $messageManager);
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $resultRedirect = $this->redirectFactory->create();
        $ruleId = $this->getRequest()->getParam(RestrictionsRuleInterface::RULE_ID);
        $ruleId = (is_scalar($ruleId)) ? (int)$ruleId : 0;

        try {
            $this->restrictionsRuleRepository->deleteById($ruleId);
            $this->messageManager->addSuccessMessage(
                __('You deleted Restrictions Rule with ID %1.', $ruleId)->render()
            );

            return $resultRedirect->setPath('*/*/');
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            return $resultRedirect->setPath('*/*/edit', [RestrictionsRuleInterface::RULE_ID => $ruleId]);
        }
    }
}
