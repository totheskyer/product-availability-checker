<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PAC_Admin {
	const OPTION_KEY = 'pac_rules';
	const NONCE_ACTION = 'pac_availability_save';

	public function register_hooks() {
		// Add the tab to WooCommerce settings
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ), 50 );
		// Render the tab content
		add_action( 'woocommerce_settings_tabs_pac_availability', array( $this, 'render_settings_tab' ) );
		// Handle form submission
		add_action( 'woocommerce_update_options_pac_availability', array( $this, 'handle_form_submit' ) );
	}

	public function add_settings_tab( $tabs ) {
		$tabs['pac_availability'] = esc_html__( 'PAC Availability', 'product-availability-checker' );
		return $tabs;
	}

	public function render_settings_tab() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'product-availability-checker' ) );
		}

		$rules = $this->get_rules();
		?>
		<h2><?php echo esc_html__( 'ZIP Code Availability', 'product-availability-checker' ); ?></h2>
		<p><?php echo esc_html__( 'Manage availability by individual ZIP codes. Each entry can be marked Available or Unavailable, with an optional custom message.', 'product-availability-checker' ); ?></p>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=pac_availability' ) ); ?>">
			<?php wp_nonce_field( self::NONCE_ACTION, '_pac_nonce' ); ?>
			<?php wp_nonce_field( 'woocommerce-settings' ); ?>
			<table class="widefat fixed striped">
				<thead>
					<tr>
						<th style="width: 20%;"><?php echo esc_html__( 'ZIP Code', 'product-availability-checker' ); ?></th>
						<th style="width: 15%;"><?php echo esc_html__( 'Status', 'product-availability-checker' ); ?></th>
						<th><?php echo esc_html__( 'Custom Message (optional)', 'product-availability-checker' ); ?></th>
						<th style="width: 10%;"><?php echo esc_html__( 'Actions', 'product-availability-checker' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! empty( $rules ) ) : $i = 0; foreach ( $rules as $zip => $data ) : ?>
					<tr>
						<td>
							<input type="text" name="rules[<?php echo esc_attr( $i ); ?>][zip]" value="<?php echo esc_attr( $zip ); ?>" maxlength="20" class="regular-text" />
						</td>
						<td>
							<select name="rules[<?php echo esc_attr( $i ); ?>][status]">
								<option value="available" <?php selected( isset( $data['status'] ) ? $data['status'] : '', 'available' ); ?>><?php echo esc_html__( 'Available', 'product-availability-checker' ); ?></option>
								<option value="unavailable" <?php selected( isset( $data['status'] ) ? $data['status'] : '', 'unavailable' ); ?>><?php echo esc_html__( 'Unavailable', 'product-availability-checker' ); ?></option>
							</select>
						</td>
						<td>
							<input type="text" name="rules[<?php echo esc_attr( $i ); ?>][message]" value="<?php echo esc_attr( isset( $data['message'] ) ? $data['message'] : '' ); ?>" class="regular-text" />
						</td>
						<td>
							<button type="button" class="button button-secondary" onclick="this.closest('tr').remove();"><?php echo esc_html__( 'Delete', 'product-availability-checker' ); ?></button>
						</td>
					</tr>
					<?php $i++; endforeach; endif; ?>
					<tr class="new-row-template" style="display:none;">
						<td>
							<input type="text" name="rules[__INDEX__][zip]" value="" maxlength="20" class="regular-text" />
						</td>
						<td>
							<select name="rules[__INDEX__][status]">
								<option value="available"><?php echo esc_html__( 'Available', 'product-availability-checker' ); ?></option>
								<option value="unavailable"><?php echo esc_html__( 'Unavailable', 'product-availability-checker' ); ?></option>
							</select>
						</td>
						<td>
							<input type="text" name="rules[__INDEX__][message]" value="" class="regular-text" />
						</td>
						<td>
							<button type="button" class="button button-secondary" onclick="this.closest('tr').remove();"><?php echo esc_html__( 'Delete', 'product-availability-checker' ); ?></button>
						</td>
					</tr>
				</tbody>
			</table>
			<p>
				<button type="button" class="button" onclick="pacAddRow()"><?php echo esc_html__( 'Add Row', 'product-availability-checker' ); ?></button>
			</p>
			<p class="submit">
				<button type="submit" class="button-primary" name="save" value="1"><?php echo esc_html__( 'Save changes', 'product-availability-checker' ); ?></button>
			</p>
		</form>
		
		<script type="text/javascript">
		var pacRowIndex = <?php echo isset( $i ) ? $i : 0; ?>;
		function pacAddRow() {
			var template = document.querySelector('.new-row-template');
			if ( template ) {
				var tbody = template.closest('tbody');
				var newRow = template.cloneNode(true);
				newRow.style.display = '';
				newRow.classList.remove('new-row-template');
				newRow.innerHTML = newRow.innerHTML.replace(/__INDEX__/g, pacRowIndex++);
				tbody.insertBefore(newRow, template);
			}
		}
		</script>
		<?php
	}

	public function handle_form_submit() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		if ( ! isset( $_POST['_pac_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_pac_nonce'] ) ), self::NONCE_ACTION ) ) {
			return;
		}

		$rules_input = isset( $_POST['rules'] ) && is_array( $_POST['rules'] ) ? $_POST['rules'] : array();
		$sanitized = array();

		foreach ( $rules_input as $row ) {
			$zip = isset( $row['zip'] ) ? sanitize_text_field( wp_unslash( $row['zip'] ) ) : '';
			$zip = strtoupper( preg_replace( '/[^A-Za-z0-9\- ]/', '', $zip ) );
			$zip = substr( $zip, 0, 20 );
			if ( '' === $zip ) {
				continue;
			}

			$status = isset( $row['status'] ) ? sanitize_text_field( wp_unslash( $row['status'] ) ) : 'available';
			$status = in_array( $status, array( 'available', 'unavailable' ), true ) ? $status : 'available';

			$message = isset( $row['message'] ) ? sanitize_text_field( wp_unslash( $row['message'] ) ) : '';
			$message = substr( $message, 0, 200 );

			$sanitized[ $zip ] = array(
				'status'  => $status,
				'message' => $message,
			);
		}

		update_option( self::OPTION_KEY, $sanitized, false );
	}

	protected function get_rules() {
		$rules = get_option( self::OPTION_KEY, array() );
		return is_array( $rules ) ? $rules : array();
	}
}
