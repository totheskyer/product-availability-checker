<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PAC_Activator {
	public static function activate() {
		add_option( 'pac_version', PAC_VERSION );
	}
}
