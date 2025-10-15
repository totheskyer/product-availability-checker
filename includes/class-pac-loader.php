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
		// Admin (settings page under WooCommerce) - only if admin is available
		if ( $this->admin ) {
			add_filter( 'woocommerce_get_settings_pages', array( $this->admin, 'register_settings_page' ) );

			// Admin CRUD handlers (add/edit/delete entries)
			add_action( 'admin_post_pac_add_zip', array( $this->admin, 'handle_add_zip' ) );
			add_action( 'admin_post_pac_edit_zip', array( $this->admin, 'handle_edit_zip' ) );
			add_action( 'admin_post_pac_delete_zip', array( $this->admin, 'handle_delete_zip' ) );
		}

		// Frontend: enqueue, render UI, AJAX
		add_action( 'wp_enqueue_scripts', array( $this->frontend, 'enqueue_assets' ) );
		add_action( 'woocommerce_before_add_to_cart_form', array( $this->frontend, 'render_zip_checker' ), 8 );
		add_action( 'wp_ajax_pac_check_zip', array( $this->frontend, 'ajax_check_zip' ) );
		add_action( 'wp_ajax_nopriv_pac_check_zip', array( $this->frontend, 'ajax_check_zip' ) );
	}
}
