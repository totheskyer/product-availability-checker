<?php
if ( ! defined( 'ABSPATH' ) ) { 
    exit; 
}

if ( ! class_exists( 'WC_Settings_Page', false ) && class_exists( 'WooCommerce', false ) ) {
	require_once WC_ABSPATH . 'includes/admin/settings/class-wc-settings-page.php';
}

class PAC_Admin extends WC_Settings_Page {

	public function __construct() {
		$this->id    = 'pac_availability';
		$this->label = __( 'Availability', 'product-availability-checker' );
		parent::__construct();
	}

	/**
	 * Register settings page into WooCommerce.
	 *
	 * @param array $pages
	 * @return array
	 */
	public function register_settings_page( $pages ) {
		$pages[] = $this;
		return $pages;
	}

	/**
	 * We don’t use standard WC fields. We render our own table + forms.
	 */
	public function get_settings() {
		return array(); // no auto fields
	}

	/**
	 * Output settings page content.
	 */
	public function output() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to manage availability.', 'product-availability-checker' ) );
		}

		$rules = get_option( PAC_OPTION_KEY, array() );
		if ( ! is_array( $rules ) ) {
			$rules = array();
		}

		// Normalize rules as keyed by ZIP for uniqueness.
		$rules = array_values( $rules ); // ensure indexed for display
		?>
		<div class="wrap woocommerce">
			<h1><?php esc_html_e( 'ZIP-based Availability', 'product-availability-checker' ); ?></h1>

			<h2><?php esc_html_e( 'Existing ZIP Rules', 'product-availability-checker' ); ?></h2>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'ZIP', 'product-availability-checker' ); ?></th>
						<th><?php esc_html_e( 'Status', 'product-availability-checker' ); ?></th>
						<th><?php esc_html_e( 'Custom Message', 'product-availability-checker' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'product-availability-checker' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php if ( empty( $rules ) ) : ?>
					<tr><td colspan="4"><?php esc_html_e( 'No entries yet.', 'product-availability-checker' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $rules as $rule ) : ?>
						<tr>
							<td><code><?php echo esc_html( $rule['zip'] ); ?></code></td>
							<td><?php echo ( 'available' === $rule['status'] ) ? esc_html__( 'Available', 'product-availability-checker' ) : esc_html__( 'Unavailable', 'product-availability-checker' ); ?></td>
							<td><?php echo esc_html( $rule['message'] ); ?></td>
							<td>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block;">
									<?php wp_nonce_field( 'pac_delete_zip', 'pac_nonce' ); ?>
									<input type="hidden" name="action" value="pac_delete_zip">
									<input type="hidden" name="zip" value="<?php echo esc_attr( $rule['zip'] ); ?>">
									<?php submit_button( __( 'Delete', 'product-availability-checker' ), 'delete small', '', false ); ?>
								</form>

								<details style="display:inline-block; margin-left:8px;">
									<summary><?php esc_html_e( 'Edit', 'product-availability-checker' ); ?></summary>
									<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:6px;">
										<?php wp_nonce_field( 'pac_edit_zip', 'pac_nonce' ); ?>
										<input type="hidden" name="action" value="pac_edit_zip">
										<input type="hidden" name="original_zip" value="<?php echo esc_attr( $rule['zip'] ); ?>">

										<p><label>
											<?php esc_html_e( 'ZIP', 'product-availability-checker' ); ?><br>
											<input type="text" name="zip" value="<?php echo esc_attr( $rule['zip'] ); ?>" required>
										</label></p>

										<p><label>
											<?php esc_html_e( 'Status', 'product-availability-checker' ); ?><br>
											<select name="status">
												<option value="available" <?php selected( $rule['status'], 'available' ); ?>><?php esc_html_e( 'Available', 'product-availability-checker' ); ?></option>
												<option value="unavailable" <?php selected( $rule['status'], 'unavailable' ); ?>><?php esc_html_e( 'Unavailable', 'product-availability-checker' ); ?></option>
											</select>
										</label></p>

										<p><label>
											<?php esc_html_e( 'Custom Message (optional)', 'product-availability-checker' ); ?><br>
											<input type="text" name="message" value="<?php echo esc_attr( $rule['message'] ); ?>">
										</label></p>

										<?php submit_button( __( 'Save Changes', 'product-availability-checker' ) ); ?>
									</form>
								</details>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				</tbody>
			</table>

			<h2 style="margin-top:24px;"><?php esc_html_e( 'Add New ZIP Rule', 'product-availability-checker' ); ?></h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'pac_add_zip', 'pac_nonce' ); ?>
				<input type="hidden" name="action" value="pac_add_zip">

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="pac_zip"><?php esc_html_e( 'ZIP', 'product-availability-checker' ); ?></label></th>
						<td><input type="text" id="pac_zip" name="zip" required placeholder="<?php esc_attr_e( 'e.g. 90210', 'product-availability-checker' ); ?>"></td>
					</tr>
					<tr>
						<th scope="row"><label for="pac_status"><?php esc_html_e( 'Status', 'product-availability-checker' ); ?></label></th>
						<td>
							<select id="pac_status" name="status">
								<option value="available"><?php esc_html_e( 'Available', 'product-availability-checker' ); ?></option>
								<option value="unavailable"><?php esc_html_e( 'Unavailable', 'product-availability-checker' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="pac_message"><?php esc_html_e( 'Custom Message', 'product-availability-checker' ); ?></label></th>
						<td><input type="text" id="pac_message" name="message" placeholder="<?php esc_attr_e( 'Optional note shown on product page', 'product-availability-checker' ); ?>"></td>
					</tr>
				</table>

				<?php submit_button( __( 'Add ZIP Rule', 'product-availability-checker' ) ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * CRUD handlers
	 */
	public function handle_add_zip() {
		$this->assert_admin_cap_and_nonce( 'pac_add_zip' );

		$zip     = isset( $_POST['zip'] ) ? sanitize_text_field( wp_unslash( $_POST['zip'] ) ) : '';
		$status  = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'available';
		$message = isset( $_POST['message'] ) ? sanitize_text_field( wp_unslash( $_POST['message'] ) ) : '';

		if ( '' === $zip ) {
			$this->redirect_settings();
		}

		$rules = $this->load_rules_indexed();
		$rules[ $zip ] = array(
			'zip'     => $zip,
			'status'  => ( 'unavailable' === $status ) ? 'unavailable' : 'available',
			'message' => $message,
		);

		update_option( PAC_OPTION_KEY, array_values( $rules ) );
		$this->redirect_settings();
	}

	public function handle_edit_zip() {
		$this->assert_admin_cap_and_nonce( 'pac_edit_zip' );

		$original_zip = isset( $_POST['original_zip'] ) ? sanitize_text_field( wp_unslash( $_POST['original_zip'] ) ) : '';
		$zip          = isset( $_POST['zip'] ) ? sanitize_text_field( wp_unslash( $_POST['zip'] ) ) : '';
		$status       = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'available';
		$message      = isset( $_POST['message'] ) ? sanitize_text_field( wp_unslash( $_POST['message'] ) ) : '';

		if ( '' === $original_zip || '' === $zip ) {
			$this->redirect_settings();
		}

		$rules = $this->load_rules_indexed();
		unset( $rules[ $original_zip ] );
		$rules[ $zip ] = array(
			'zip'     => $zip,
			'status'  => ( 'unavailable' === $status ) ? 'unavailable' : 'available',
			'message' => $message,
		);

		update_option( PAC_OPTION_KEY, array_values( $rules ) );
		$this->redirect_settings();
	}

	public function handle_delete_zip() {
		$this->assert_admin_cap_and_nonce( 'pac_delete_zip' );

		$zip = isset( $_POST['zip'] ) ? sanitize_text_field( wp_unslash( $_POST['zip'] ) ) : '';
		if ( '' === $zip ) {
			$this->redirect_settings();
		}

		$rules = $this->load_rules_indexed();
		if ( isset( $rules[ $zip ] ) ) {
			unset( $rules[ $zip ] );
			update_option( PAC_OPTION_KEY, array_values( $rules ) );
		}

		$this->redirect_settings();
	}

	/**
	 * Utilities
	 */
	private function assert_admin_cap_and_nonce( $nonce_action ) {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'product-availability-checker' ) );
		}
		check_admin_referer( $nonce_action, 'pac_nonce' );
	}

	private function redirect_settings() {
		wp_safe_redirect( admin_url( 'admin.php?page=wc-settings&tab=pac_availability' ) );
		exit;
	}

	private function load_rules_indexed() {
		$rules = get_option( PAC_OPTION_KEY, array() );
		if ( ! is_array( $rules ) ) {
			$rules = array();
		}
		$indexed = array();
		foreach ( $rules as $rule ) {
			if ( empty( $rule['zip'] ) ) { continue; }
			$indexed[ $rule['zip'] ] = array(
				'zip'     => sanitize_text_field( $rule['zip'] ),
				'status'  => ( isset( $rule['status'] ) && 'unavailable' === $rule['status'] ) ? 'unavailable' : 'available',
				'message' => isset( $rule['message'] ) ? sanitize_text_field( $rule['message'] ) : '',
			);
		}
		return $indexed;
	}
}
