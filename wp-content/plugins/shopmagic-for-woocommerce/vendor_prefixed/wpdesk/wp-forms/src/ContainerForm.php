<?php

namespace ShopMagicVendor\WPDesk\Forms;

use Psr\Container\ContainerInterface;
use ShopMagicVendor\WPDesk\Persistence\PersistentContainer;
/**
 * Persistent container support for forms.
 *
 * @package WPDesk\Forms
 */
interface ContainerForm
{
    /**
     * @param ContainerInterface $data
     *
     * @return void
     */
    public function set_data($data);
    /**
     * Put data from form into a container.
     *
     * @param PersistentContainer $container Target container.
     *
     * @return void
     */
    public function put_data(\ShopMagicVendor\WPDesk\Persistence\PersistentContainer $container);
}
