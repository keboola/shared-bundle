<?php

declare(strict_types=1);

namespace Keboola\JobQueueSharedBundle\DependencyInjection\Compiler;

use Keboola\JobQueueSharedBundle\Database\ConnectionWithRetry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ConfigureDbalRetryProxyPass implements CompilerPassInterface
{
    private const RETRY_PROXY_SERVICE = 'doctrine.dbal.retry_proxy';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(self::RETRY_PROXY_SERVICE)) {
            return;
        }

        $connections = (array) $container->getParameter('doctrine.connections');
        foreach ($connections as $serviceName) {
            $connectionDefinition = $container->getDefinition($serviceName);

            if (!is_a((string) $connectionDefinition->getClass(), ConnectionWithRetry::class, true)) {
                continue;
            }

            $connectionDefinition->addMethodCall('setRetryProxy', [new Reference(self::RETRY_PROXY_SERVICE)]);
        }
    }
}
