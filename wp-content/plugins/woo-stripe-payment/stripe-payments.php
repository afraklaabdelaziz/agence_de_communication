<?php
/**
 * Plugin Name: Payment Plugins for Stripe WooCommerce
 * Plugin URI: https://docs.paymentplugins.com/wc-stripe/config/
 * Description: Accept Credit Cards, Google Pay, Apple Pay, ACH, Klarna and more using Stripe.
 * Version: 3.3.19
 * Author: Payment Plugins, support@paymentplugins.com
 * Text Domain: woo-stripe-payment
 * Domain Path: /i18n/languages/
 * Tested up to: 5.9
 * WC requires at least: 3.0.0
 * WC tested up to: 6.4
 */
defined( 'ABSPATH' ) || exit ();

function wc_stripe_php_version_notice() {
	$message = sprintf( __( 'Your PHP version is %s but Stripe requires version 5.6+.', 'woo-stripe-payment' ), PHP_VERSION );
	echo '<div class="notice notice-error"><p style="font-size: 16px">' . $message . '</p></div>';
}

if ( version_compare( PHP_VERSION, '5.6', '<' ) ) {
	add_action( 'admin_init', 'wc_stripe_php_version_notice' );

	return;
}

define( 'WC_STRIPE_PLUGIN_FILE_PATH', plugin_dir_path( __FILE__ ) );
define( 'WC_STRIPE_ASSETS', plugin_dir_url( __FILE__ ) . 'assets/' );
define( 'WC_STRIPE_PLUGIN_NAME', plugin_basename( __FILE__ ) );

require_once( WC_STRIPE_PLUGIN_FILE_PATH . 'vendor/autoload.php' );
// include main plugin file.
require_once( WC_STRIPE_PLUGIN_FILE_PATH . 'includes/class-stripe.php' );