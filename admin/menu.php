<?php
/*
 * Add menu items
 */
add_action( 'admin_menu', 'register_wpmbil_settings_page' );
add_action( 'admin_menu', 'add_wpmbil_menu_bubble' );

function register_wpmbil_settings_page(){
    //add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
        add_menu_page( __( 'Incoming Links','wpmbil'), __( 'Incoming Links','wpmbil'), 'manage_options', 'wpmb-dashboard', array('WPMB_Dashboard','dashboard_page'),plugins_url('/images/incominglinks-icon.png',__FILE__));
    //add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
        add_submenu_page( 'wpmb-dashboard' , __( 'Settings','wpmbil') , __( 'Settings','wpmbil') , 'manage_options', 'wpmb-settings' , array('WPMB_Settings','settings_page') );
}

function add_wpmbil_menu_bubble() {
	global $menu;
	global $wpdb;
	$referrer_count =  $wpdb->query("SELECT id FROM $wpdb->prefix"."backlinks_cron");
	if ( $referrer_count ) {
		foreach ( $menu as $key => $value ) {
			if ( $menu[$key][2] == 'wpmb-dashboard' ) {
				$menu[$key][0] .= "<span class='update-plugins count-1'><span class='update-count'>$referrer_count</span></span>";
				return;
			}
		}
	}
}
