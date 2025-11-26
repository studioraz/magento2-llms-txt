<?php

declare(strict_types=1);

namespace SR\LlmsTxt\Block;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\FileSystem;
use Magento\Framework\View\TemplateEnginePool;
use Magento\Store\Model\StoreManagerInterface;
use SR\LlmsTxt\Model\Config;
use SR\LlmsTxt\ViewModel\GeneratedData;

class Data extends AbstractBlock
{
    public function __construct(
        Context $context,
        private readonly Config $config,
        private readonly StoreManagerInterface $storeManager,
        private readonly GeneratedData $generatedData,
        private readonly State $appState,
        private readonly \Magento\Store\Model\App\Emulation $appEmulation,
        private readonly FileSystem $fileSystem,
        private readonly DesignInterface $design,
        private readonly ThemeProviderInterface $themeProvider,
        private readonly TemplateEnginePool $templateEnginePool,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    protected function _toHtml(): string
    {
        try {
            $storeId = (int) $this->storeManager->getStore()->getId();

            if (!$this->config->isEnabled($storeId) && $this->appState->getAreaCode() === Area::AREA_FRONTEND) {
                return '';
            }

            if ($this->config->useManualContent($storeId) && $this->appState->getAreaCode() === Area::AREA_FRONTEND) {
                return $this->config->getManualContent($storeId) . PHP_EOL;
            }

            if ($this->appState->getAreaCode() === Area::AREA_ADMINHTML) {
                $storeId = $this->getData('adminhtml_store_id') ?? $storeId;
                // Emulate frontend environment to render the template correctly.
                $this->appEmulation->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, true);

                $themeId = $this->design->getConfigurationDesignTheme('frontend');
                $theme = $this->themeProvider->getThemeById($themeId);
                $name = $this->fileSystem->getTemplateFileName('SR_LlmsTxt::llmstxt-generated.phtml', ['themeModel' => $theme]);
                $extension = pathinfo($name, PATHINFO_EXTENSION);
                $templateEngine = $this->templateEnginePool->get($extension);
                // Pass generated data to the template
                $this->setData('generatedData', $this->generatedData);
                // Render the template
                $html = $templateEngine->render($this, $name);

                $this->appEmulation->stopEnvironmentEmulation();
                return $html;
            }

            // If the mode is auto-generating FE content will be rendered here
            // \Magento\Framework\View\Result\Page::renderPage
            return '';
        } catch (\Exception $e) {
            return '';
        }
    }
}
