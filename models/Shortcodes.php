<?php

class Gecka_Submenu_Shortcodes {
	
	public function __construct()  {
		
		add_shortcode( 'submenu', 	array( $this, 'submenu') );
				
	}
	
	public function submenu ($Attributes) {

		$Menu = new Gecka_Submenu_Submenu();
		return $Menu->Get($Attributes);
		
	}
	
}