<?php
if ( ! defined( 'ABSPATH' ) ) { 
	exit; 
}

class PAC_Frontend {

	public function enqueue_assets() {
		if ( ! is_product() ) {
			return;
		}

		wp_enqueue_style(
			'pac-style',
			PAC_PLUGIN_URL . 'assets/css/pac-style.css',
			array(),
			PAC_VERSION
		);

		wp_enqueue_script(
			'pac-frontend',
			PAC_PLUGIN_URL . 'assets/js/pac-frontend.js',
			array(),
			PAC_VERSION,
			true
		);

		wp_localize_script( 'pac-frontend', 'PAC', array(
			'ajax'       => admin_url( 'admin-ajax.php' ),
			'nonce'      => wp_create_nonce( 'pac_nonce' ),
			'msgAvail'   => __( 'This product is available in your area.', 'product-availability-checker' ),
			'msgUnavail' => __( 'Sorry, this product is not available in your area.', 'product-availability-checker' ),
		) );
	}

	/**
	 * Renders the ZIP checker UI before add-to-cart form.
	 * TODO:
	 * - add a way to check availability for category view
	 * - add a way to check availability for search results
	 * - add a way to check availability for cart view
	 */
	public function render_zip_checker() {
		?>
		<div class="pac-zip-checker" data-pac>
			<label for="pac-zip-input" class="pac-label">
				<?php esc_html_e( 'Enter your ZIP code:', 'product-availability-checker' ); ?>
			</label>
			<div class="pac-row">
				<input type="text" id="pac-zip-input" class="pac-input" placeholder="<?php esc_attr_e( 'e.g. 90210', 'product-availability-checker' ); ?>" />
				<button type="button" id="pac-zip-btn" class="button"><?php esc_html_e( 'Check Availability', 'product-availability-checker' ); ?></button>
			</div>
			<div id="pac-zip-result" class="pac-result" aria-live="polite"></div>
		</div>
		<?php
	}

	/**
	 * AJAX handler for ZIP checks.
	 */
	public function ajax_check_zip() {
		check_ajax_referer( 'pac_nonce', 'nonce' );

		$zip = isset( $_POST['zip'] ) ? sanitize_text_field( wp_unslash( $_POST['zip'] ) ) : '';

		if ( '' === $zip ) {
			wp_send_json_error( array( 'message' => __( 'Please enter a ZIP code.', 'product-availability-checker' ) ) );
		}

		$rules = get_option( PAC_OPTION_KEY, array() );
		if ( ! is_array( $rules ) ) {
			$rules = array();
		}

		// Find rule by exact ZIP match.
		$found = null;
		foreach ( $rules as $rule ) {
			if ( isset( $rule['zip'] ) && $zip === $rule['zip'] ) {
				$found = $rule;
				break;
			}
		}

		if ( null === $found ) {
			// Not specified means treat as available? You can change behavior.
			wp_send_json_success( array(
				'available' => true,
				'message'   => __( 'No restriction found for this ZIP. Available.', 'product-availability-checker' ),
			) );
		}

		$is_available = ( isset( $found['status'] ) && 'unavailable' === $found['status'] ) ? false : true;
		$message      = isset( $found['message'] ) && $found['message'] !== '' ? $found['message'] : ( $is_available
			? __( 'Available in your area.', 'product-availability-checker' )
			: __( 'Unavailable in your area.', 'product-availability-checker' ) );

		wp_send_json_success( array(
			'available' => $is_available,
			'message'   => $message,
		) );
	}
}
