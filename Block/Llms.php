<?php

declare(strict_types=1);

namespace SR\LlmsTxt\Block;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\FileSystem;
use Magento\Framework\View\TemplateEnginePool;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use SR\LlmsTxt\Model\Config;
use SR\LlmsTxt\ViewModel\GeneratedData;

class Llms extends Template
{

}
