<?php

declare(strict_types=1);

namespace SR\LlmsTxt\Model;

use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\Cms\Helper\Page;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private const XML_PATH_ENABLED = 'llmstxt/general/enabled';
    private const XML_PATH_MANUAL_CONTENT = 'llmstxt/general/manual_content';
    private const XML_PATH_USE_MANUAL_CONTENT = 'llmstxt/general/use_manual_content';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
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

    public function getManualContent(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_MANUAL_CONTENT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function useManualContent(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_USE_MANUAL_CONTENT,
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
        return (string)($this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId) ?: '');
    }

    public function getCategoryUrlSuffix(int $storeId): string
    {
        return $this->getConfigValue(CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX, $storeId);
    }

    public function getProductUrlSuffix(int $storeId): string
    {
        return $this->getConfigValue(ProductUrlPathGenerator::XML_PATH_PRODUCT_URL_SUFFIX, $storeId);
    }

    public function getHomePageIdentifier(int $storeId): string
    {
        // Use the configured home page identifier for the store
        $identifier = $this->getConfigValue(Page::XML_PATH_HOME_PAGE, $storeId);
        return $identifier ?: 'home';
    }

    public function getNoRouteIdentifier(int $storeId): string
    {
        $identifier = $this->getConfigValue(Page::XML_PATH_NO_ROUTE_PAGE, $storeId);
        return $identifier ?: 'no-route';
    }

    public function getNoCookiesIdentifier(int $storeId): string
    {
        $identifier = $this->getConfigValue(Page::XML_PATH_NO_COOKIES_PAGE, $storeId);
        return $identifier ?: 'no-cookies';
    }
}
