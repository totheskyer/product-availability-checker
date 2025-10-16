<?php
if ( ! defined( 'ABSPATH' ) ) { 
	exit; 
}

class PAC_Loader {

	private $admin;
	private $frontend;

	public function __construct() {
		// Only instantiate admin if WooCommerce is available
		if ( class_exists( 'PAC_Admin' ) ) {
			$this->admin = new PAC_Admin();
		}
		$this->frontend = new PAC_Frontend();
	}

	public function run() {
		// Admin hooks
		if ( $this->admin && is_admin() ) {
			$this->admin->register_hooks();
		}

		// Frontend: enqueue, render UI, AJAX
		add_action( 'wp_enqueue_scripts', array( $this->frontend, 'enqueue_assets' ) );
		add_action( 'woocommerce_before_add_to_cart_form', array( $this->frontend, 'render_zip_checker' ), 8 );
		add_action( 'wp_ajax_pac_check_zip', array( $this->frontend, 'ajax_check_zip' ) );
		add_action( 'wp_ajax_nopriv_pac_check_zip', array( $this->frontend, 'ajax_check_zip' ) );
	}
}
