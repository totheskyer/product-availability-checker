<?php
if ( ! defined( 'ABSPATH' ) ) { 
	exit; 
}

class PAC_Activator {
	public static function activate() {
		if ( false === get_option( PAC_OPTION_KEY, false ) ) {
			add_option( PAC_OPTION_KEY, array() );
		}
		add_option( 'pac_version', PAC_VERSION );
	}
}
