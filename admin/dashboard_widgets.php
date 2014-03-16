<?php
if(!class_exists('WPMB_Dashboard_Widgets') && class_exists('WPMB_Dashboard')){

    Class WPMB_Dashboard_Widgets extends WPMB_Dashboard{

        /**
         * Add a widget to the dashboard.
         *
         * This function is hooked into the 'wp_dashboard_setup' action below.
         */
        function __construct(){
            add_action( 'wp_dashboard_setup',array( $this, 'init' ) );
        }

        public function init(){

            /*
             * add widget latest links
             */

        	if (!current_user_can('manage_options')){
        		return;
        	}

            wp_add_dashboard_widget(
                'wpmb-latest-links',         // Widget slug.
                __('Recent Backlinks','wpmbil'),         // Title.
                array($this,'wpmb_latest_links') // Display function.
            );

            wp_add_dashboard_widget(
                'wpmb-statistic-links',         // Widget slug.
                __('Backlinks Statistic (last 30 days)','wpmbil'),         // Title.
                array($this,'wpmb_statistic_links') // Display function.
            );


            // Globalize the metaboxes array, this holds all the widgets for wp-admin

            global $wp_meta_boxes;

            // Get the regular dashboard widgets array
            // (which has our new widget already but at the end)

            $normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];

            // Backup and delete our new dashboard widget from the end of the array

            $latest_links_widget_backup = array( 'wpmb-latest-links' => $normal_dashboard['wpmb-latest-links'] );
            unset( $normal_dashboard['wpmb-latest-links'] );

            $stat_widget_backup = array( 'wpmb-statistic-links' => $normal_dashboard['wpmb-statistic-links'] );
            unset( $normal_dashboard['wpmb-statistic-links'] );


            // Merge the two arrays together so our widget is at the beginning

            $sorted_dashboard = array_merge( $stat_widget_backup, $normal_dashboard );
            $sorted_dashboard = array_merge( $latest_links_widget_backup, $sorted_dashboard );

            // Save the sorted array back into the original metaboxes

            $wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;



        }

        /**
         * Create the function to output the contents of our Dashboard Widget.
         */
        function wpmb_latest_links() {
            global $wpdb;
            $backlinks = $wpdb->get_results("SELECT time,domain,referrer FROM ".$wpdb->prefix."backlinks  ORDER BY time DESC LIMIT 10");
            if(count($backlinks)){
                ?>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th><?php _e('Date', 'wpmbil'); ?></th>
                            <th><?php _e('Domain', 'wpmbil'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        foreach($backlinks as $backlink){
                            ?>
                            <tr>
                                <td>
                                    <?php echo date('Y-m-d H:i:s',strtotime($backlink->time)); ?>
                                </td>
                                <td>
                                    <a href="<?php echo $backlink->referrer; ?>" target="_blank"><?php echo $backlink->domain; ?></a>
                                </td>
                            </tr>
                            <?php
                        }
                    ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th><?php _e('Date','wpmbil'); ?></th>
                            <th><?php _e('Domain','wpmbil'); ?></th>
                        </tr>
                    </tfoot>
                </table>
                <br>
                <a href="admin.php?page=wpmb-dashboard" class="button-primary right"><?php _e('View Links','wpmbil'); ?></a>
                <div class="clear"></div>
                <?php
            }
        }

        function wpmb_statistic_links(){
            global $wpdb;
            $stat_data = $wpdb->get_results("SELECT COUNT(id) as count, time
                                             FROM ".$wpdb->prefix."backlinks
                                             GROUP BY DAY(time)
											 ORDER BY time
											 LIMIT 30
                                            ");
            if($stat_data){
                ?>
                <script type="text/javascript" src="https://www.google.com/jsapi"></script>
                <script type="text/javascript">
                    google.load("visualization", "1", {packages:["corechart"]});
                    google.setOnLoadCallback(function () {
                        var data = google.visualization.arrayToDataTable([
                            ['<?php _e('Days','wpmbil'); ?>', '<?php _e('Backlinks','wpmbil'); ?>'],
                            <?php
							for ($i=30;$i>0;$i--){
                           		$complete_data[date("Y-m-d",strtotime("-$i days"))] = 0;
                           	}

                            foreach($stat_data as $item){
                            	$complete_data[date("Y-m-d",strtotime($item->time))] = $item->count;
                            }

                            foreach($complete_data as $item => $value){
	                           	echo "['".$item."',".$value."],";
                            }
                            ?>
                        ]);

                        var options = {
                            hAxis: {title: '<?php _e('Days','wpmbil'); ?>',  titleTextStyle: {color: '#333'}},
                            legend: {position: 'none'},
                            chartArea: {width: '80%'},
                            vAxis: {minValue: 0},
              			  	hAxis: { showTextEvery: 7},
              			  	title: '<?php _e('Backlinks / Day','wpmbil'); ?>'
                        };

                        var chart = new google.visualization.AreaChart(document.getElementById('backlink_chart'));
                        chart.draw(data, options);
                    });
                </script>
                <div id="backlink_chart" class="widefat" style="height: 350px;"></div>
                <?php
            }
        }


    }
}

/*
 * Init settings page
 */
if(is_admin()){
    if(!isset($GLOBALS['WPMB_Dashboard_Widgets'])){
        $GLOBALS['WPMB_Dashboard_Widgets'] = new WPMB_Dashboard_Widgets();
    }
}

