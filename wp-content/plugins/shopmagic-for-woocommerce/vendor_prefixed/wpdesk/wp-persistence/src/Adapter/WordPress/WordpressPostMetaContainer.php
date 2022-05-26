<?php

namespace ShopMagicVendor\WPDesk\Persistence\Adapter\WordPress;

use ShopMagicVendor\WPDesk\Persistence\ElementNotExistsException;
use ShopMagicVendor\WPDesk\Persistence\FallbackFromGetTrait;
use ShopMagicVendor\WPDesk\Persistence\PersistentContainer;
/**
 * Can store data using WordPress Post metadata.
 * Warning: stored string '' is considered unset.
 *
 * @package WPDesk\Persistence\Wordpress
 */
final class WordpressPostMetaContainer implements \ShopMagicVendor\WPDesk\Persistence\PersistentContainer
{
    use FallbackFromGetTrait;
    /** @var int */
    private $post_id;
    /**
     * @param int $post_id Id of the WordPress post.
     */
    public function __construct(int $post_id)
    {
        $this->post_id = $post_id;
    }
    public function set(string $key, $value)
    {
        if ($value !== null) {
            \update_post_meta($this->post_id, $key, $value);
        } else {
            $this->delete($key);
        }
    }
    public function get($key)
    {
        $meta = \get_post_meta($this->post_id, $key);
        if (\count($meta) === 0) {
            throw new \ShopMagicVendor\WPDesk\Persistence\ElementNotExistsException(\sprintf('Element %s not exists!', $key));
        }
        return $meta[0];
    }
    /**
     * @param string $id
     *
     * @return bool
     */
    public function has($id) : bool
    {
        return \metadata_exists('post', $this->post_id, $id);
    }
    public function delete(string $key)
    {
        \delete_post_meta($this->post_id, $key);
    }
}
