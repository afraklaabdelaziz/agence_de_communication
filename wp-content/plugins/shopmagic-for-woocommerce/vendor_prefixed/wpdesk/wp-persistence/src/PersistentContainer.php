<?php

namespace ShopMagicVendor\WPDesk\Persistence;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
/**
 * Container that you can use to save some values.
 * When class require only read capabilities use ContainerInterface. When requires to write use this interface.
 *
 * @package WPDesk\Persistence
 */
interface PersistentContainer extends \Psr\Container\ContainerInterface
{
    /**
     * Similar to ::get but throws no exception when element has not been found.
     * When no value has been found the fallback is returned.
     *
     * @param string $id Identifier of the entry to look for.
     * @param mixed $fallback
     *
     * @return mixed Entry.
     *
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     */
    public function get_fallback(string $id, $fallback = null);
    /**
     * Set value for a given key.
     *
     * @param string $id Identifier of the entry to look for.
     * @param array|int|string|float $value Value should not be an object or callable.
     *
     * @return void
     */
    public function set(string $id, $value);
    /**
     * Clear value from a given key.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return void
     */
    public function delete(string $id);
    /**
     * @inheritDoc
     */
    public function has($id) : bool;
}
