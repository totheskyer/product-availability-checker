<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PAC_Deactivator {
	public static function deactivate() {
		delete_option( 'pac_version' );
	}
}
