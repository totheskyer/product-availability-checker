<?php
if ( ! defined( 'ABSPATH' ) ) { 
	exit; 
}

class PAC_Activator {
	public static function activate() {
		// Check if WooCommerce is active
		if ( ! class_exists( 'WooCommerce' ) ) {
			deactivate_plugins( plugin_basename( PAC_PLUGIN_DIR . 'product-availability-checker.php' ) );
			wp_die(
				esc_html__( 'Product Availability Checker requires WooCommerce to be installed and active.', 'product-availability-checker' ),
				esc_html__( 'Plugin Activation Error', 'product-availability-checker' ),
				array( 'back_link' => true )
			);
		}

		if ( false === get_option( PAC_OPTION_KEY, false ) ) {
			add_option( PAC_OPTION_KEY, array() );
		}
		add_option( 'pac_version', PAC_VERSION );
	}
}
