<?php

namespace ShopMagicVendor\WPDesk\Persistence\Adapter\WordPress;

use ShopMagicVendor\WPDesk\Persistence\ElementNotExistsException;
use ShopMagicVendor\WPDesk\Persistence\FallbackFromGetTrait;
use ShopMagicVendor\WPDesk\Persistence\PersistentContainer;
/**
 * Can store data using WordPress options.
 *
 * @internal WARNING: NEVER use this container without SinglePersistentContainer. Huge amount of options can be created.
 *
 * @package WPDesk\Persistence\Wordpress
 */
final class WordpressOptionsContainer implements \ShopMagicVendor\WPDesk\Persistence\PersistentContainer
{
    use FallbackFromGetTrait;
    /** @var string */
    private $namespace;
    /**
     * @param string $namespace Namespace so options in different containers would not conflict.
     */
    public function __construct($namespace = '')
    {
        $this->namespace = $namespace;
    }
    public function set(string $id, $value)
    {
        if ($value === null) {
            $this->delete($id);
        } else {
            \update_option($this->prepare_key_name($id), $value);
        }
    }
    public function delete(string $id)
    {
        \delete_option($this->prepare_key_name($id));
    }
    /**
     * Prepare name for key.
     *
     * @param string $key Key.
     *
     * @return string
     */
    private function prepare_key_name(string $key) : string
    {
        return \sanitize_key($this->namespace . $key);
    }
    public function has($id) : bool
    {
        return \get_option($this->prepare_key_name($id)) !== \false;
    }
    public function get($id)
    {
        $value = \get_option($this->prepare_key_name($id));
        if (\false === $value) {
            throw new \ShopMagicVendor\WPDesk\Persistence\ElementNotExistsException(\sprintf('Element %s not exists!', $id));
        }
        return $value;
    }
}
