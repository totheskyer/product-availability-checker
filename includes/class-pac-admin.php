<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PAC_Admin {
	public function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have permission to access this page.', 'product-availability-checker' ) );
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Product Availability Checker Settings', 'product-availability-checker' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'pac_settings_group' );
				do_settings_sections( 'pac-admin' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}
