<?php
if(!class_exists('WPMB_Email') && class_exists('WPMB_Config') ){

    Class WPMB_Email extends WPMB_Config{
		function __construct(){
    		add_action( 'email_wpmb_event', array('WPMB_Email','send_emails'));
		}
        public static function send_emails(){
            global $WPMB_Config;
            global $WPMB_Email;
            global $wpdb;
            $settings = json_decode(get_option('wmpb_backlinks_config'));

            if($settings->email_frequency=='weekly'){
                $interval = 'WEEK';
            }else if($settings->email_frequency=='daily'){
                $interval = 'DAY';
            }
            //get all items with interval from settings
            $items = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'backlinks WHERE time BETWEEN date_sub(now(),INTERVAL 1 ' . $interval . ') and now()');
            if(count($items) && $settings->mailingList){

                $html =  '<style>';
                    $html .=  '
                        table.widefat td, .widefat th {
                            border-top-color: #fff;
                            border-bottom-color: #dfdfdf;
                            color: #555;
                            vertical-align: middle;
                            font-size: 12px;
                            padding: 4px 7px 2px;
                            border-width: 1px 0;
                            border-style: solid;
                        }
                    ';
                $html .=  '</style>';
                $html .=  __('Hello,','wpmbil');
                $html .= '<br/><br/>'.__('Bellow is a list with your new valid backlinks','wpmbil').' (generated '.$settings->email_frequency.'):<br/><br/>';
                $html .= '<table cellspacing="0" style="border-color: #dfdfdf;
                                                        background-color: #f9f9f9;
                                                        table-layout: fixed;
                                                        border-spacing: 0;
                                                        width: 100%;
                                                        clear: both;
                                                        margin: 0;
                                                        -webkit-border-radius: 3px;
                                                        border-radius: 3px;
                                                        border-width: 1px;
                                                        border-style: solid;
                                                        line-height: 1.4em;
                                                        color: #333;">';
                    $html .= '  <thead>
                                    <tr>
                                        <th width="5%" style="background: #f1f1f1;
                                                    background-image: -webkit-gradient(linear,left bottom,left top,from(#ececec),to(#f9f9f9));
                                                    background-image: -webkit-linear-gradient(bottom,#ececec,#f9f9f9);
                                                    background-image: -moz-linear-gradient(bottom,#ececec,#f9f9f9);
                                                    background-image: -o-linear-gradient(bottom,#ececec,#f9f9f9);
                                                    background-image: linear-gradient(to top,#ececec,#f9f9f9);
                                                    border-top-color: #fff;
                                                    border-bottom-color: #dfdfdf;
                                                    padding: 7px 7px 8px;
                                                    text-align: left;
                                                    line-height: 1.3em;
                                                    font-size: 14px;
                                                    border-width: 1px 0;
                                                    border-style: solid;" scope="col">#</th>
                                        <th width="20%" scope="col"  style="background: #f1f1f1;
                                                    background-image: -webkit-gradient(linear,left bottom,left top,from(#ececec),to(#f9f9f9));
                                                    background-image: -webkit-linear-gradient(bottom,#ececec,#f9f9f9);
                                                    background-image: -moz-linear-gradient(bottom,#ececec,#f9f9f9);
                                                    background-image: -o-linear-gradient(bottom,#ececec,#f9f9f9);
                                                    background-image: linear-gradient(to top,#ececec,#f9f9f9);
                                                    border-top-color: #fff;
                                                    border-bottom-color: #dfdfdf;
                                                    padding: 7px 7px 8px;
                                                    text-align: left;
                                                    line-height: 1.3em;
                                                    font-size: 14px;
                                                    border-width: 1px 0;
                                                    border-style: solid;">'.__('Domain','wpmbil').'</th>
                                        <th width="12%" scope="col"  style="background: #f1f1f1;
                                                    background-image: -webkit-gradient(linear,left bottom,left top,from(#ececec),to(#f9f9f9));
                                                    background-image: -webkit-linear-gradient(bottom,#ececec,#f9f9f9);
                                                    background-image: -moz-linear-gradient(bottom,#ececec,#f9f9f9);
                                                    background-image: -o-linear-gradient(bottom,#ececec,#f9f9f9);
                                                    background-image: linear-gradient(to top,#ececec,#f9f9f9);
                                                    border-top-color: #fff;
                                                    border-bottom-color: #dfdfdf;
                                                    padding: 7px 7px 8px;
                                                    text-align: left;
                                                    line-height: 1.3em;
                                                    font-size: 14px;
                                                    border-width: 1px 0;
                                                    border-style: solid;">'.__('Type','wpmbil').'</th>
                                        <th width="12%" scope="col" style="background: #f1f1f1;
                                                    background-image: -webkit-gradient(linear,left bottom,left top,from(#ececec),to(#f9f9f9));
                                                    background-image: -webkit-linear-gradient(bottom,#ececec,#f9f9f9);
                                                    background-image: -moz-linear-gradient(bottom,#ececec,#f9f9f9);
                                                    background-image: -o-linear-gradient(bottom,#ececec,#f9f9f9);
                                                    background-image: linear-gradient(to top,#ececec,#f9f9f9);
                                                    border-top-color: #fff;
                                                    border-bottom-color: #dfdfdf;
                                                    padding: 7px 7px 8px;
                                                    text-align: left;
                                                    line-height: 1.3em;
                                                    font-size: 14px;
                                                    border-width: 1px 0;
                                                    border-style: solid;">'.__('Found on','wpmbil').'</th>
                                        <th scope="col" style="background: #f1f1f1;
                                                    background-image: -webkit-gradient(linear,left bottom,left top,from(#ececec),to(#f9f9f9));
                                                    background-image: -webkit-linear-gradient(bottom,#ececec,#f9f9f9);
                                                    background-image: -moz-linear-gradient(bottom,#ececec,#f9f9f9);
                                                    background-image: -o-linear-gradient(bottom,#ececec,#f9f9f9);
                                                    background-image: linear-gradient(to top,#ececec,#f9f9f9);
                                                    border-top-color: #fff;
                                                    border-bottom-color: #dfdfdf;
                                                    padding: 7px 7px 8px;
                                                    text-align: left;
                                                    line-height: 1.3em;
                                                    font-size: 14px;
                                                    border-width: 1px 0;
                                                    border-style: solid;">'.__('Anchor Text','wpmbil').'</th>
                                    </tr>
                                </thead>
                    ';
                    $html .= '<tbody id="the-list">';
                    	$i=0;
                        foreach($items as $item){
                        	$i++;
                            $html .= '<tr>';
                                $html .= '<td style="border-top-color:#fff;
                                                                border-bottom-color:#dfdfdf;
                                                                color:#555;
                                                                vertical-align:middle;
                                                                font-size:12px;
                                                                padding:4px 7px 2px;
                                                                border-width:1px 0;
                                                                border-style:solid;
                                                                text-align:center">' . $i . '</td>';
                                $html .= '<td style="border-top-color:#fff;
                                                                border-bottom-color:#dfdfdf;
                                                                color:#555;
                                                                vertical-align:middle;
                                                                font-size:12px;
                                                                padding:4px 7px 2px;
                                                                border-width:1px 0;
                                                                border-style:solid;"><a href="' . $item->referrer . '" target="_blank">' . $item->domain . '</a></td>';
                                $html .= '<td style="border-top-color:#fff;
                                                                border-bottom-color:#dfdfdf;
                                                                color:#555;
                                                                vertical-align: middle;
                                                                font-size: 12px;
                                                                padding: 4px 7px 2px;
                                                                border-width: 1px 0;
                                                                border-style: solid;">' . ($item->follow?'FOLLOW':'NOFOLLOW') . '</td>';
                                $html .= '<td style="border-top-color: #fff;
                                                                border-bottom-color: #dfdfdf;
                                                                color: #555;
                                                                vertical-align: middle;
                                                                font-size: 12px;
                                                                padding: 4px 7px 2px;
                                                                border-width: 1px 0;
                                                                border-style: solid;">' . date('Y-m-d',strtotime($item->time)) . '</td>';
                                $html .= '<td style="border-top-color: #fff;
                                                                border-bottom-color: #dfdfdf;
                                                                color: #555;
                                                                vertical-align: middle;
                                                                font-size: 12px;
                                                                padding: 4px 7px 2px;
                                                                border-width: 1px 0;
                                                                border-style: solid;">' . ($item->anchor_text?$item->anchor_text:'N/A') . '</td>';
                            $html .= '</tr>';
                        }
                    $html .= '</tbody>';
                    $html .= '                                <tfoot>
                                    <tr>
                                        <th scope="col" style="background: #f1f1f1;
                                                    background-image: -webkit-gradient(linear,left bottom,left top,from(#ececec),to(#f9f9f9));
                                                    background-image: -webkit-linear-gradient(bottom,#ececec,#f9f9f9);
                                                    background-image: -moz-linear-gradient(bottom,#ececec,#f9f9f9);
                                                    background-image: -o-linear-gradient(bottom,#ececec,#f9f9f9);
                                                    background-image: linear-gradient(to top,#ececec,#f9f9f9);
                                                    border-top-color: #fff;
                                                    border-bottom-color: #dfdfdf;
                                                    padding: 7px 7px 8px;
                                                    text-align: left;
                                                    line-height: 1.3em;
                                                    font-size: 14px;
                                                    border-width: 1px 0;
                                                    border-style: solid;">#</th>
                                        <th scope="col" style="background: #f1f1f1;
                                                    background-image: -webkit-gradient(linear,left bottom,left top,from(#ececec),to(#f9f9f9));
                                                    background-image: -webkit-linear-gradient(bottom,#ececec,#f9f9f9);
                                                    background-image: -moz-linear-gradient(bottom,#ececec,#f9f9f9);
                                                    background-image: -o-linear-gradient(bottom,#ececec,#f9f9f9);
                                                    background-image: linear-gradient(to top,#ececec,#f9f9f9);
                                                    border-top-color: #fff;
                                                    border-bottom-color: #dfdfdf;
                                                    padding: 7px 7px 8px;
                                                    text-align: left;
                                                    line-height: 1.3em;
                                                    font-size: 14px;
                                                    border-width: 1px 0;
                                                    border-style: solid;">'.__('Domain','wpmbil').'</th>
                                        <th scope="col" style="background: #f1f1f1;
                                                    background-image: -webkit-gradient(linear,left bottom,left top,from(#ececec),to(#f9f9f9));
                                                    background-image: -webkit-linear-gradient(bottom,#ececec,#f9f9f9);
                                                    background-image: -moz-linear-gradient(bottom,#ececec,#f9f9f9);
                                                    background-image: -o-linear-gradient(bottom,#ececec,#f9f9f9);
                                                    background-image: linear-gradient(to top,#ececec,#f9f9f9);
                                                    border-top-color: #fff;
                                                    border-bottom-color: #dfdfdf;
                                                    padding: 7px 7px 8px;
                                                    text-align: left;
                                                    line-height: 1.3em;
                                                    font-size: 14px;
                                                    border-width: 1px 0;
                                                    border-style: solid;">Type</th>
                                        <th scope="col" style="background: #f1f1f1;
                                                    background-image: -webkit-gradient(linear,left bottom,left top,from(#ececec),to(#f9f9f9));
                                                    background-image: -webkit-linear-gradient(bottom,#ececec,#f9f9f9);
                                                    background-image: -moz-linear-gradient(bottom,#ececec,#f9f9f9);
                                                    background-image: -o-linear-gradient(bottom,#ececec,#f9f9f9);
                                                    background-image: linear-gradient(to top,#ececec,#f9f9f9);
                                                    border-top-color: #fff;
                                                    border-bottom-color: #dfdfdf;
                                                    padding: 7px 7px 8px;
                                                    text-align: left;
                                                    line-height: 1.3em;
                                                    font-size: 14px;
                                                    border-width: 1px 0;
                                                    border-style: solid;">'.__('Found On','wpmbil').'</th>
                                        <th scope="col" style="background: #f1f1f1;
                                                    background-image: -webkit-gradient(linear,left bottom,left top,from(#ececec),to(#f9f9f9));
                                                    background-image: -webkit-linear-gradient(bottom,#ececec,#f9f9f9);
                                                    background-image: -moz-linear-gradient(bottom,#ececec,#f9f9f9);
                                                    background-image: -o-linear-gradient(bottom,#ececec,#f9f9f9);
                                                    background-image: linear-gradient(to top,#ececec,#f9f9f9);
                                                    border-top-color: #fff;
                                                    border-bottom-color: #dfdfdf;
                                                    padding: 7px 7px 8px;
                                                    text-align: left;
                                                    line-height: 1.3em;
                                                    font-size: 14px;
                                                    border-width: 1px 0;
                                                    border-style: solid;">'.__('Anchor Text','wpmbil').'</th>
                                    </tr>
                                </tfoot>
                    ';
                $html .= '</table>';
                $html .= '<br/><br/><a href="' . admin_url( 'admin.php?page=wpmb-dashboard') . '" style="text-align: center; font-size: 11px; font-family: arial, sans-serif; color: white; font-weight: bold; border-color: #3079ed; background-color: #4d90fe; background-image: linear-gradient(top,#4d90fe,#4787ed); text-decoration: none; display:inline-block; height: 27px; padding-left: 8px; padding-right: 8px; line-height: 27px; border-radius: 2px; border-width: 1px;"> '.__('Detailed Report','wpmbil').'</a>';
                $html .= '<br/><br/><br/><div style="color:#777;font-size:0.8em;width:100%">'.__('This report was automatically generated by','wpmbil').' <a href="http://monitorbacklinks.com/blog/incoming-links/">Incoming Links</a> plugin. '.__('You can change email frequency from your','wpmbil').' <a href="' . admin_url( 'admin.php?page=wpmb-settings#email-frequency') . '">'.__('settings page','wpmbil').'</a>.<br/><br/><a href="https://monitorbacklinks.com/">&copy; Monitor Backlinks</a></div><br/><br/>';
                    //Default Wp Email
                    add_filter( 'wp_mail_content_type', array($WPMB_Email,'set_html_content_type') );
                    wp_mail( explode(',', $settings->mailingList) , __('New incoming links for','wpmbil').' '.get_bloginfo('name'), $html);
                    remove_filter( 'wp_mail_content_type', array($WPMB_Email,'set_html_content_type') );
                //}
            }
        }

        function set_html_content_type() {
            return 'text/html';
        }

    }
}

/*
 * Create new obj
 */
if(!is_admin()){
    $GLOBALS['WPMB_Email'] = new WPMB_Email();
}

