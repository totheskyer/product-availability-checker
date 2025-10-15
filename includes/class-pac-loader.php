<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PAC_Loader {

	private $admin;
	private $frontend;

	public function __construct() {
		$this->load_dependencies();
		$this->define_hooks();
	}

	private function load_dependencies() {
		$this->admin 	= new PAC_Admin();
		$this->frontend = new PAC_Frontend();
	}

	private function define_hooks() {
		add_shortcode( 'product_checker', array( $this->frontend, 'display_form' ) );
		add_action( 'wp_enqueue_scripts', array( $this->frontend, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_pac_check_availability', array( $this->frontend, 'ajax_check_availability' ) );
		add_action( 'wp_ajax_nopriv_pac_check_availability', array( $this->frontend, 'ajax_check_availability' ) );
	}

	public function run() {
		// Reserved for future extensions
	}
}
