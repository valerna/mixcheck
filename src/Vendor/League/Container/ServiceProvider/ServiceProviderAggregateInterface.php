<?php

declare (strict_types=1);
namespace OM4\WooCommerceMixCheck\Vendor\League\Container\ServiceProvider;

use IteratorAggregate;
use OM4\WooCommerceMixCheck\Vendor\League\Container\ContainerAwareInterface;
interface ServiceProviderAggregateInterface extends ContainerAwareInterface, IteratorAggregate
{
    public function add(ServiceProviderInterface $provider) : ServiceProviderAggregateInterface;
    public function provides(string $id) : bool;
    public function register(string $service) : void;
}
