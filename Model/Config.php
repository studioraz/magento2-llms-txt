<?php

declare(strict_types=1);

namespace SR\LlmsTxt\Model;

use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\Cms\Helper\Page;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private const XML_PATH_ENABLED = 'llmstxt/general/enabled';
    private const XML_PATH_ADDITIONAL_CONTENT = 'llmstxt/general/additional_content';
    private const XML_PATH_PAGES = 'llmstxt/general/pages';
    private const XML_PATH_CATEGORIES = 'llmstxt/general/categories';
    private const XML_PATH_PRODUCT_LIMIT = 'llmstxt/general/product_limit';
    private const XML_PATH_AMASTY_FAQ = 'llmstxt/general/amasty_faq_enabled';
    private const XML_PATH_SOCIAL_LINKS = 'llmstxt/general/social_media_links';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly SerializerInterface $serializer
    ) {
    }

    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getAdditionalContent(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_ADDITIONAL_CONTENT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getStoreName(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            'general/store_information/name',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getConfigValue(string $path, int $storeId): string
    {
        return (string)($this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId) ?: '');
    }

    public function getPages(?int $storeId = null): array
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_PAGES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $value ? explode(',', (string)$value) : [];
    }

    public function getCategories(?int $storeId = null): array
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_CATEGORIES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $value ? explode(',', (string)$value) : [];
    }

    public function getHomePageIdentifier(int $storeId): string
    {
        // Use the configured home page identifier for the store
        $identifier = $this->getConfigValue(Page::XML_PATH_HOME_PAGE, $storeId);
        return $identifier ?: 'home';
    }

    public function getProductLimit(?int $storeId = null): int
    {
        $limit = $this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_LIMIT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return (int)($limit ?: 10);
    }

    public function isAmastyFaqEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_AMASTY_FAQ,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getSocialLinks(?int $storeId = null): array
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_SOCIAL_LINKS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (!$value) {
            return [];
        }

        try {
            $decoded = $this->serializer->unserialize($value);
            if (!is_array($decoded)) {
                return [];
            }

            $result = [];
            foreach ($decoded as $item) {
                if (isset($item['name'], $item['url']) && !empty($item['name']) && !empty($item['url'])) {
                    $result[] = [
                        'name' => trim((string)$item['name']),
                        'url' => trim((string)$item['url']),
                    ];
                }
            }
            return $result;
        } catch (\Exception $e) {
            return [];
        }
    }
}
