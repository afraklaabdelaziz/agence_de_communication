<?php

namespace ShopMagicVendor\WPDesk\Persistence;

/**
 * Container that persists values only after save method is used.
 *
 * @package WPDesk\Persistence
 */
interface DeferredPersistentContainer extends \ShopMagicVendor\WPDesk\Persistence\PersistentContainer
{
    /**
     * Save changed data.
     *
     * @return void
     */
    public function save();
    /**
     * Is there any new data to save.
     *
     * @return bool
     */
    public function is_changed() : bool;
    /**
     * Reset data to last saved values. If remote repository is used the data can be retrived from it.
     *
     * @return void
     */
    public function reset();
}
