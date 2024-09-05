<?php

declare(strict_types=1);

namespace InPost\Restrictions\Controller\Adminhtml\Rule;

use InPost\Restrictions\Controller\Adminhtml\RestrictionsController;
use InPost\Restrictions\Service\RefreshRestrictionRulesService;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;

class Refresh extends RestrictionsController implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'InPost_Restrictions::rule_save';

    public function __construct(
        PageFactory $pageFactory,
        RedirectFactory $redirectFactory,
        AuthorizationInterface $authorization,
        RequestInterface $request,
        ManagerInterface $messageManager,
        private readonly RefreshRestrictionRulesService $refreshRestrictionRulesService
    ) {
        parent::__construct($pageFactory, $redirectFactory, $authorization, $request, $messageManager);
    }

    public function execute(): ResultInterface
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->redirectFactory->create();

        try {
            $this->refreshRestrictionRulesService->execute();
            $this->messageManager->addSuccessMessage(
                __('You have refreshed the InPost Restriction Rules.')->render()
            );

            return $resultRedirect->setPath('*/*/');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect->setPath('*/*/');
    }
}
