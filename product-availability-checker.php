<?php
/**
 * Plugin Name:       Product Availability Checker
 * Plugin URI:        https://github.com/totheskyer/product-availability-checker
 * Description:       A lightweight plugin to check WooCommerce product shipping availability.
 * Version:           1.0.0
 * Author:            igorter
 * Text Domain:       product-availability-checker
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PAC_VERSION', '1.0.0' );
define( 'PAC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PAC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'PAC_OPTION_KEY', 'pac_zip_rules' ); // stores array of rules

// Dependencies
require_once PAC_PLUGIN_DIR . 'includes/class-pac-activator.php';
require_once PAC_PLUGIN_DIR . 'includes/class-pac-deactivator.php';
require_once PAC_PLUGIN_DIR . 'includes/class-pac-loader.php';
require_once PAC_PLUGIN_DIR . 'includes/class-pac-admin.php';
require_once PAC_PLUGIN_DIR . 'includes/class-pac-frontend.php';

// Hooks
register_activation_hook( __FILE__, array( 'PAC_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'PAC_Deactivator', 'deactivate' ) );

// Bootstrap
function pac_run() {
	$plugin = new PAC_Loader();
	$plugin->run();
}
pac_run();
