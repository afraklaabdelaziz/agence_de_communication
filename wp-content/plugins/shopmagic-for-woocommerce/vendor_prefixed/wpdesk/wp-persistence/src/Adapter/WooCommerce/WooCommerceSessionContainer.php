<?php

namespace ShopMagicVendor\WPDesk\Persistence\Adapter\WooCommerce;

use ShopMagicVendor\WPDesk\Persistence\ElementNotExistsException;
use ShopMagicVendor\WPDesk\Persistence\FallbackFromGetTrait;
use ShopMagicVendor\WPDesk\Persistence\PersistentContainer;
/**
 * @package WPDesk\Persistence\WooCommerce
 */
final class WooCommerceSessionContainer implements \ShopMagicVendor\WPDesk\Persistence\PersistentContainer
{
    use FallbackFromGetTrait;
    /** @var \WC_Settings_API */
    private $session;
    public function __construct(\WC_Session $session)
    {
        $this->session = $session;
    }
    public function get($id)
    {
        if (!$this->has($id)) {
            throw new \ShopMagicVendor\WPDesk\Persistence\ElementNotExistsException(\sprintf('Element %s not exists!', $id));
        }
        return $this->session->get($id);
    }
    public function has($id) : bool
    {
        $value = $this->session->get($id);
        return $value !== null;
    }
    public function set(string $id, $value)
    {
        $this->session->set($id, $value);
    }
    public function delete(string $id)
    {
        $this->session->set($id, null);
    }
}
