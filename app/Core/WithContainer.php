<?php

namespace App\Core;

use Psr\Container\ContainerInterface;

trait WithContainer
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }
}
