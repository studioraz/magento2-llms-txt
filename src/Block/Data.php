<?php

declare(strict_types=1);

namespace SR\LlmsTxt\Block;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Context;
use Magento\Store\Model\StoreManagerInterface;
use SR\LlmsTxt\Model\Config;
use SR\LlmsTxt\Model\Generator;

class Data extends AbstractBlock
{
    public function __construct(
        Context $context,
        private readonly Generator $generator,
        private readonly Config $config,
        private readonly StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    protected function _toHtml(): string
    {
        try {
            $storeId = (int) $this->storeManager->getStore()->getId();

            // Check if module is enabled
            if (!$this->config->isEnabled($storeId)) {
                return '';
            }

            return $this->generator->generate($storeId) . PHP_EOL;
        } catch (\Exception $e) {
            return '';
        }
    }
}
