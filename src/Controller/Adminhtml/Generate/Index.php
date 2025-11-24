<?php

declare(strict_types=1);

namespace SR\LlmsTxt\Controller\Adminhtml\Generate;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\StoreManagerInterface;
use SR\LlmsTxt\Model\Generator;
use SR\LlmsTxt\Model\StoreDataCollector;

// TODO for the future generating purposes.
class Index implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'SR_LlmsTxt::config';

    public function __construct(
        private readonly RequestInterface $request,
        private readonly JsonFactory $resultJsonFactory,
        private readonly StoreDataCollector $storeDataCollector,
        private readonly Generator $generator,
        private readonly StoreManagerInterface $storeManager
    ) {}

    public function execute(): Json
    {
        $result = $this->resultJsonFactory->create();

        try {
            $storeId = (int) $this->request->getParam('store', 0);
            if ($storeId === 0) {
                $storeId = (int) $this->storeManager->getDefaultStoreView()->getId();
            }

            // Collect store data
            $content = $this->generator->generate($storeId);

            return $result->setData([
                'success' => true,
                'content' => $content,
                'message' => __('Content generated successfully!')
            ]);

        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => __('Generation failed: %1', $e->getMessage())
            ]);
        }
    }
}
