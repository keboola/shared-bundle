<?php

declare(strict_types=1);

namespace Keboola\JobQueueSharedBundle;

use Keboola\JobQueueSharedBundle\DependencyInjection\Compiler\ConfigureDbalRetryProxyPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class KeboolaJobQueueSharedBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ConfigureDbalRetryProxyPass());
    }
}
