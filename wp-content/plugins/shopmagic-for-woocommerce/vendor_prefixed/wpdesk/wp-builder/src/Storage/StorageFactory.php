<?php

namespace ShopMagicVendor\WPDesk\PluginBuilder\Storage;

class StorageFactory
{
    /**
     * @return PluginStorage
     */
    public function create_storage()
    {
        return new \ShopMagicVendor\WPDesk\PluginBuilder\Storage\WordpressFilterStorage();
    }
}
