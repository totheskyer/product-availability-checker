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
	exit; // Exit if accessed directly
}

define( 'PAC_VERSION', '1.1.0' );
define( 'PAC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PAC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once PAC_PLUGIN_DIR . 'includes/class-pac-activator.php';
require_once PAC_PLUGIN_DIR . 'includes/class-pac-deactivator.php';
require_once PAC_PLUGIN_DIR . 'includes/class-pac-loader.php';
require_once PAC_PLUGIN_DIR . 'includes/class-pac-admin.php';
require_once PAC_PLUGIN_DIR . 'includes/class-pac-frontend.php';

register_activation_hook( __FILE__, array( 'PAC_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'PAC_Deactivator', 'deactivate' ) );

function run_pac_plugin() {
	$plugin = new PAC_Loader();
	$plugin->run();
}
run_pac_plugin();
