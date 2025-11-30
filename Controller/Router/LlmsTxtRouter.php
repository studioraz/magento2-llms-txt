<?php

declare(strict_types=1);

namespace SR\LlmsTxt\Controller\Router;

use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Route\ConfigInterface;
use Magento\Framework\App\Router\ActionList;
use Magento\Framework\App\RouterInterface;
use SR\LlmsTxt\Model\Config;

class LlmsTxtRouter implements RouterInterface
{
    public function __construct(
        private readonly ActionFactory $actionFactory,
        private readonly ActionList $actionList,
        private readonly ConfigInterface $routeConfig,
        private readonly Config $llmsConfig
    ) {
    }

    public function match(RequestInterface $request): ?ActionInterface
    {
        $identifier = trim($request->getPathInfo(), '/');

        if ($identifier !== 'llms.txt') {
            return null;
        }


        $modules = $this->routeConfig->getModulesByFrontName('llmstxt');
        if (empty($modules)) {
            return null;
        }

        // Use SR\LlmsTxt\Model\Config API to check if enabled
        if (!$this->llmsConfig->isEnabled()) {
            return null; // Native router will return 404
        }

        $actionClassName = $this->actionList->get($modules[0], null, 'index', 'index');

        return $this->actionFactory->create($actionClassName);
    }
}
