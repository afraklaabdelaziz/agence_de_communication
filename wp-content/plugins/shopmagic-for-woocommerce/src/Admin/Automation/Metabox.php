<?php

declare(strict_types=1);

namespace WPDesk\ShopMagic\Admin\Automation;

use ShopMagicVendor\WPDesk\View\Renderer\Renderer;

interface Metabox {

	/**
	 * Initially shows metabox as HTML markup. Method returns nothing, yet it should output content directly.
	 *
	 * @param \WP_Post $post
	 *
	 * @return void
	 */
	public function render( \WP_Post $post );

	/**
	 * @param string $post_id
	 *
	 * @return void
	 */
	public function save( string $post_id );

	/**
	 * Initialize metabox class with registering admin UI, firing hooks and preparing renderer. All of the actions will
	 * be needed to properly instantiate metabox object.
	 * Each metabox will need renderer, so while initializing object, store renderer instance in object variable.
	 *
	 * @param Renderer $renderer
	 *
	 * @return void
	 */
	public function initialize( Renderer $renderer );
}
