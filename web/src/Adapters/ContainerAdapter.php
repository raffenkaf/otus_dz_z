<?php

namespace App\Adapters;

use DI\Container;
use Psr\Container\ContainerInterface;

class ContainerAdapter implements ContainerInterface
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function has($name): bool
    {
        return $this->container->has($name);
    }

    public function get(string $id)
    {
        return $this->container->get($id);
    }

    public function make($name, array $parameters = [])
    {

        return $this->container->make($name, $parameters );
    }

    public function injectOn($instance)
    {
        return $this->container->injectOn($instance);
    }

    public function set($name, $value): self
    {
        $this->container->set($name, $value);
        return $this;
    }

    public function call($callable, array $parameters = [])
    {
        return $this->container->call($callable, $parameters);
    }
}