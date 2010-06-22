<?php

function gk_submenu ($Options) {
	
	$Menu = new Gecka_Submenu_Submenu($Options);

	if( isset( $Options['echo']) && $Options['echo'] === false ) return $Menu->Get();
	else $Menu->Show();
	
}
add_action( 'submenu', 'gk_submenu', 10, 1 );
add_action( 'gk-submenu', 'gk_submenu', 10, 1 );