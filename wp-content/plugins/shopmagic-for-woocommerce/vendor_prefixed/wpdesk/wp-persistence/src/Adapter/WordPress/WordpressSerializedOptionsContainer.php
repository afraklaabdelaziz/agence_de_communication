<?php

namespace ShopMagicVendor\WPDesk\Persistence\Adapter\WordPress;

use ShopMagicVendor\WPDesk\Persistence\ElementNotExistsException;
use ShopMagicVendor\WPDesk\Persistence\FallbackFromGetTrait;
use ShopMagicVendor\WPDesk\Persistence\PersistentContainer;
/**
 * Can store data using WordPress options. All data are stored in one option as serialized data.
 *
 * @package WPDesk\Persistence\Wordpress
 */
final class WordpressSerializedOptionsContainer implements \ShopMagicVendor\WPDesk\Persistence\PersistentContainer
{
    use FallbackFromGetTrait;
    /** @var string */
    private $option_name;
    /** @var array */
    private $option_value;
    /**
     * @param string option_name
     */
    public function __construct($option_name)
    {
        $this->option_name = $option_name;
    }
    /**
     * @return void
     */
    private function refresh_value()
    {
        $this->option_value = \get_option($this->option_name, []);
    }
    public function set(string $id, $value)
    {
        $this->refresh_value();
        $this->option_value[$id] = $value;
        \update_option($this->option_name, $this->option_value);
    }
    public function delete(string $id)
    {
        $this->refresh_value();
        unset($this->option_value[$id]);
        \update_option($this->option_name, $this->option_value);
    }
    public function has($key) : bool
    {
        $this->refresh_value();
        return isset($this->option_value[$key]);
    }
    public function get($id)
    {
        $this->refresh_value();
        if (!isset($this->option_value[$id])) {
            throw new \ShopMagicVendor\WPDesk\Persistence\ElementNotExistsException(\sprintf('Element %s not exists!', $id));
        }
        return $this->option_value[$id];
    }
}
