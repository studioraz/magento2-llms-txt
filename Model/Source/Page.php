<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SR\LlmsTxt\Model\Source;

use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Option\ArrayInterface;
use Magento\Store\Model\StoreManagerInterface;

class Page implements ArrayInterface
{
    /**
     * @var array|null
     */
    private $options;

    public function __construct(
        private readonly CollectionFactory $collectionFactory,
        private readonly RequestInterface $request,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    public function toOptionArray(): array
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $collection = $this->collectionFactory->create();
        $collection->addStoreFilter($this->getScopeStoreIds());

        $this->options = $collection->toOptionIdArray();
        return $this->options;
    }

    private function getScopeStoreIds(): array
    {
        $storeIds = [0]; // Always include admin/global store

        // Check if we're in store scope
        if ($storeParam = $this->request->getParam('store')) {
            $storeIds[] = (int) $storeParam;
            return array_values(array_unique($storeIds));
        }

        // Check if we're in website scope - get default store ID
        if ($websiteParam = $this->request->getParam('website')) {
            try {
                $website = $this->storeManager->getWebsite((int) $websiteParam);
                $defaultStore = $website->getDefaultStore();
                if ($defaultStore) {
                    $storeIds[] = (int) $defaultStore->getId();
                }
            } catch (NoSuchEntityException $e) {
                // Fallback to just admin store
            }
        }

        return array_values(array_unique($storeIds));
    }
}
