<?php
if (! class_exists('WPMB_Dashboard') && class_exists('WPMB_Blocks')) {

    class WPMB_Dashboard extends WPMB_Blocks
    {

        function __construct()
        {
            add_action('init', array(
                $this,
                'init'
            ));
        }

        public function init()
        {
            // Init remove row from main table
            add_action('wp_ajax_delete_backlink', array(
                $this,
                'deleteBacklink'
            ));
            
            // Init remove row from cron table
            add_action('wp_ajax_delete_cron_item', array(
                $this,
                'deleteBlockedDomain'
            ));
            
            // set highlight backlink
            add_action('wp_ajax_highlight_backlink', array(
                $this,
                'highlightBacklink'
            ));
            
            // set unhighlight backlink
            add_action('wp_ajax_unhighlight_backlink', array(
                $this,
                'unhighlightBacklink'
            ));
        }
        
        // remove highlight from backlink
        public function unhighlightBacklink($id = 0)
        {
            global $wpdb;
            $action = $_POST['action'];
            if (! $action) {
                $action = 'unhighlight_backlink'; // set default action
            }
            if (! $id && $_POST['id']) {
                $id = (int) $_POST['id'];
            }
            if (is_int($id)) {
                global $wpdb;
                $highlight = $wpdb->get_var($wpdb->prepare("SELECT highlight FROM " . $wpdb->prefix . "backlinks WHERE id= %d", $id));
                if ($highlight) {
                    // set unhighlight to item
                    if ($wpdb->update($wpdb->prefix . "backlinks", array(
                        'highlight' => 0
                    ), array(
                        'id' => $id
                    ))) {
                        if (isset($_POST['ajax'])) {
                            echo json_encode(array(
                                'status' => true,
                                'message' => __('Backlink successful unhighlighted.', 'wpmbil'),
                                'id' => $id,
                                'action' => $action
                            ));
                            die();
                        }
                        return array(
                            'status' => true,
                            'message' => __('Backlink successful unhighlighted.', 'wpmbil'),
                            'id' => $id,
                            'action' => $action
                        );
                    } else {
                        // can't update
                        if (isset($_POST['ajax'])) {
                            echo json_encode(array(
                                'status' => false,
                                'message' => __('Error, incorrect data.', 'wpmbil'),
                                'id' => $id,
                                'action' => $action
                            ));
                            die();
                        }
                        return array(
                            'status' => false,
                            'message' => __('Error, incorrect data.', 'wpmbil'),
                            'id' => $id,
                            'action' => $action
                        );
                    }
                } else {
                    // already unhighlight
                    if (isset($_POST['ajax'])) {
                        echo json_encode(array(
                            'status' => false,
                            'message' => __('Item already unhighlighted.', 'wpmbil'),
                            'id' => $id,
                            'action' => $action
                        ));
                        die();
                    }
                    return array(
                        'status' => false,
                        'message' => __('Item already unhighlighted.', 'wpmbil'),
                        'id' => $id,
                        'action' => $action
                    );
                }
            } else {
                // incorrect data
                if (isset($_POST['ajax'])) {
                    echo json_encode(array(
                        'status' => false,
                        'message' => __('Error, incorrect data.', 'wpmbil'),
                        'id' => $id,
                        'action' => $action
                    ));
                    die();
                }
                return array(
                    'status' => false,
                    'message' => __('Error, incorrect data.', 'wpmbil'),
                    'id' => $id,
                    'action' => $action
                );
            }
        }
        
        // set highlight backlink
        public function highlightBacklink($id = 0)
        {
            global $wpdb;
            $action = $_POST['action'];
            if (! $action) {
                $action = 'highlight_backlink'; // set default action
            }
            if (! $id && $_POST['id']) {
                $id = (int) $_POST['id'];
            }
            if (is_int($id)) {
                global $wpdb;
                $highlight = $wpdb->get_var($wpdb->prepare("SELECT highlight FROM " . $wpdb->prefix . "backlinks WHERE id= %d", $id));
                if (! $highlight) {
                    // set highlight to item
                    if ($wpdb->update($wpdb->prefix . "backlinks", array(
                        'highlight' => 1
                    ), array(
                        'id' => $id
                    ))) {
                        if (isset($_POST['ajax'])) {
                            echo json_encode(array(
                                'status' => true,
                                'message' => __('Backlink successful highlighted.', 'wpmbil'),
                                'id' => $id,
                                'action' => $action
                            ));
                            die();
                        }
                        return array(
                            'status' => true,
                            'message' => __('Backlink successful highlighted.', 'wpmbil'),
                            'id' => $id,
                            'action' => $action
                        );
                    } else {
                        // can't update
                        if (isset($_POST['ajax'])) {
                            echo json_encode(array(
                                'status' => false,
                                'message' => __('Error, incorrect data.', 'wpmbil'),
                                'id' => $id,
                                'action' => $action
                            ));
                            die();
                        }
                        return array(
                            'status' => false,
                            'message' => __('Error, incorrect data.', 'wpmbil'),
                            'id' => $id,
                            'action' => $action
                        );
                    }
                } else {
                    // already highlight
                    if (isset($_POST['ajax'])) {
                        echo json_encode(array(
                            'status' => false,
                            'message' => __('Item already highlighted.', 'wpmbil'),
                            'id' => $id,
                            'action' => $action
                        ));
                        die();
                    }
                    return array(
                        'status' => false,
                        'message' => __('Item already highlighted.', 'wpmbil'),
                        'id' => $id,
                        'action' => $action
                    );
                }
            } else {
                // incorrect data
                if (isset($_POST['ajax'])) {
                    echo json_encode(array(
                        'status' => false,
                        'message' => __('Error, incorrect data.', 'wpmbil'),
                        'id' => $id,
                        'action' => $action
                    ));
                    die();
                }
                return array(
                    'status' => false,
                    'message' => __('Error, incorrect data.', 'wpmbil'),
                    'id' => $id,
                    'action' => $action
                );
            }
        }

        public function deleteBacklink($id = 0)
        {
            $action = $_POST['action'];
            if (! $action) {
                $action = 'delete_backlink'; // set default action
            }
            if (! $id && $_POST['id']) {
                $id = (int) $_POST['id'];
            }
            if (is_int($id)) {
                global $wpdb;
                if ($wpdb->delete($wpdb->prefix . "backlinks", array(
                    'id' => $id
                ), array(
                    '%d'
                ))) {
                    // success
                    if (isset($_POST['ajax'])) {
                        echo json_encode(array(
                            'status' => true,
                            'message' => __('Backlink successful removed from list.', 'wpmbil'),
                            'id' => $id,
                            'action' => $action
                        ));
                        die();
                    }
                    return array(
                        'status' => true,
                        'message' => __('Backlink successful removed from list.', 'wpmbil')
                    );
                } else {
                    // error
                    if (isset($_POST['ajax'])) {
                        echo json_encode(array(
                            'status' => false,
                            'message' => __('Error, can not remove Backlink from list.', 'wpmbil'),
                            'action' => $action
                        ));
                        die();
                    }
                    return array(
                        'status' => false,
                        'message' => __('Error, can not remove Backlink from list.', 'wpmbil'),
                        'action' => $action
                    );
                }
            } else {
                // incorrect data
                if (isset($_POST['ajax'])) {
                    echo json_encode(array(
                        'status' => false,
                        'message' => __('Error, incorrect data.', 'wpmbil'),
                        'action' => $action
                    ));
                    die();
                }
                return array(
                    'status' => false,
                    'message' => __('Error, incorrect data.', 'wpmbil'),
                    'action' => $action
                );
            }
        }

        public function getDashboardStatistic()
        {
            global $wpdb;
            $settings = json_decode(get_option('wmpb_backlinks_config'));
            // Filter by date
            if (isset($_GET['month']) && $_GET['month']) {
                if ($_GET['month'] == 'current') {
                    $where = ' WHERE YEAR(time) = YEAR(NOW()) AND MONTH(time) = MONTH(NOW()) ';
                } else 
                    if ((int) $_GET['month']) {
                        $where = ' WHERE time > NOW() - INTERVAL ' . (int) $_GET['month'] . ' MONTH';
                    }
            } else {
                $where = '';
            }
            // Filter by pagination
            $paged = max(1, (isset($_GET['paged']) ? $_GET['paged'] : '1'));
            $from = $settings->items_per_page_main * $paged - $settings->items_per_page_main;
            $limit = 'LIMIT ' . $from . ', ' . $settings->items_per_page_main;
            // ---end Filter by pagination
            $rows = $wpdb->get_results("
                                    SELECT *
                                    FROM " . $wpdb->prefix . "backlinks
                                    " . $where . "
                                    ORDER BY time DESC
                                    " . $limit . "
                                ");
            return $rows;
        }

        public function getCountOfTotalReferrers()
        {
            global $wpdb;
            if (isset($_GET['month']) && $_GET['month']) {
                if ($_GET['month'] == 'current') {
                    $where = ' WHERE YEAR(time) = YEAR(NOW()) AND MONTH(time) = MONTH(NOW()) ';
                } else 
                    if ((int) $_GET['month']) {
                        $where = ' WHERE time > NOW() - INTERVAL ' . (int) $_GET['month'] . ' MONTH';
                    }
            } else {
                $where = '';
            }
            $count = $wpdb->get_var("
                                    SELECT count(*)
                                    FROM " . $wpdb->prefix . "backlinks
                                    " . $where . "
                                ");
            return $count;
        }

        public function getWaitingList()
        {
            global $wpdb;
            $settings = json_decode(get_option('wmpb_backlinks_config'));
            // Filter by pagination
            $paged = max(1, (isset($_GET['pagedWl']) ? $_GET['pagedWl'] : '1'));
            $from = $settings->items_per_page_wait * $paged - $settings->items_per_page_wait;
            $limit = 'LIMIT ' . $from . ', ' . $settings->items_per_page_wait;
            // ---end Filter by pagination
            return $wpdb->get_results("
                SELECT referrer,time
                FROM " . $wpdb->prefix . "backlinks_cron
                " . $limit);
        }

        public function getCountOfTotalWaitingList()
        {
            global $wpdb;
            return $wpdb->get_var("SELECT count(*) FROM " . $wpdb->prefix . "backlinks_cron");
        }

        public static function dashboard_page($options = '')
        {
            global $wpdb;
            global $WPMB_Dashboard;
            $settings = json_decode(get_option('wmpb_backlinks_config'));
            $total = $WPMB_Dashboard->getCountOfTotalReferrers();
            $rows = $WPMB_Dashboard->getDashboardStatistic();
            $totalWl = $WPMB_Dashboard->getCountOfTotalWaitingList();
            $referrers = $WPMB_Dashboard->getWaitingList();
            $waitng_links_per_page = $settings->items_per_page_wait;
            $referrers_per_page = $settings->items_per_page_main;
            $paged = max(1, (isset($_GET['paged']) ? $_GET['paged'] : '1'));
            $index = $total - ($referrers_per_page * $paged - $referrers_per_page) + 1;
            ?>
<div class="wrap wpmb_dashboard">
                                <?php
            if (defined('DISABLE_WP_CRON') and DISABLE_WP_CRON and $settings->cron) {
                echo "<div class='error'><p><strong style='color: #dd3d36;'>" . __("Error:", 'wpmbil') . " </strong>" . __("WP_CRON is disabled. Pending incoming links won't be verified! Edit your <strong>wp-config.php</strong> file and set <strong>DISABLE_WP_CRON</strong> to <strong>FALSE</strong>", 'wpmbil') . "!</p></div>";
            }
            ?>           
                <form id="posts-filter" action="" method="get">
		<!-- Filter by Month -->
		<div class="alignleft actions">
			<select name="month">
                            <?php
            // get list of month
            $months[0] = 'All';
            $months[12] = 'Last year';
            $months[6] = 'Last 6 months';
            $months[3] = 'Last 3 months';
            $months[1] = 'Last month';
            $months['current'] = 'This month';
            
            if (isset($_GET['month'])) {
                $current = $_GET['month'];
            } else {
                $current = 0;
            }
            foreach ($months as $number => $month) {
                ?>
                                <option
					<?php selected( $current, $number); ?>
					value="<?php echo $number;?>"><?php echo $month;?></option>
                                <?php
            }
            ?>
                        </select> <input type="hidden" name="page"
				value="<?php echo $_GET['page'];?>"> <input type="submit" name=""
				id="post-query-submit" class="button" value="Filter"> <br
				class="clear">
			<br>
		</div>
	</form>
	<!-- /Filter by Month -->
	<div id="poststuff">
		<div id="post-body" class="metabox-holder">
			<!-- main content -->
			<div id="post-body-content">
				<div class="meta-box-sortables ui-sortable">
					<div class="postbox">
						<h3>
							<span><?php echo __('Valid Backlinks ( ','wpmbil').$total.__(' items )','wpmbil'); ?></span>
						</h3>
						<div class="inside">
							<div class="tablenav-pages">

								<span class="pagination-links">
                                            <?php
            $big = 999999999; // need an unlikely integer
            echo paginate_links(array(
                'base' => html_entity_decode(str_replace($big, '%#%', esc_url(get_pagenum_link($big)))),
                'format' => '',
                'current' => max(1, (isset($_GET['paged']) ? $_GET['paged'] : '1')),
                'total' => ceil($total / $referrers_per_page)
            ));
            ?>
                                            </span>
							</div>
							<br>
							<table class="wp-list-table widefat fixed" cellspacing="0">
								<thead>
									<tr>
										<th scope="col" id="id" class="manage-column column-id"
											style="">#</th>
										<th scope="col" id="domain"
											class="manage-column column-domain" style=""><?php _e('Domain','wpmbil'); ?></th>
										<th scope="col" id="type" class="manage-column column-type"
											style=""><?php _e('Type','wpmbil'); ?></th>
										<th scope="col" id="found" class="manage-column column-found"
											style=""><?php _e('Found On','wpmbil'); ?></th>
										<th scope="col" id="anchor-text"
											class="manage-column column-anchor-text" style=""><?php _e('Anchor Text','wpmbil'); ?></th>
										<th scope="col" id="url-from"
											class="manage-column column-url-from" style=""><?php _e('URL From','wpmbil'); ?></th>
										<th scope="col" id="url-to"
											class="manage-column column-url-to" style=""><?php _e('URL To','wpmbil'); ?></th>
										<th scope="col" id="remove"
											class="manage-column column-remove" style=""><?php _e('Actions','wpmbil'); ?></th>
									</tr>
								</thead>
								<tfoot>
									<tr>
										<th scope="col" id="id" class="manage-column column-id"
											style="">#</th>
										<th scope="col" id="domain"
											class="manage-column column-domain" style=""><?php _e('Domain','wpmbil'); ?></th>
										<th scope="col" id="type" class="manage-column column-type"
											style=""><?php _e('Type','wpmbil'); ?></th>
										<th scope="col" id="found" class="manage-column column-found"
											style=""><?php _e('Found On','wpmbil'); ?></th>
										<th scope="col" id="anchor-text"
											class="manage-column column-anchor-text" style=""><?php _e('Anchor Text','wpmbil'); ?></th>
										<th scope="col" id="url-from"
											class="manage-column column-url-from" style=""><?php _e('URL From','wpmbil'); ?></th>
										<th scope="col" id="url-to"
											class="manage-column column-url-to" style=""><?php _e('URL To','wpmbil'); ?></th>
										<th scope="col" id="remove"
											class="manage-column column-remove" style=""><?php _e('Actions','wpmbil'); ?></th>
									</tr>
								</tfoot>
								<tbody id="the-list">
                                            <?php
            foreach ($rows as $row) {
                $index --;
                ?>
                                                <tr
										class="format-standard hentry row-<?php echo $row->id;?><?php echo ($row->highlight?' highlight':'');?>">
										<td class="id"><?php echo $index;?>
                                                    </td>
										<td class="domain"><a href="<?php echo $row->referrer;?>"
											target="_blank">
                                                            <?php echo $row->domain;?>
                                                        </a></td>
										<td class="type">
											<div class="<?php echo ($row->follow?'':'no');?>follow">
                                                            <?php echo ($row->follow?'':'nofollow');?>
                                                        </div>
										</td>
										<td class="found">
                                                        <?php echo date('Y-m-d',strtotime($row->time));?>
                                                    </td>
										<td class="anchor-text">
                                                        <?php echo $row->anchor_text?$row->anchor_text:'N/A';?>
                                                    </td>
										<td class="url-from">
                                                        <?php $parse = parse_url($row->referrer); ?>
                                                        <a
											href="<?php echo $row->referrer;?>" target="_blank"
											title="<?php echo $row->referrer;?>"><?php echo $WPMB_Dashboard->url_display($parse['path'].(isset($parse['query'])?$parse['query']:''),40);?></a>
										</td>
										<td class="url-to">
                                                        <?php $parse = parse_url($row->site_url); ?>
                                                        <a
											href="<?php echo $row->site_url;?>" target="_blank"
											title="<?php echo $parse['path'].(isset($parse['query'])?$parse['query']:'');?>"><?php echo $WPMB_Dashboard->url_display($parse['path'].(isset($parse['query'])?$parse['query']:''),40);?></a>
										</td>
										<td class="actions">
											<form action="<?php echo $_SERVER['REQUEST_URI']; ?>"
												method="POST">
												<label> <select name="action" class="actionsSelect">
														<option value="" selected="selected"><?php _e('Select Action','wpmbil'); ?></option>
														<option value="delete_backlink"><?php _e('Remove','wpmbil'); ?></option>
														<option value="add_block_domain"><?php _e('Block Domain','wpmbil'); ?></option>
														<option
															value="<?php echo ($row->highlight?'unhighlight':'highlight');?>_backlink"><?php echo ($row->highlight?__('Unhighlight','wpmbil'):__('Highlight','wpmbil'));?></option>
												</select>
												</label> <input type="hidden" name="domain"
													value="<?php echo $row->domain;?>"> <input type="hidden"
													name="ajax" value="true"> <input type="hidden" name="id"
													value="<?php echo $row->id;?>">
											</form>
										</td>
									</tr>
                                            <?php
            }
            ?>

                                            </tbody>
							</table>
							<br>
							<div class="align-center">
								<em>
                                            <?php _e('The maximum number of links from the same domain is','wpmbil'); ?>
                                            <strong><?php echo $settings->limit_links_domain; ?></strong>.
                                            <?php _e('You can change it in the','wpmbil'); ?>
                                            <a
									href="admin.php?page=wpmb-settings"><?php _e('settings page','wpmbil'); ?></a>.
								</em>
							</div>
							<br>
							<div class="tablenav-pages">
								<span class="pagination-links">
                                            <?php
            $big = 999999999; // need an unlikely integer
            echo paginate_links(array(
                'base' => html_entity_decode(str_replace($big, '%#%', esc_url(get_pagenum_link($big)))),
                'format' => '?paged=%#%',
                'current' => max(1, (isset($_GET['paged']) ? $_GET['paged'] : '1')),
                'total' => ceil($total / $referrers_per_page)
            ));
            ?>
                                            </span>
							</div>
						</div>
						<!-- .inside -->
					</div>
					<!-- .postbox -->
				</div>
				<!-- .meta-box-sortables .ui-sortable -->
			</div>
			<!-- post-body-content -->

			<!-- sidebar -->
			<div id="postbox-container-1" class="postbox-container">

                                <?php
            if (defined('DISABLE_WP_CRON') and DISABLE_WP_CRON and $settings->cron) {
                echo "<div class='error'><p><strong style='color: #dd3d36;'>" . __("Error:", 'wpmbil') . " </strong>" . __("WP_CRON is disabled. Pending incoming links won't be verified! Edit your <strong>wp-config.php</strong> file and set <strong>DISABLE_WP_CRON</strong> to <strong>FALSE</strong>", 'wpmbil') . "!</p></div>";
            }
            ?>                         
                        
                            <div class="meta-box-sortables">

					<div class="postbox">

						<h3>
							<span><?php echo __('Referrers pending for verification ( ','wpmbil'). $totalWl .__(' items )','wpmbil'); ?></span>
						</h3>
						<div class="inside">
							<div class="tablenav-pages">
								<span class="pagination-links">
                                                <?php
            if ($totalWl && $waitng_links_per_page) {
                echo paginate_links(array(
                    'base' => '?page=' . $_GET['page'] . '&pagedWl=%#%',
                    'current' => max(1, (isset($_GET['pagedWl']) ? $_GET['pagedWl'] : '1')),
                    'format' => '?pagedWl=%#%',
                    'total' => ceil($totalWl / $waitng_links_per_page),
                    'add_args' => array(
                        'paged' => max(1, (isset($_GET['paged']) ? $_GET['paged'] : '1'))
                    )
                ));
            }
            ?>
                                            </span>
							</div>
							<br>
                                        <?php
            foreach ($referrers as $referrer) {
                ?>
                                            <table class="widefat">
								<tr valign="top">
									<td class="column-time">
                                                        <?php echo $referrer->time;?>
                                                    </td>
									<td class="column-referrer"><a
										href="<?php echo $referrer->referrer;?>" target="_blank"
										title="<?php echo $referrer->referrer;?>"><?php echo $WPMB_Dashboard->url_display($referrer->referrer,120);?></a>
									</td>
								</tr>
							</table>
                                            <?php
            }
            ?>
                                        <br>
							<div class="tablenav-pages">
								<span class="pagination-links">
                                                <?php
            if ($totalWl && $waitng_links_per_page) {
                echo paginate_links(array(
                    'base' => '?page=' . $_GET['page'] . '&pagedWl=%#%',
                    'current' => max(1, (isset($_GET['pagedWl']) ? $_GET['pagedWl'] : '1')),
                    'format' => '?pagedWl=%#%',
                    'total' => ceil($totalWl / $waitng_links_per_page),
                    'add_args' => array(
                        'paged' => max(1, (isset($_GET['paged']) ? $_GET['paged'] : '1'))
                    )
                ));
            }
            ?>
                                            </span>
							</div>
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

	<p>
		<a class="alignright button" href="javascript:void(0);"
			onclick="window.scrollTo(0,0);" style="margin: 3px 0 0 30px;"><?php _e('scroll to top','wpmbil'); ?></a><br
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
    if (! isset($GLOBALS['WPMB_Dashboard'])) {
        $GLOBALS['WPMB_Dashboard'] = new WPMB_Dashboard();
    }
}
