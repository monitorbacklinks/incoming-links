<?php
if (! class_exists('WPMB_Settings') && class_exists('WPMB_Blocks')) {

    class WPMB_Settings extends WPMB_Blocks
    {

        public $settings;

        function __construct()
        {
            add_action('init', array(
                $this,
                'init'
            ));
        }

        public function init()
        {
            global $WPMB_Config;
            // Init add to block actions
            add_action('wp_ajax_add_block_ip', array(
                $this,
                'addBlockedIp'
            ));
            add_action('wp_ajax_add_block_domain', array(
                $this,
                'addBlockedDomain'
            ));
            add_action('wp_ajax_add_block_referrer', array(
                $this,
                'addBlockedReferrer'
            ));
            
            // Init remove block actions
            add_action('wp_ajax_delete_block_ip', array(
                $this,
                'deleteBlockedIp'
            ));
            add_action('wp_ajax_delete_block_domain', array(
                $this,
                'deleteBlockedDomain'
            ));
            add_action('wp_ajax_delete_block_referrer', array(
                $this,
                'deleteBlockedReferrer'
            ));
            
            // Init Remove All action
            add_action('wp_ajax_reset_all', array(
                $this,
                'RemoveAllData'
            ));
            
            // Init save config action
            add_action('wp_ajax_save_general_option', array(
                $this,
                'saveGeneralOption'
            ));
            
            // Add settings link on plugin page
            add_filter("plugin_action_links_" . plugin_basename($WPMB_Config->plugin_path) . '/wpmb_backlinks.php', array(
                $this,
                'WPMB_settings_link'
            ));
        }

        public function WPMB_settings_link($links)
        {
            $settings_link = '<a href="' . get_admin_url(null, 'admin.php?page=wpmb-settings') . '">' . __('Settings', 'wpmbil') . '</a>';
            array_unshift($links, $settings_link);
            return $links;
        }

        function RemoveAllData($options = '')
        {
            global $wpdb;
            $wpdb->query("DELETE FROM " . $wpdb->prefix . "backlinks");
            $wpdb->query("DELETE FROM " . $wpdb->prefix . "backlinks_block_domain");
            $wpdb->query("DELETE FROM " . $wpdb->prefix . "backlinks_block_ip");
            $wpdb->query("DELETE FROM " . $wpdb->prefix . "backlinks_cron");
            
            // Reset options to default
            delete_option('wmpb_backlinks_config');
            WPMB_Install::create_config_option();
            
            // success
            if (isset($_POST['ajax'])) {
                echo json_encode(array(
                    'status' => true,
                    'message' => __('All data successful removed.', 'wpmbil')
                ));
                die();
            }
            
            return array(
                'status' => true,
                'message' => __('All data successful removed.', 'wpmbil')
            );
        }

        function saveGeneralOption($options = '')
        {
            global $wpdb;
            global $WPMB_Settings;
            global $WPMB_Config;
            if (! $options && $_POST['options']) {
                $options = $_POST['options'];
            }
            
            /* Prepare data before save */
            
            // Re-schedule emails
            
            $schedule = wp_get_schedule('email_wpmb_event');
            if ($schedule) {
                wp_clear_scheduled_hook('email_wpmb_event');
            }
            $timezone = time() - (int) current_time('timestamp');
            
            if ($options['email_frequency']) {
                // Calculate seconds from now
                // daily
                error_log("intrat ", 3, 'my_errors.log');
                if ($options['email_frequency'] === 'daily') {
                    error_log("daily ", 3, 'my_errors.log');
                    $email_frequency_hour_min = explode(':', $options['email_frequency_hour_min']);
                    $start = strtotime(date("Y") . '-' . date("m") . '-' . date("d") . ' ' . $email_frequency_hour_min[0] . ':' . (isset($email_frequency_hour_min[1]) ? $email_frequency_hour_min[1] : '00') . ':00');
                }
                // weekly
                if ($options['email_frequency'] === 'weekly') {
                    error_log("weekly ", 3, 'my_errors.log');
                    $day = date('d', strtotime('next ' . strtolower($options['email_frequency_day'])));
                    $month = date('m', strtotime('next ' . strtolower($options['email_frequency_day'])));
                    $year = date('Y', strtotime('next ' . strtolower($options['email_frequency_day'])));
                    
                    $email_frequency_hour_min = explode(':', $options['email_frequency_hour_min']);
                    $start = strtotime($year . '-' . $month . '-' . $day . ' ' . $email_frequency_hour_min[0] . ':' . (isset($email_frequency_hour_min[1]) ? $email_frequency_hour_min[1] : '00') . ':00');
                }
                wp_schedule_event($start + $timezone, $options['email_frequency'], 'email_wpmb_event');
            }
            
            // Exclude domains using excerpts, convert data from string to array
            if (isset($options['exclude_domains_sing_excerpts'])) {
                $options['exclude_domains_sing_excerpts'] = array_map('trim', explode(',', stripslashes($options['exclude_domains_sing_excerpts'])));
            } else {
                $options['exclude_domains_sing_excerpts'] = array();
            }
            
            // add secret key
            $options['secret_key'] = $WPMB_Config->configs->secret_key;
            
            /* ! End Prepare data before save */
            
            if (get_option('wmpb_backlinks_config')) {
                update_option('wmpb_backlinks_config', json_encode($options));
            } else {
                add_option('wmpb_backlinks_config', json_encode($options));
            }
            $WPMB_Settings->configs = $options;
            $WPMB_Config->configs = $options;
            // success
            if (isset($_POST['ajax'])) {
                echo json_encode(array(
                    'status' => true,
                    'message' => __('Settings successful saved.', 'wpmbil')
                ));
                die();
            }
            return array(
                'status' => true,
                'message' => __('Settings successful saved.', 'wpmbil')
            );
        }

        /*
         * Display content for general option tab
         */
        public function display_general_options_page()
        {
            global $WPMB_Settings;
            global $WPMB_Config;
            $settings = $WPMB_Config->configs;
            ?>
<div id="poststuff">
	<div id="post-body" class="metabox-holder columns-2">
		<!-- main content -->
		<div id="post-body-content">
			<div class="meta-box-sortables ui-sortable">
                                <?php
            if (defined('DISABLE_WP_CRON') and DISABLE_WP_CRON and $settings->cron) {
                echo "<div class='error' style='margin-top:20px;'><p><strong style='color: #dd3d36;'>" . __("Error:", 'wpmbil') . " </strong>" . __("WP_CRON is disabled. Pending incoming links won't be verified! Edit your <strong>wp-config.php</strong> file and set <strong>DISABLE_WP_CRON</strong> to <strong>FALSE</strong>", 'wpmbil') . "!</p></div>";
            }
            ?>                        
	                        <div class="settings-wrapper">
					<div class="inside">
						<h2><?php _e('General','wpmbil'); ?></h2>
						<div class="general-settings">
							<form class="reset_all" method="POST"
								action="<?php echo $_SERVER['REQUEST_URI']; ?>">
								<table class="settings-table">
									<!-- Remove all data -->
									<tr valign="top">
										<td width="274" class="row-title"><label for="submit"><?php _e('Reset all stats:','wpmbil'); ?></label>
										</td>
										<td>
                                                        <?php submit_button( __('Reset all stats and settings','wpmbil'), $type = 'secondary', $name = 'submit', $wrap = false, $other_attributes = null ); ?>
                                                        <div>
												<p class="wpmbdesc label">
                                                                <?php _e('If you want to restore the plugin to default settings and to remove all stats, use this button. This option will also clear all your lists, including: Valid Backlinks, Referrers, Blocked IPs, Blocked Domains and Blocked Referrers.','wpmbil'); ?>
                                                            </p>
											</div>
										</td>
									</tr>
									<input type="hidden" name="action" value="reset_all">
									<input type="hidden" name="ajax" value="true">
								</table>
							</form>
						</div>
						<hr />
						<form method="POST"
							action="<?php echo $_SERVER['REQUEST_URI']; ?>">
							<h2><?php _e('Email Settings','wpmbil'); ?></h2>
							<div class="email-settings">
								<table class="settings-table">
									<!-- Email settings -->
									<tr valign="top">
										<td class="row-title"><label for="email-frequency"><?php _e('Frequency:','wpmbil'); ?></label>
										</td>
										<td><select class="all-options" id="email-frequency"
											name="options[email_frequency]">
												<option value="0"
													<?php selected( $settings->email_frequency, "0"); ?>><?php _e('Never','wpmbil'); ?></option>
												<option value="daily"
													<?php selected( $settings->email_frequency, "daily"); ?>><?php _e('Daily','wpmbil'); ?></option>
												<option value="weekly"
													<?php selected( $settings->email_frequency, "weekly"); ?>><?php _e('Weekly','wpmbil'); ?></option>
										</select>

                                                        <?php
            $days = array(
                'Sunday',
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
                'Saturday'
            );
            ?>
                                                        <select class="all-options" id="email-frequency-day" name="options[email_frequency_day]" style="<?php echo (($settings->email_frequency === "0" || $settings->email_frequency==='daily')?'display:none;':''); ?>">
                                                            <?php
            foreach ($days as $day) {
                ?>
                                                                <option
													value="<?php echo $day; ?>"
													<?php selected( $settings->email_frequency_day, $day); ?>><?php _e($day); ?></option>
                                                                <?php
            }
            ?>
                                                        </select> <select class="all-options" id="email-frequency-hour-min" name="options[email_frequency_hour_min]" style="<?php echo (($settings->email_frequency === "0")?'display:none;':''); ?>">
                                                            <?php
            for ($i = 1; $i < 12; $i ++) {
                ?>
                                                                <option
													value="<?php echo $i; ?>"
													<?php selected( $settings->email_frequency_hour_min, $i); ?>><?php _e($i.':00am'); ?></option>
												<option value="<?php echo $i.':30'; ?>"
													<?php selected( $settings->email_frequency_hour_min, $i.':30'); ?>><?php _e($i.':30am'); ?></option>
                                                                <?php
            }
            for ($i = 12; $i < 24; $i ++) {
                ?>
                                                                <option
													value="<?php echo $i; ?>"
													<?php selected( $settings->email_frequency_hour_min, $i); ?>><?php _e($i.':00pm'); ?></option>
												<option value="<?php echo $i.':30'; ?>"
													<?php selected( $settings->email_frequency_hour_min, $i.':30'); ?>><?php _e($i.':30pm'); ?></option>
                                                            <?php
            }
            ?>
                                                        </select></td>
									</tr>
									<tr>
										<td colspan="2">
											<p class="wpmbdesc">
                                                            <?php
            
_e('Using this feature you will automatically receive latest backlinks, via email, daily or on
                                                            the selected day of each week at the selected time. After receiving the email, you can
                                                            share it with your friends, team or business partners.', 'wpmbil');
            ?>
                                                        </p>
										</td>
									</tr>
									<tr valign="top" id="mailingListWrapper">
										<td class="row-title"><label for="mailing-list"><?php _e('Mailing List:','wpmbil'); ?></label>
										</td>
										<td><textarea rows="3" cols="70" id="mailing-list"
												name="options[mailingList]"><?php echo $settings->mailingList; ?></textarea>
										</td>
									</tr>
									<tr>
										<td colspan="2">
											<p class="wpmbdesc">
                                                             <?php _e('Emails addresses in this list will receive daily/weekly reports with your latest backlinks.','wpmbil'); ?>
                                                         </p>
										</td>
									</tr>
                                                 <?php 
/*
                   * <tr valign="top" id="mandrillApiKeyWrapper">
                   * <td class="row-title">
                   * <label for="email-frequency"><?php _e('Mandrill Api Key:','wpmbil'); ?></label>
                   * </td>
                   * <td>
                   * <input type="text" name="options[mandrillApiKey]" placeholder="B46lJYwgFM36gLj417wa4A" value="<?php echo $settings->mandrillApiKey; ?>" />
                   *
                   * </td>
                   * </tr>
                   * <tr>
                   * <td colspan="2">
                   * <p class="wpmbdesc">
                   * <?php _e('We integrated Mandrill to our system, so you can use it by free, just need Sign Up and get Api key by link <a href="https://mandrill.com/signup/" target="_blank">https://mandrill.com/signup/</a> . Via Mandrill you can see mail details statistics and get messages without spam and without delay.','wpmbil'); ?>
                   * </p>
                   * </td>
                   * </tr>
                   */
            ?>
                                                 <tr>
										<td colspan="2">
                                                         <?php submit_button( __('Save All Settings','wpmbil'), $type = 'primary', $name = 'submit', $wrap = false, $other_attributes = null ); ?>
                                                     </td>
									</tr>
								</table>
							</div>

							<hr />

							<h2><?php _e('Pagination Settings','wpmbil'); ?></h2>
							<div class="pagination-settings">
								<table class="settings-table">
									<!--  Last links found -->
									<tr valign="top">
										<td class="row-title"><label for="items_per_page_last_found"><?php _e('Recent Backlinks:','wpmbil'); ?></label>
										</td>
										<td><select id="items_per_page_main"
											name="options[items_per_page_last_found]">
                                                            <?php
            for ($i = 10; $i < 110; $i = $i + 10) {
                ?>
                                                                <option
													value="<?php echo $i?>"
													<?php selected( $settings->items_per_page_last_found, $i); ?>><?php echo $i.' '.__('per page','wpmbil'); ?></option>
                                                            <?php
            }
            ?>
                                                        </select></td>
									</tr>
									<tr>
										<td colspan="2"><p class="wpmbdesc"><?php _e('Change the default value if you want to display more than 10 items on your Recent Backlinks widget.','wpmbil'); ?></p></td>
									</tr>

									<!-- Items per page (main links) -->
									<tr valign="top">
										<td class="row-title"><label for="items_per_page_main"><?php _e('Valid Backlinks:','wpmbil'); ?></label>
										</td>
										<td><select id="items_per_page_main"
											name="options[items_per_page_main]">
                                                            <?php
            for ($i = 10; $i < 110; $i = $i + 10) {
                ?>
                                                                <option
													value="<?php echo $i?>"
													<?php selected( $settings->items_per_page_main, $i); ?>><?php echo $i.' '.__('per page','wpmbil'); ?></option>
                                                            <?php
            }
            ?>
                                                        </select></td>
									</tr>
									<tr>
										<td colspan="2">
											<p class="wpmbdesc">
                                                            <?php _e('Change the default value if you want to display more than 10 items per page on the Valid Backlinks list.','wpmbil'); ?>
                                                        </p>
										</td>
									</tr>

									<!-- Items per page (wait links) -->
									<tr valign="top">
										<td class="row-title"><label for="items_per_page_wait"><?php _e('Referrers list:','wpmbil'); ?></label>
										</td>
										<td><select id="items_per_page_wait"
											name="options[items_per_page_wait]">
                                                            <?php
            for ($i = 10; $i < 110; $i = $i + 10) {
                ?>
                                                                <option
													value="<?php echo $i?>"
													<?php selected( $settings->items_per_page_wait, $i); ?>><?php echo $i.' '.__('per page','wpmbil'); ?></option>
                                                            <?php
            }
            ?>
                                                        </select></td>
									</tr>
									<tr>
										<td colspan="2">
											<p class="wpmbdesc">
                                                            <?php _e('Change the default value if you want to display more than 10 items per page on Referrers Pending for Verification list.','wpmbil'); ?>
                                                        </p>
										</td>
									</tr>
									<!-- Items per page (Blocked links) -->
									<tr valign="top">
										<td class="row-title"><label for="items_per_page_blocked"><?php _e('Other lists:','wpmbil'); ?></label>
										</td>
										<td><select id="items_per_page_blocked"
											name="options[items_per_page_blocked]">
                                                            <?php
            for ($i = 10; $i < 110; $i = $i + 10) {
                ?>
                                                                <option
													value="<?php echo $i?>"
													<?php selected( $settings->items_per_page_blocked, $i); ?>><?php echo $i.' '.__('per page','wpmbil'); ?></option>
                                                            <?php
            }
            ?>
                                                        </select></td>
									</tr>
									<tr>
										<td colspan="2">
											<p class="wpmbdesc">
                                                            <?php _e('Change the default value if you want to display more than 10 items per page on blocked  IPs list, blocked domains list and blocked referrers list.','wpmbil'); ?>
                                                        </p>
										</td>
									</tr>
								</table>

                                            <?php submit_button( __('Save All Settings','wpmbil'), $type = 'primary', $name = 'submit', $wrap = false, $other_attributes = null ); ?>
                                        </div>

							<hr />

							<h2><?php _e('Advanced Settings','wpmbil'); ?><span
									class="advanced-settings-trigger"><a class="show" href="#"><?php _e('Show','wpmbil'); ?></a><a
									style="display: none;" class="hide" href="#"><?php _e('Hide','wpmbil'); ?></a></span>
							</h2>
							<div class="advanced-settings" style="display: none;">
								<table class="settings-table">
									<tr valign="top">
										<td class="row-title"><label for="limit_links_domain"><?php _e('Links/Domain:','wpmbil'); ?></label>
										</td>
										<td>
                                                        <?php
            $limit_links_domain = array(
                '1' => '1',
                '3' => '3',
                '5' => '5',
                '10' => '10',
                '50' => '50',
                '100' => '100',
                'unlimited' => 0
            );
            ?>
                                                        <select
											id="limit_links_domain" name="options[limit_links_domain]">
                                                            <?php
            foreach ($limit_links_domain as $key => $value) {
                ?>
                                                                    <option
													value="<?php echo $value?>"
													<?php selected( $settings->limit_links_domain, $value); ?>><?php echo $key?></option>
                                                                <?php
            }
            ?>
                                                        </select>
										</td>
									</tr>
									<tr>
										<td colspan="2">
											<p class="wpmbdesc">
                                                            <?php _e('This is the number of links which will be crawled for each domain. If the maximum number is reached the rest of the links coming from that specific domain will be ignored.','wpmbil'); ?>
                                                        </p>
										</td>
									</tr>


									<!-- Ignore roles referrer -->
									<tr valign="top" class="tr_ignore_roles_referer">
										<td class="row-title"><label for="ignore_roles_referer"><?php _e('Exclude by Role:','wpmbil'); ?></label>
										</td>
										<td class="td_ignore_roles_referer">
                                                        <?php
            if (! isset($settings->ignore_roles_referer))
                $settings->ignore_roles_referer = array();
            if (! isset($wp_roles))
                $wp_roles = new WP_Roles();
            foreach ($wp_roles->role_names as $role => $name) :
                ?>
                                                            <label>
                                                                <?php echo $name; ?>
                                                                <input
												type="checkbox" name="options[ignore_roles_referer][]"
												value="<?php echo $role; ?>"
												<?php if (in_array($role,$settings->ignore_roles_referer)) echo 'checked="checked"'; ?> />
										</label>
                                                        <?php
            endforeach
            ;
            ?>
                                                    </td>
									</tr>
									<tr>
										<td colspan="2">
											<p class="wpmbdesc">
                                                            <?php _e('Use this option if you need to ignore referrers for logged in users, with a specified role. You can select multiple checkboxes to exclude referrer checking for multiple roles.','wpmbil'); ?>
                                                        </p>
										</td>
									</tr>

									<!-- Exclude domains using excerpts -->
									<tr valign="top" class="tr_exclude_domains_sing_excerpts">
										<td class="row-title"><label
											for="exclude_domains_sing_excerpts"><?php _e('Exclude Filter:','wpmbil'); ?></label>
										</td>
										<td class="td_exclude_domains_sing_excerpts">
                                                        <?php
            if (! isset($settings->exclude_domains_sing_excerpts))
                $settings->exclude_domains_sing_excerpts = array();
            ?>
                                                        <textarea
												rows="5" cols="70"
												name="options[exclude_domains_sing_excerpts]"
												id="exclude_domains_sing_excerpts"><?php echo implode(' , ',$settings->exclude_domains_sing_excerpts); ?></textarea>
										</td>
									</tr>
									<tr>
										<td colspan="2">
											<p class="wpmbdesc">
                                                           <?php  _e('Use this option if you need to eliminate certain referrers from your reports (referrers containing a specific string or matching a pattern). By default, the list is populated with most popular CMS backends.','wpmbil'); ?>
                                                        </p>
										</td>
									</tr>



									<!-- After X referrals not found, block domain -->
									<tr valign="top">
										<td class="row-title"><label for="ban_domain"><?php _e('Block domain, after:','wpmbil'); ?></label>
										</td>
										<td><select id="ban_domain" name="options[ban_domain]">
												<option value="0"
													<?php selected( $settings->ban_domain, 0); ?>><?php _e('Never','wpmbil'); ?></option>
                                                            <?php
            for ($i = 1; $i < 20; $i ++) {
                ?>
                                                                <option
													value="<?php echo $i;?>"
													<?php selected( $settings->ban_domain, $i); ?>><?php echo $i.' '.__('referrals not found.','wpmbil'); ?></option>
                                                            <?php
            }
            ?>
                                                        </select></td>
									</tr>
									<tr>
										<td colspan="2">
											<p class="wpmbdesc">
                                                            <?php _e('Using this option you can block a domain if no referrers are found after a selected number of checks. Choose Never to disable this option.','wpmbil'); ?>
                                                        </p>
										</td>
									</tr>



									<!-- Count of items for parse per Cron -->
									<tr valign="top">
										<td class="row-title"><label for="cron_parse_count"><?php _e('Referrers per cron:','wpmbil'); ?></label>
										</td>
										<td><select id="cron_parse_count"
											name="options[cron_parse_count]">
                                                            <?php
            for ($i = 1; $i < 10; $i ++) {
                ?>
                                                                <option
													value="<?php echo $i;?>"
													<?php selected( $settings->cron_parse_count, $i); ?>><?php echo $i;?></option>
                                                            <?php
            }
            ?>
                                                        </select></td>
									</tr>
									<tr>
										<td colspan="2">
											<p class="wpmbdesc">
                                                            <?php _e('This option indicates the number of links (referrers) to be checked on each cron. If your referrers list becomes too large you should increase this value.','wpmbil'); ?>
                                                        </p>
										</td>
									</tr>

									<!-- Choose Cron system -->
									<tr valign="top">
										<td class="row-title"><label for="cron"><?php _e('Cron Type:','wpmbil'); ?></label>
										</td>
										<td><select id="cron" name="options[cron]">
												<option value="1" <?php selected( $settings->cron, "1"); ?>><?php _e('Wordpress','wpmbil'); ?></option>
												<option value="0" <?php selected( $settings->cron, "0"); ?>><?php _e('Use own cron','wpmbil'); ?></option>
										</select></td>
									</tr>
									<tr>
										<td colspan="2">
											<p class="wpmbdesc">
												<span id="ownCronDescription"
													<?php echo ((int)$settings->cron==1 ? ' style="display:none;"' : ''); ?>>
                                                                <?php echo __('Using this option you will need to define your own cron job using this link: ','wpmbil').get_bloginfo('url').'?action=wpmb_check_referrers&secret_key='.$settings->secret_key; ?>
                                                            </span> <span
													id="wpCronDescription"
													<?php echo ((int)$settings->cron==0 ? ' style="display:none;"' : ''); ?>>
                                                                <?php _e('WordPress Cron is triggered by site visits, being a "pseudocron" it may not run as expected on sites with little or no traffic.','wpmbil'); ?>
                                                            </span>
											</p>
										</td>
									</tr>


									<!-- Cron recurrence -->
									<tr valign="top" id="cron_recurrence_row"
										<?php echo ((int)$settings->cron==0 ? ' style="display:none;"' : ''); ?>>
										<td class="row-title"><label for="cron_recurrence"><?php _e('Cron recurrence:','wpmbil'); ?></label>
										</td>
										<td><select id="cron_recurrence"
											name="options[cron_recurrence]" style="width: 300px">
                                                            <?php
            $schedules = wp_get_schedules();
            unset($schedules['weekly']);
            unset($schedules['monthly']);
            foreach ($schedules as $key => $schedule) :
                ?>
                                                                <option
													value="<?php echo $key;?>"
													<?php selected( $settings->cron_recurrence, $key); ?>><?php echo $schedule['display'];?></option>
                                                                <?php
            endforeach
            ;
            ?>
                                                        </select></td>
									</tr>
									<tr id="cron_recurrence_desc_row"
										<?php echo ((int)$settings->cron==0 ? ' style="display:none;"' : ''); ?>>
										<td colspan="2">
											<p class="wpmbdesc">
                                                            <?php _e('This is how often the cron job will run. If your referrers list becomes too large you should set a lower value, allowing cron job to run more often.','wpmbil'); ?>
                                                        </p>
										</td>
									</tr>

									<!-- Submit button -->
									<tr>
										<td colspan="2">
                                                        <?php submit_button( __('Save All Settings','wpmbil'), $type = 'primary', $name = 'submit', $wrap = false, $other_attributes = null); ?>
                                                    </td>
									</tr>
								</table>
							</div>
							<!--advanced-settings-->
							<input type="hidden" name="action" value="save_general_option"> <input
								type="hidden" name="ajax" value="true">
						</form>
					</div>
					<!-- .inside -->
				</div>
				<!-- .settings-wrapper -->
			</div>
			<!-- .meta-box-sortables .ui-sortable -->
		</div>
		<!-- post-body-content -->

		<!-- sidebar -->
		<div id="postbox-container-1" class="postbox-container">

			<div class="meta-box-sortables">

				<div class="postbox">

					<h3>
						<span><?php _e('How does it work?','wpmbil'); ?></span>
					</h3>
					<div class="inside">
						<div>
							<a href="http://monitorbacklinks.com/blog/incoming-links/"><img
								src="<?php echo plugins_url( 'images/incoming-links.png' , __FILE__ ); ?>" /></a>
						</div>
						<span class="wpmbdesc"><?php echo  __('A full description and FAQs are available','wpmbil').' <a href="http://monitorbacklinks.com/blog/incoming-links/">'.__('here').'</a>.'; ?></span>
						<br />
						<br /> <span class="wpmbdesc"><?php echo  __('Use the','wpmbil').' <a href="widgets.php">'.__('Recent Backlinks Widget').'</a> '.__('to display the latest domains linking back to you!','wpmbil'); ?></span>
					</div>
					<!-- .inside -->

				</div>
				<!-- .postbox -->


				<div class="postbox">

					<h3>
						<span><?php _e('Support & Reviews','wpmbil'); ?></span>
					</h3>
					<div class="inside">
						<div class="mbl-title"><?php _e('Have a question?','wpmbil'); ?></div>
						<span class="wpmbdesc"><?php echo  __('You can ask for support','wpmbil').' <a href="http://wordpress.org/support/plugin/incoming-links/">'.__('here','wpmbil').'</a>.'; ?></span>
						<br />
						<br /> <span class="wpmbdesc"><?php echo  __('This is a beta version, at this stage, please consider','wpmbil').' <a href="https://github.com/monitorbacklinks/incoming-links/issues">'.__('submitting a bug','wpmbil').'</a> '.__('instead of rating it','wpmbil'); ?>.</span>
						<br />
						<br />
						<div>
							<a href="#"><img
								src="<?php echo plugins_url( 'images/reviews.png' , __FILE__ ); ?>" /></a>
						</div>
						<span class="wpmbdesc"><?php echo  __('Your opinions and reviews are very important, write one','wpmbil').' <a href="http://wordpress.org/support/view/plugin-reviews/incoming-links/">'.__('now','wpmbil').'</a>!'; ?></span>
					</div>
					<!-- .inside -->

				</div>
				<!-- .postbox -->

				<div class="postbox">

					<h3>
						<span><?php _e('About us','wpmbil'); ?></span>
					</h3>
					<div class="inside">
						<div class="mbl-logo">
							<a href="http://monitorbacklinks.com"><img
								src="<?php echo plugins_url( 'images/monitorbacklinks.png' , __FILE__ ); ?>" /></a>
						</div>
						<span class="wpmbdesc"><?php echo  __('Monitor Backlinks provides professional link monitoring services. A powerful, must have tool for SEOs and Web Marketers,','wpmbil').' <a href="http://monitorbacklinks.com">'.__('read more','wpmbil').'</a>!'; ?></span>
					</div>
					<!-- .inside -->

				</div>
				<!-- .postbox -->

			</div>
			<!-- .meta-box-sortables -->

		</div>
		<!-- #postbox-container-1 .postbox-container -->

	</div>
	<!-- #post-body .metabox-holder .columns-2 -->

	<br class="clear">
</div>
<!-- #poststuff -->
<?php
        }

        /*
         * Display content for block ip tab
         */
        public function display_block_ip_page()
        {
            global $WPMB_Settings;
            $totalIp = $WPMB_Settings->getCountBlockedIps();
            ?>
<div id="poststuff">

	<div id="post-body" class="metabox-holder columns-2">

		<!-- main content -->
		<div id="post-body-content">
			<div class="meta-box-sortables ui-sortable">
				<div class="postbox">
					<h3>
						<span><?php _e('List of blocked IPs','wpmbil'); ?></span>
					</h3>
					<div class="inside">
						<div class="tablenav-pages">
							<div class="pagination-links">
                                                <?php
            if ($totalIp && $WPMB_Settings->settings->items_per_page_blocked) {
                echo paginate_links(array(
                    'base' => '?page=wpmb-settings&pagedIp=%#%',
                    'current' => max(1, (isset($_GET['pagedIp']) ? $_GET['pagedIp'] : '1')),
                    'format' => '?pagedIp=%#%',
                    'total' => ceil($totalIp / $WPMB_Settings->settings->items_per_page_blocked),
                    'add_args' => array(
                        'active_page' => 'block-ip',
                        'pagedDomains' => max(1, (isset($_GET['pagedDomains']) ? $_GET['pagedDomains'] : '1')),
                        'pagedRef' => max(1, (isset($_GET['pagedRef']) ? $_GET['pagedRef'] : '1'))
                    )
                ));
            }
            ?>
                                            </div>
							<div class="form-block">
								<form method="POST"
									action="<?php echo $_SERVER['REQUEST_URI']; ?>">
									<table>
										<tr>
											<td><input required="required"
												pattern="\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}" name="ip"
												id="ip" type="text" value="" />
		                                                    <?php submit_button( __('Block IP','wpmbil'), $type = 'block', $name = 'submit', $wrap = false, $other_attributes = null ); ?>
		                                                </td>
										</tr>
									</table>
									<input type="hidden" name="action" value="add_block_ip"> <input
										type="hidden" name="ajax" value="true">
								</form>
							</div>
						</div>
						<br>
                                    <?php
            foreach ($WPMB_Settings->getBlockedIps($WPMB_Settings->settings->items_per_page_blocked) as $ip) {
                ?>
                                        <form method="POST"
							action="<?php echo $_SERVER['REQUEST_URI']; ?>"
							data-per-page="<?php echo $WPMB_Settings->settings->items_per_page_blocked;?>">
							<table class="widefat">
								<tr valign="top">
									<td class="row-title"><label><?php echo $ip->ip; ?></label></td>
									<td align="right"><input class="button-primary" type="submit"
										name="submit" value="<?php _e( 'Remove','wpmbil' ); ?>" /></td>
								</tr>
							</table>
							<input type="hidden" name="id" value="<?php echo $ip->id; ?>"> <input
								type="hidden" name="action" value="delete_block_ip"> <input
								type="hidden" name="ajax" value="true">
						</form>
                                    <?php
            }
            ?>
                                </div>
					<!-- .inside -->
					<br>
					<div class="tablenav-pages bottom">
						<span class="pagination-links">
                                        <?php
            if ($totalIp && $WPMB_Settings->settings->items_per_page_blocked) {
                echo paginate_links(array(
                    'base' => '?page=wpmb-settings&pagedIp=%#%',
                    'current' => max(1, (isset($_GET['pagedIp']) ? $_GET['pagedIp'] : '1')),
                    'format' => '?pagedIp=%#%',
                    'total' => ceil($totalIp / $WPMB_Settings->settings->items_per_page_blocked),
                    'add_args' => array(
                        'active_page' => 'block-ip',
                        'pagedDomains' => max(1, (isset($_GET['pagedDomains']) ? $_GET['pagedDomains'] : '1')),
                        'pagedRef' => max(1, (isset($_GET['pagedRef']) ? $_GET['pagedRef'] : '1'))
                    )
                ));
            }
            ?>
                                    </span>
					</div>
					<br>
				</div>
				<!-- .postbox -->

			</div>
			<!-- .meta-box-sortables .ui-sortable -->

		</div>
		<!-- post-body-content -->

		<!-- sidebar -->
		<div id="postbox-container-1" class="postbox-container sidebar">

			<div class="meta-box-sortables">

				<div class="postbox">

					<h3>
						<span><?php _e('How does it work?','wpmbil'); ?></span>
					</h3>
					<div class="inside">
						<div>
							<a href="http://monitorbacklinks.com/blog/incoming-links/"><img
								src="<?php echo plugins_url( 'images/incoming-links.png' , __FILE__ ); ?>" /></a>
						</div>
						<span class="wpmbdesc"><?php echo  __('A full description and FAQs are available','wpmbil').' <a href="http://monitorbacklinks.com/blog/incoming-links/">'.__('here').'</a>.'; ?></span>
						<br />
						<br /> <span class="wpmbdesc"><?php echo  __('Use the','wpmbil').' <a href="widgets.php">'.__('Recent Backlinks Widget').'</a> '.__('to display the latest domains linking back to you!','wpmbil'); ?></span>
					</div>
					<!-- .inside -->

				</div>
				<!-- .postbox -->


				<div class="postbox">

					<h3>
						<span><?php _e('Support & Reviews','wpmbil'); ?></span>
					</h3>
					<div class="inside">
						<div class="mbl-title"><?php _e('Have a question?','wpmbil'); ?></div>
						<span class="wpmbdesc"><?php echo  __('You can ask for support','wpmbil').' <a href="http://wordpress.org/support/plugin/incoming-links/">'.__('here','wpmbil').'</a>.'; ?></span>
						<br />
						<br /> <span class="wpmbdesc"><?php echo  __('This is a beta version, at this stage, please consider','wpmbil').' <a href="https://github.com/monitorbacklinks/incoming-links/issues">'.__('submitting a bug','wpmbil').'</a> '.__('instead of rating it','wpmbil'); ?>.</span>
						<br />
						<br />
						<div>
							<a href="#"><img
								src="<?php echo plugins_url( 'images/reviews.png' , __FILE__ ); ?>" /></a>
						</div>
						<span class="wpmbdesc"><?php echo  __('Your opinions and reviews are very important, write one','wpmbil').' <a href="http://wordpress.org/support/view/plugin-reviews/incoming-links/">'.__('now','wpmbil').'</a>!'; ?></span>
					</div>
					<!-- .inside -->

				</div>
				<!-- .postbox -->

				<div class="postbox">

					<h3>
						<span><?php _e('About us','wpmbil'); ?></span>
					</h3>
					<div class="inside">
						<div class="mbl-logo">
							<a href="http://monitorbacklinks.com"><img
								src="<?php echo plugins_url( 'images/monitorbacklinks.png' , __FILE__ ); ?>" /></a>
						</div>
						<span class="wpmbdesc"><?php echo  __('Monitor Backlinks provides professional link monitoring services. A powerful, must have tool for SEOs and Web Marketers,','wpmbil').' <a href="http://monitorbacklinks.com">'.__('read more','wpmbil').'</a>!'; ?></span>
					</div>
					<!-- .inside -->

				</div>
				<!-- .postbox -->

			</div>
			<!-- .meta-box-sortables -->

		</div>
		<!-- #postbox-container-1 .postbox-container -->

	</div>
	<!-- #post-body .metabox-holder .columns-2 -->

	<br class="clear">
</div>
<!-- #poststuff -->
<?php
        }

        /*
         * Display content for block domain tab
         */
        public function display_block_domain_page()
        {
            global $WPMB_Settings;
            $totalDomains = $WPMB_Settings->getCountBlockedDomains();
            
            ?>
<div id="poststuff">

	<div id="post-body" class="metabox-holder columns-2">

		<!-- main content -->
		<div id="post-body-content">

			<div class="meta-box-sortables ui-sortable">

				<div class="postbox">

					<h3>
						<span><?php _e('List of blocked Domains','wpmbil'); ?></span>
					</h3>
					<div class="inside" data-per-page="">
						<div class="tablenav-pages">
							<div class="pagination-links">
                                                <?php
            if ($totalDomains && $WPMB_Settings->settings->items_per_page_blocked) {
                echo paginate_links(array(
                    'base' => '?page=wpmb-settings&pagedDomains=%#%',
                    'current' => max(1, (isset($_GET['pagedDomains']) ? $_GET['pagedDomains'] : '1')),
                    'format' => '?pagedDomains=%#%',
                    'total' => ceil($totalDomains / $WPMB_Settings->settings->items_per_page_blocked),
                    'add_args' => array(
                        'active_page' => 'block-domain',
                        'pagedIp' => max(1, (isset($_GET['pagedIp']) ? $_GET['pagedIp'] : '1')),
                        'pagedRef' => max(1, (isset($_GET['pagedRef']) ? $_GET['pagedRef'] : '1'))
                    )
                ));
            }
            ?>
                                            </div>
							<div class="form-block">
								<form method="POST"
									action="<?php echo $_SERVER['REQUEST_URI']; ?>">
									<table>
										<tr>
											<td><input required="required" name="domain" id="domain"
												type="text" placeholder="domain.com" value="" />
		                                                    <?php submit_button( __('Block Domain','wpmbil'), $type = 'block', $name = 'submit', $wrap = false, $other_attributes = null ); ?>
		                                                </td>
										</tr>
									</table>
									<input type="hidden" name="action" value="add_block_domain"> <input
										type="hidden" name="ajax" value="true">
								</form>
							</div>
						</div>
						<br>
                                    <?php
            foreach ($WPMB_Settings->getBlockedDomains($WPMB_Settings->settings->items_per_page_blocked) as $domain) {
                ?>
                                        <form method="POST"
							action="<?php echo $_SERVER['REQUEST_URI']; ?>"
							data-per-page="<?php echo $WPMB_Settings->settings->items_per_page_blocked;?>">
							<table class="widefat">
								<tr valign="top">
									<td class="row-title"><label><a
											href="<?php echo $domain->domain; ?>" target="_blank"><?php echo $domain->domain; ?></a></label>
									</td>
									<td align="right"><input class="button-primary" type="submit"
										name="submit" value="<?php _e( 'Remove','wpmbil' ); ?>" /></td>
								</tr>
							</table>
							<input type="hidden" name="id" value="<?php echo $domain->id; ?>">
							<input type="hidden" name="action" value="delete_block_domain"> <input
								type="hidden" name="ajax" value="true">
						</form>
                                    <?php
            }
            ?>
                                </div>
					<!-- .inside -->

					<br>
					<div class="tablenav-pages bottom">
						<span class="pagination-links">
                                            <?php
            if ($totalDomains && $WPMB_Settings->settings->items_per_page_blocked) {
                echo paginate_links(array(
                    'base' => '?page=wpmb-settings&pagedDomains=%#%',
                    'current' => max(1, (isset($_GET['pagedDomains']) ? $_GET['pagedDomains'] : '1')),
                    'format' => '?pagedDomains=%#%',
                    'total' => ceil($totalDomains / $WPMB_Settings->settings->items_per_page_blocked),
                    'add_args' => array(
                        'active_page' => 'block-domain',
                        'pagedIp' => max(1, (isset($_GET['pagedIp']) ? $_GET['pagedIp'] : '1')),
                        'pagedRef' => max(1, (isset($_GET['pagedRef']) ? $_GET['pagedRef'] : '1'))
                    )
                ));
            }
            ?>
                                        </span>
					</div>
					<br>

				</div>
				<!-- .postbox -->

			</div>
			<!-- .meta-box-sortables .ui-sortable -->

		</div>
		<!-- post-body-content -->

		<!-- sidebar -->
		<div id="postbox-container-1" class="postbox-container sidebar">

			<div class="meta-box-sortables">

				<div class="postbox">

					<h3>
						<span><?php _e('How does it work?','wpmbil'); ?></span>
					</h3>
					<div class="inside">
						<div>
							<a href="http://monitorbacklinks.com/blog/incoming-links/"><img
								src="<?php echo plugins_url( 'images/incoming-links.png' , __FILE__ ); ?>" /></a>
						</div>
						<span class="wpmbdesc"><?php echo  __('A full description and FAQs are available','wpmbil').' <a href="http://monitorbacklinks.com/blog/incoming-links/">'.__('here').'</a>.'; ?></span>
						<br />
						<br /> <span class="wpmbdesc"><?php echo  __('Use the','wpmbil').' <a href="widgets.php">'.__('Recent Backlinks Widget').'</a> '.__('to display the latest domains linking back to you!','wpmbil'); ?></span>
					</div>
					<!-- .inside -->

				</div>
				<!-- .postbox -->


				<div class="postbox">

					<h3>
						<span><?php _e('Support & Reviews','wpmbil'); ?></span>
					</h3>
					<div class="inside">
						<div class="mbl-title"><?php _e('Have a question?','wpmbil'); ?></div>
						<span class="wpmbdesc"><?php echo  __('You can ask for support','wpmbil').' <a href="http://wordpress.org/support/plugin/incoming-links/">'.__('here','wpmbil').'</a>.'; ?></span>
						<br />
						<br /> <span class="wpmbdesc"><?php echo  __('This is a beta version, at this stage, please consider','wpmbil').' <a href="https://github.com/monitorbacklinks/incoming-links/issues">'.__('submitting a bug','wpmbil').'</a> '.__('instead of rating it','wpmbil'); ?>.</span>
						<br />
						<br />
						<div>
							<a href="#"><img
								src="<?php echo plugins_url( 'images/reviews.png' , __FILE__ ); ?>" /></a>
						</div>
						<span class="wpmbdesc"><?php echo  __('Your opinions and reviews are very important, write one','wpmbil').' <a href="http://wordpress.org/support/view/plugin-reviews/incoming-links/">'.__('now','wpmbil').'</a>!'; ?></span>
					</div>
					<!-- .inside -->

				</div>
				<!-- .postbox -->

				<div class="postbox">

					<h3>
						<span><?php _e('About us','wpmbil'); ?></span>
					</h3>
					<div class="inside">
						<div class="mbl-logo">
							<a href="http://monitorbacklinks.com"><img
								src="<?php echo plugins_url( 'images/monitorbacklinks.png' , __FILE__ ); ?>" /></a>
						</div>
						<span class="wpmbdesc"><?php echo  __('Monitor Backlinks provides professional link monitoring services. A powerful, must have tool for SEOs and Web Marketers,','wpmbil').' <a href="http://monitorbacklinks.com">'.__('read more','wpmbil').'</a>!'; ?></span>
					</div>
					<!-- .inside -->

				</div>
				<!-- .postbox -->

			</div>
			<!-- .meta-box-sortables -->

		</div>
		<!-- #postbox-container-1 .postbox-container -->

	</div>
	<!-- #post-body .metabox-holder .columns-2 -->

	<br class="clear">
</div>
<!-- #poststuff -->
<?php
        }

        /*
         * Display content for block Referrer tab
         */
        public function display_block_referrer_page()
        {
            global $WPMB_Settings;
            $totalRef = $WPMB_Settings->getCountBlockedReferrers();
            ?>
<div id="poststuff">

	<div id="post-body" class="metabox-holder columns-2">

		<!-- main content -->
		<div id="post-body-content">

			<div class="meta-box-sortables ui-sortable">

				<div class="postbox">

					<h3>
						<span><?php _e('List of blocked Referrers','wpmbil'); ?></span>
					</h3>
					<div class="inside">
						<div class="tablenav-pages">
							<div class="pagination-links">
                                            <?php
            if ($totalRef && $WPMB_Settings->settings->items_per_page_blocked) {
                echo paginate_links(array(
                    'base' => '?page=wpmb-settings&pagedRef=%#%',
                    'current' => max(1, (isset($_GET['pagedRef']) ? $_GET['pagedRef'] : '1')),
                    'format' => '?pagedDomains=%#%',
                    'total' => ceil($totalRef / $WPMB_Settings->settings->items_per_page_blocked),
                    'add_args' => array(
                        'active_page' => 'block-referrer',
                        'pagedIp' => max(1, (isset($_GET['pagedIp']) ? $_GET['pagedIp'] : '1')),
                        'pagedDomains' => max(1, (isset($_GET['pagedDomains']) ? $_GET['pagedDomains'] : '1'))
                    )
                ));
            }
            ?>
                                        </div>
							<div class="form-block">
								<form method="POST"
									action="<?php echo $_SERVER['REQUEST_URI']; ?>">
									<table>
										<tr>
											<td><input required="required" name="referrer" id="referrer"
												placeholder="http://domain.com/query" type="url" value="" />
	                                                    <?php submit_button( __('Block URL','wpmbil'), $type = 'block', $name = 'submit', $wrap = false, $other_attributes = null ); ?>
	                                                </td>
										</tr>
									</table>
									<input type="hidden" name="action" value="add_block_referrer">
									<input type="hidden" name="ajax" value="true">
								</form>
							</div>
						</div>
						<br>
                                    <?php
            foreach ($WPMB_Settings->getBlockedReferrers($WPMB_Settings->settings->items_per_page_blocked) as $referrer) {
                ?>
                                        <form method="POST"
							action="<?php echo $_SERVER['REQUEST_URI']; ?>"
							data-per-page="<?php echo $WPMB_Settings->settings->items_per_page_blocked;?>">
							<table class="widefat">
								<tr valign="top">
									<td class="row-title"><label><a
											href="<?php echo $referrer->referrer; ?>" target="_blank"><?php echo $WPMB_Settings->url_display($referrer->referrer,85); ?></a></label>
									</td>
									<td align="right"><input class="button-primary" type="submit"
										name="submit" value="<?php _e( 'Remove','wpmbil' ); ?>" /></td>
								</tr>
							</table>
							<input type="hidden" name="id"
								value="<?php echo $referrer->id; ?>"> <input type="hidden"
								name="action" value="delete_block_referrer"> <input
								type="hidden" name="ajax" value="true">
						</form>
                                    <?php
            }
            ?>
                                </div>
					<!-- .inside -->
					<br>
					<div class="tablenav-pages bottom">
						<span class="pagination-links">
                                        <?php
            if ($totalRef && $WPMB_Settings->settings->items_per_page_blocked) {
                echo paginate_links(array(
                    'base' => '?page=wpmb-settings&pagedRef=%#%',
                    'current' => max(1, (isset($_GET['pagedRef']) ? $_GET['pagedRef'] : '1')),
                    'format' => '?pagedDomains=%#%',
                    'total' => ceil($totalRef / $WPMB_Settings->settings->items_per_page_blocked),
                    'add_args' => array(
                        'active_page' => 'block-referrer',
                        'pagedIp' => max(1, (isset($_GET['pagedIp']) ? $_GET['pagedIp'] : '1')),
                        'pagedDomains' => max(1, (isset($_GET['pagedDomains']) ? $_GET['pagedDomains'] : '1'))
                    )
                ));
            }
            ?>
                                    </span>
					</div>
					<br>
				</div>
				<!-- .postbox -->

			</div>
			<!-- .meta-box-sortables .ui-sortable -->

		</div>
		<!-- post-body-content -->

		<!-- sidebar -->
		<div id="postbox-container-1" class="postbox-container sidebar">

			<div class="meta-box-sortables">

				<div class="postbox">

					<h3>
						<span><?php _e('How does it work?','wpmbil'); ?></span>
					</h3>
					<div class="inside">
						<div>
							<a href="http://monitorbacklinks.com/blog/incoming-links/"><img
								src="<?php echo plugins_url( 'images/incoming-links.png' , __FILE__ ); ?>" /></a>
						</div>
						<span class="wpmbdesc"><?php echo  __('A full description and FAQs are available','wpmbil').' <a href="http://monitorbacklinks.com/blog/incoming-links/">'.__('here').'</a>.'; ?></span>
						<br />
						<br /> <span class="wpmbdesc"><?php echo  __('Use the','wpmbil').' <a href="widgets.php">'.__('Recent Backlinks Widget').'</a> '.__('to display the latest domains linking back to you!','wpmbil'); ?></span>
					</div>
					<!-- .inside -->

				</div>
				<!-- .postbox -->


				<div class="postbox">

					<h3>
						<span><?php _e('Support & Reviews','wpmbil'); ?></span>
					</h3>
					<div class="inside">
						<div class="mbl-title"><?php _e('Have a question?','wpmbil'); ?></div>
						<span class="wpmbdesc"><?php echo  __('You can ask for support','wpmbil').' <a href="http://wordpress.org/support/plugin/incoming-links/">'.__('here','wpmbil').'</a>.'; ?></span>
						<br />
						<br /> <span class="wpmbdesc"><?php echo  __('This is a beta version, at this stage, please consider','wpmbil').' <a href="https://github.com/monitorbacklinks/incoming-links/issues">'.__('submitting a bug','wpmbil').'</a> '.__('instead of rating it','wpmbil'); ?>.</span>
						<br />
						<br />
						<div>
							<a href="#"><img
								src="<?php echo plugins_url( 'images/reviews.png' , __FILE__ ); ?>" /></a>
						</div>
						<span class="wpmbdesc"><?php echo  __('Your opinions and reviews are very important, write one','wpmbil').' <a href="http://wordpress.org/support/view/plugin-reviews/incoming-links/">'.__('now','wpmbil').'</a>!'; ?></span>
					</div>
					<!-- .inside -->

				</div>
				<!-- .postbox -->

				<div class="postbox">

					<h3>
						<span><?php _e('About us','wpmbil'); ?></span>
					</h3>
					<div class="inside">
						<div class="mbl-logo">
							<a href="http://monitorbacklinks.com"><img
								src="<?php echo plugins_url( 'images/monitorbacklinks.png' , __FILE__ ); ?>" /></a>
						</div>
						<span class="wpmbdesc"><?php echo  __('Monitor Backlinks provides professional link monitoring services. A powerful, must have tool for SEOs and Web Marketers,','wpmbil').' <a href="http://monitorbacklinks.com">'.__('read more','wpmbil').'</a>!'; ?></span>
					</div>
					<!-- .inside -->

				</div>
				<!-- .postbox -->

			</div>
			<!-- .meta-box-sortables -->

		</div>
		<!-- #postbox-container-1 .postbox-container -->

	</div>
	<!-- #post-body .metabox-holder .columns-2 -->

	<br class="clear">
</div>
<!-- #poststuff -->
<?php
        }

        /*
         * Generate full content for settings page
         */
        static function settings_page()
        {
            global $WPMB_Settings;
            global $WPMB_Config;
            $WPMB_Settings->settings = $WPMB_Config->configs;
            $active_page = (isset($_GET['active_page']) && $_GET['active_page'] ? $_GET['active_page'] : 'general-options');
            ?>
<div class="wrap wpmb_settings">
	<div id="icon-options-general" class="icon32">
		<br>
	</div>
	<h2 class="nav-tab-wrapper">
		<a href="#general-options"
			class="nav-tab <?php echo ($active_page=='general-options'?'nav-tab-active':'')?>"><?php _e('General Settings','wpmbil'); ?></a>
		<a href="#block-ip"
			class="nav-tab <?php echo ($active_page=='block-ip'?'nav-tab-active':'');?>"><?php _e('Blocked IPs','wpmbil'); ?></a>
		<a href="#block-domain"
			class="nav-tab <?php echo ($active_page=='block-domain'?'nav-tab-active':'');?>"><?php _e('Blocked Domains','wpmbil'); ?></a>
		<a href="#block-referrer"
			class="nav-tab <?php echo ($active_page=='block-referrer'?'nav-tab-active':'');?>"><?php _e('Blocked Referrers','wpmbil'); ?></a>
	</h2>
	<div id="general-options" class="nav-tab-panel" style="display: <?php echo ($active_page=='general-options'?'block':'none');?>">
                    <?php $WPMB_Settings->display_general_options_page(); ?>
                </div>
	<div id="block-ip" class="nav-tab-panel" style="display: <?php echo ($active_page=='block-ip'?'block':'none');?>">
                    <?php $WPMB_Settings->display_block_ip_page(); ?>
                </div>
	<div id="block-domain" class="nav-tab-panel" style="display: <?php echo ($active_page=='block-domain'?'block':'none');?>">
                    <?php $WPMB_Settings->display_block_domain_page(); ?>
                </div>
	<div id="block-referrer" class="nav-tab-panel" style="display: <?php echo ($active_page=='block-referrer'?'block':'none');?>">
                    <?php $WPMB_Settings->display_block_referrer_page(); ?>
                </div>
	<p>
		<a class="alignright button" href="javascript:void(0);"
			onclick="window.scrollTo(0,0);" style="margin: 3px 0 0 30px;"><?php _e('scroll to top', 'wpmbil' ); ?></a><br
			class="clear" />
	</p>
</div>
<?php
        }
    }
}

/*
 * Init settings page
 */
if (is_admin()) {
    if (! isset($GLOBALS['WPMB_Settings'])) {
        $GLOBALS['WPMB_Settings'] = new WPMB_Settings();
    }
}

