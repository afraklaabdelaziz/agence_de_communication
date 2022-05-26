<?php

namespace ShopMagicVendor\WPDesk\Persistence\Decorator;

use ShopMagicVendor\WPDesk\Persistence\ElementNotExistsException;
use ShopMagicVendor\WPDesk\Persistence\PersistentContainer;
use ShopMagicVendor\WPDesk\Persistence\AllDataAccessContainer;
/**
 * You can use this class to delay write access to any PersistenceContainer and save it as single value.
 *
 * @package WPDesk\Persistence
 */
final class DelaySinglePersistentContainer extends \ShopMagicVendor\WPDesk\Persistence\Decorator\DelayPersistentContainer implements \ShopMagicVendor\WPDesk\Persistence\AllDataAccessContainer
{
    /**
     * Key where the data will be saved.
     *
     * @var string
     */
    private $key;
    public function __construct(\ShopMagicVendor\WPDesk\Persistence\PersistentContainer $container, string $key)
    {
        parent::__construct($container);
        $this->key = $key;
    }
    public function get($id)
    {
        if (isset($this->changed[$id]) && $this->changed[$id]) {
            if (isset($this->internal_data[$id])) {
                return $this->internal_data[$id];
            }
        } else {
            $data = \unserialize($this->container->get($this->key));
            if (\is_array($data) && isset($data[$id])) {
                return $data[$id];
            }
        }
        throw new \ShopMagicVendor\WPDesk\Persistence\ElementNotExistsException(\sprintf('Element %s not exists!', $id));
    }
    public function has($id) : bool
    {
        if (isset($this->changed[$id]) && $this->changed[$id]) {
            return isset($this->internal_data[$id]);
        }
        if ($this->container->has($this->key)) {
            $data = \unserialize($this->container->get($this->key));
            return \is_array($data) && isset($data[$id]);
        }
        return \false;
    }
    public function save()
    {
        if ($this->is_changed()) {
            $this->container->set($this->key, \serialize($this->internal_data));
            $this->reset();
        }
    }
    public function get_all() : array
    {
        if (!empty($this->changed)) {
            if (!empty($this->internal_data)) {
                return $this->internal_data;
            }
        } else {
            $data = \unserialize($this->container->get($this->key));
            if (!empty($data)) {
                return $data;
            }
        }
        return array();
    }
}
