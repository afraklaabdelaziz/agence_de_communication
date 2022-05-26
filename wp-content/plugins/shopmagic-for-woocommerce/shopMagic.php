<?php
/**
Plugin Name: ShopMagic for WooCommerce
Plugin URI: https://shopmagic.app/
Description: Marketing Automation and Custom Email Designer for WooCommerce
Version: 2.37.7
Author: WP Desk
Author URI: https://shopmagic.app/
Text Domain: shopmagic-for-woocommerce
Domain Path: /lang/
Requires at least: 5.0
Tested up to: 6.0
WC requires at least: 4.8
WC tested up to: 6.5.1
Requires PHP: 7.0

Copyright 2020 WP Desk Ltd.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/* THESE VARIABLES CAN BE CHANGED AUTOMATICALLY */
$plugin_version = '2.37.7';

if ( ! defined( 'SHOPMAGIC_VERSION' ) ) {
	define( 'SHOPMAGIC_VERSION', $plugin_version );
}

$plugin_name        = 'ShopMagic for WooCommerce';
$plugin_class_name  = '\WPDesk\ShopMagic\Plugin';
$plugin_text_domain = 'shopmagic-for-woocommerce';
$product_id         = 'ShopMagic for WooCommerce';
$plugin_file        = __FILE__;
$plugin_dir         = __DIR__;

$requirements = [
	'php'     => '7.0',
	'wp'      => '5.0',
	'plugins' => [
		[
			'name'      => 'woocommerce/woocommerce.php',
			'nice_name' => 'WooCommerce',
			'version'   => '4.8',
		],
	],
];

require __DIR__ . '/vendor_prefixed/wpdesk/wp-plugin-flow/src/plugin-init-php52-free.php';
require __DIR__ . '/vendor_prefixed/league/csv/src/functions.php';
