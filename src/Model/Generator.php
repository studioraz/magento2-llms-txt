<?php

declare(strict_types=1);

namespace SR\LlmsTxt\Model;

use Magento\Store\Model\StoreManagerInterface;

class Generator
{
    public function __construct(
        private readonly Config $config,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    public function generate(?int $storeId = null): string
    {
        $storeId = $storeId ?? (int) $this->storeManager->getStore()->getId();

        return $this->config->getManualContent($storeId);
    }
}
