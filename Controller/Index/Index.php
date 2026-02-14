<?php

declare(strict_types=1);

namespace SR\LlmsTxt\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use SR\LlmsTxt\Model\Config;

class Index extends Action implements HttpGetActionInterface
{
    public function __construct(
        Context $context,
        private readonly PageFactory $llmsResultPageFactory

    ) {
        parent::__construct($context);
    }

    public function execute(): Page
    {
        /** @var Page $resultPage */
        $resultPage = $this->llmsResultPageFactory->create(true);
        $resultPage->addHandle('llmstxt_index_index');
        $resultPage->setHeader('Content-Type', 'text/plain; charset=utf-8');

        return $resultPage;
    }
}
