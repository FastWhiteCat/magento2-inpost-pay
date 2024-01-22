<?php

declare(strict_types=1);

namespace InPost\Restrictions\Controller\Adminhtml\Rule;

use Exception;
use InPost\Restrictions\Controller\Adminhtml\RestrictionsController;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use InPost\Restrictions\Api\Data\RestrictionsRuleInterface;
use InPost\Restrictions\Service\RestrictionsRulePersistorService;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends RestrictionsController implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'InPost_Restrictions::rule_save';
    public const RESTRICTION_RULE_KEY = 'inpost_restrictions_rule_key';

    /**
     * @param PageFactory $pageFactory
     * @param RedirectFactory $redirectFactory
     * @param AuthorizationInterface $authorization
     * @param RequestInterface $request
     * @param ManagerInterface $messageManager
     * @param DataPersistorInterface $dataPersistor
     * @param RestrictionsRulePersistorService $restrictionRulePersistorService
     */
    public function __construct(
        PageFactory $pageFactory,
        RedirectFactory $redirectFactory,
        AuthorizationInterface $authorization,
        RequestInterface $request,
        ManagerInterface $messageManager,
        private readonly DataPersistorInterface $dataPersistor,
        private readonly RestrictionsRulePersistorService $restrictionRulePersistorService
    ) {
        parent::__construct($pageFactory, $redirectFactory, $authorization, $request, $messageManager);
    }

    public function execute(): ResultInterface
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->redirectFactory->create();
        // @phpstan-ignore-next-line
        $ruleData = $this->getRequest()->getPostValue();
        $ruleId = $ruleData[RestrictionsRuleInterface::RULE_ID] ?? null;

        if (empty($ruleData)) {
            return $resultRedirect->setPath('*/*/');
        }

        try {
            $this->restrictionRulePersistorService->execute($ruleData);
            $this->messageManager->addSuccessMessage(__('You have saved the InPost Restriction Rule.')->render());
            $this->dataPersistor->clear(self::RESTRICTION_RULE_KEY);

            return $resultRedirect->setPath('*/*/');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Something went wrong while saving Restrictions Rule.')->render()
            );
        }

        $this->dataPersistor->set(self::RESTRICTION_RULE_KEY, $ruleData);

        if ($ruleId) {
            return $resultRedirect->setPath('*/*/edit', ['rule_id' => $ruleId]);
        }

        return $resultRedirect->setPath('*/*/new');
    }
}
