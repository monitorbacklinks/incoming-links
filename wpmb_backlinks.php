<?php
/*
Plugin Name: Incoming Links
Plugin URI: http://monitorbacklinks.com/blog/incoming-links/
Description: Automatically detects all new incoming links and keeps track of your existing backlinks. 
Author: Monitor Backlinks
Version: 0.9.10b
Author URI: https://monitorbacklinks.com/
*/

/*
 * Include Install action
 */

include_once(dirname(__FILE__) . '/install/install.php');
register_activation_hook(__FILE__, array('WPMB_Install','install') );


/*
 * Include uninstall action
 */
include_once(dirname( __FILE__ ) . '/uninstall/uninstall.php');
register_uninstall_hook(__FILE__, array('WPMB_Uninstall','uninstall') );

/*
 * Include base class(get configs)
 */
include_once(dirname( __FILE__ ) . '/config.php');

/*
 * Get referrers from users
 */
include_once(dirname( __FILE__ ) . '/front/referrers.php');

/*
 * Init cron actions
 */
include_once(dirname( __FILE__ ) . '/front/cron.php');

/*
 * Init email by cron actions
 */
include_once(dirname( __FILE__ ) . '/front/email.php');

/*
 * Include admin functions
 */
include_once(dirname( __FILE__ ) . '/admin/wpmb_backlinks_admin.php');

/*
 * Include font-end Widgets
 */
include_once(dirname( __FILE__ ) . '/widgets/widgets.php');

/*
 * Include l10n
*/
function incoming_links_loadtextdomain() {
	load_plugin_textdomain( 'wpmbil', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action('plugins_loaded', 'incoming_links_loadtextdomain');
