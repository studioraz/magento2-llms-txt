<?php

declare(strict_types=1);

namespace SR\LlmsTxt\Model;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Module\Manager;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class StoreDataCollector
{
    public function __construct(
        private readonly StoreManagerInterface     $storeManager,
        private readonly CategoryCollectionFactory $categoryCollectionFactory,
        private readonly ProductCollectionFactory  $productCollectionFactory,
        private readonly PageRepositoryInterface   $pageRepository,
        private readonly SearchCriteriaBuilder     $searchCriteriaBuilder,
        private readonly Manager                   $moduleManager,
        private readonly Config                    $config,
        private readonly ?array $dependenciesArray = null
    ) {
    }

    public function collect(int $storeId): array
    {
        $baseUrl = $this->getBaseUrl($storeId);

        return [
            'store_name' => $this->storeManager->getStore($storeId)->getName(),
            'store_url' => $baseUrl,
            'home_page_meta_title' => $this->getHomePageMetaTitle($storeId),
            'categories' => $this->collectCategories($storeId, $baseUrl),
            'products' => $this->collectProducts($storeId, $baseUrl),
            'product_feed_urls' => $this->getProductFeedUrls($storeId),
            'cms_pages' => $this->collectCmsPages($storeId, $baseUrl),
            'point_of_sales' => $this->getPointOfSales($storeId, $baseUrl),
        ];
    }

    protected function collectCategories(int $storeId, string $baseUrl): array
    {
        $categorySuffix = $this->config->getCategoryUrlSuffix($storeId);
        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToSelect(['name', 'url_key', 'description'])
            ->addAttributeToFilter('is_active', 1)
            ->addAttributeToFilter('level', 2) // Only top-level categories
            ->setStoreId($storeId)
            ->setOrder('position', 'ASC');

        $categories = [];
        foreach ($collection as $category) {
            $categories[] = [
                'name' => (string)$category->getName(),
                'url' => $baseUrl . $category->getUrlKey() . $categorySuffix,
                'description' => strip_tags((string)$category->getDescription()),
            ];
        }

        return $categories;
    }

    protected function collectProducts(int $storeId, string $baseUrl): array
    {
        $productSuffix = $this->config->getProductUrlSuffix($storeId);
        // Get 10 bestsellers for the current store
        $bestsellerCollection = \Magento\Framework\App\ObjectManager::getInstance()->create(\Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection::class);
        $bestsellerCollection->setModel(\Magento\Catalog\Model\Product::class)
            ->addStoreFilter($storeId)
            ->setPeriod('year')
            ->setPageSize(10)
            ->setCurPage(1);

        $productIds = [];
        foreach ($bestsellerCollection as $bestseller) {
            $productIds[] = $bestseller->getProductId();
        }
        if (empty($productIds)) {
            return [];
        }

        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect(['name', 'url_key', 'short_description'])
            ->addAttributeToFilter('entity_id', ['in' => $productIds])
            ->addAttributeToFilter('status', 1)
            ->addAttributeToFilter('visibility', ['in' => [2, 3, 4]])
            ->setStoreId($storeId)
            ->addStoreFilter($storeId);

        $products = [];
        foreach ($collection as $product) {
            $products[] = [
                'name' => (string)$product->getName(),
                'url' => $baseUrl . $product->getUrlKey() . $productSuffix,
                'description' => strip_tags((string)$product->getShortDescription()),
            ];
        }

        return $products;
    }

    protected function collectCmsPages(int $storeId, string $baseUrl): array
    {
        $homePageIdentifier = $this->config->getHomePageIdentifier($storeId);
        $noRouteIdentifier = $this->config->getNoRouteIdentifier($storeId);
        $noCookiesIdentifier = $this->config->getNoCookiesIdentifier($storeId);
        $skipIdentifiers = [$homePageIdentifier, $noRouteIdentifier, $noCookiesIdentifier];
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('is_active', 1)
            ->addFilter('store_id', [$storeId, 0], 'in')
            ->create();

        try {
            $pages = $this->pageRepository->getList($searchCriteria)->getItems();
        } catch (NoSuchEntityException $e) {
            return [];
        }

        $cmsPages = [];

        foreach ($pages as $page) {
            $identifier = (string)$page->getIdentifier();

            if (in_array($identifier, $skipIdentifiers, true)) {
                continue;
            }

            $cmsPages[] = [
                'title' => (string)$page->getTitle(),
                'url' => $baseUrl . $identifier,
                'meta_description' => (string)$page->getMetaDescription(),
            ];
        }
        // Add Contact Us page
        $cmsPages[] = [
            'title' => 'Contact Us',
            'url' => $baseUrl . 'contact',
            'meta_description' => 'Get in touch with us through our Contact Us page.',
        ];

        return $cmsPages;
    }

    protected function getBaseUrl(int $storeId): string
    {
        try {
            return (string)$this->storeManager->getStore($storeId)->getBaseUrl();
        } catch (NoSuchEntityException $e) {
            return '';
        }
    }

    protected function getHomePageMetaTitle(int $storeId): string
    {
        $homePageIdentifier = $this->config->getHomePageIdentifier($storeId);
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('identifier', $homePageIdentifier)
            ->addFilter('is_active', 1)
            ->addFilter('store_id', [$storeId, 0], 'in')
            ->create();
        $pages = $this->pageRepository->getList($searchCriteria)->getItems();
        foreach ($pages as $page) {
            return (string)$page->getMetaTitle();
        }
        return '';
    }

    protected function getProductFeedUrls($storeId): array
    {
        $result = [];
        if (!$this->moduleManager->isEnabled('Mirasvit_Feed') || !isset($this->dependenciesArray)) {
            return $result;
        }

        foreach ($this->dependenciesArray as $dependency) {
            $feedCollection = 'Mirasvit\Feed\Model\ResourceModel\Feed\Collection';
            if ($dependency instanceof $feedCollection) {
                $feedCollection = $dependency;
                break;
            }
        }
        /** @var $feedCollection \Mirasvit\Feed\Model\ResourceModel\Feed\Collection */
        $feedCollection
            ->addFieldToFilter('filename', ['like' => '%product%'])
            ->addFieldToFilter('store_id', $storeId)
            ->addFieldToFilter('is_active', 1);

        /** @var $feed \Mirasvit\Feed\Model\Feed */
        foreach ($feedCollection as $feed) {
            $result[] = $feed->getUrl();
        }

        return $result;
    }

    protected function getPointOfSales($storeId, $baseUrl): array
    {
        $result = [];
        if (!$this->moduleManager->isEnabled('SR_PointOfSale') || !isset($this->dependenciesArray)) {
            return $result;
        }

        foreach ($this->dependenciesArray as $dependency) {
            $posCollection = 'SR\PointOfSale\Model\ResourceModel\PointOfSale\Collection';
            if ($dependency instanceof $posCollection) {
                $posCollection = $dependency;
                break;
            }
        }
        /** @var $posCollection \SR\PointOfSale\Model\ResourceModel\PointOfSale\Collection */
        $posCollection
            ->addStoreFilter($storeId)
            ->addIsActiveFilter();

        /** @var $pos \SR\PointOfSale\Model\PointOfSale */
        foreach ($posCollection as $pos) {
            $addressParts = array_filter([$pos->getAddress(), $pos->getCity()]);

            $result[] = [
                'name' => $pos->getName(),
                'url' => $baseUrl . 'pointofsale/' . $pos->getUrlKey(),
                'address' => implode(', ', $addressParts),
                'opening_hours' => preg_replace('/\R/u', ' ', $pos->getOpeningHours()),
                'phone' => $pos->getTelephone(),
            ];
        }

        return $result;
    }
}
