<?php

if(is_admin()){
    
    /*
     * Init admin menu
     */
    include_once(dirname( __FILE__ ) . '/menu.php');

    /*
     * Init functions (block domains/ips)
     */
    include_once(dirname( __FILE__ ) . '/block_domains_ips.php');
    /*
     * Init dashboard page
     */
    include_once(dirname( __FILE__ ) . '/dashboard.php');

    /*
     * Init settings page
     */
    include_once(dirname( __FILE__ ) . '/settings_page.php');

    /*
     * Init Dashboard widgets
     */
    include_once(dirname( __FILE__ ) . '/dashboard_widgets.php');
}
