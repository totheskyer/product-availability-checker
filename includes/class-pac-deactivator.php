<?php
if ( ! defined( 'ABSPATH' ) ) { 
	exit; 
}

class PAC_Deactivator {
	public static function deactivate() {
		// Keep options to preserve rules on deactivation.
		update_option( 'pac_version', PAC_VERSION );
	}
}
