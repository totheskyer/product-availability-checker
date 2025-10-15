<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PAC_Frontend {

	public function enqueue_scripts() {
		wp_enqueue_script(
			'pac-frontend',
			PAC_PLUGIN_URL . 'assets/js/pac-frontend.js',
			array(),
			PAC_VERSION,
			true
		);

		wp_localize_script( 'pac-frontend', 'pac_ajax', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'pac_nonce' ),
		) );
	}

	public function display_form() {
		ob_start(); ?>
		
		<form id="pac-form">
			<p>plugin - product availability checker</p>
			<input type="text" id="pac-zip-code" name="zip-code" required placeholder="Enter ZIP code">
			<button type="submit">Check Availability</button>
			<div id="pac-result"></div>
		</form>
		
		<?php
		return ob_get_clean();
	}

	public function ajax_check_availability() {
		check_ajax_referer( 'pac_nonce', 'nonce' );

		$product_id = isset( $_POST['product_id'] ) ? sanitize_text_field( $_POST['product_id'] ) : '';

		if ( empty( $product_id ) ) {
			wp_send_json_error( __( 'Please provide a valid product ID or SKU.', 'product-availability-checker' ) );
		}

		if ( class_exists( 'WC_Product' ) ) {
			$product = wc_get_product( wc_get_product_id_by_sku( $product_id ) ?: $product_id );

			if ( ! $product ) {
				wp_send_json_error( __( 'Product not found.', 'product-availability-checker' ) );
			}

			if ( $product->is_in_stock() ) {
				wp_send_json_success( __( 'Product is available!', 'product-availability-checker' ) );
			} else {
				wp_send_json_success( __( 'Product is out of stock.', 'product-availability-checker' ) );
			}
		} else {
			wp_send_json_error( __( 'WooCommerce is not active.', 'product-availability-checker' ) );
		}
	}
}
