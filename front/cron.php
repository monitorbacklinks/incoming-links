<?php
if(!class_exists('WPMB_Cron') && class_exists('WPMB_Config') ){

    Class WPMB_Cron extends WPMB_Config{

        function __construct(){
            add_action( 'init', array( &$this, 'init' ) );
        }

        public function init(){
            global $WPMB_Config;
            global $WPMB_Blocks;
            $settings = $WPMB_Config->configs;
            //If use Own Cron
            if($settings->cron == 0 && isset($_GET['action']) && $_GET['action']=='wpmb_check_referrers'){
                if($_GET['secret_key'] == $WPMB_Config->configs->secret_key){
                    //Run parse for own cron
                    $this->parse_referrers();
                    add_action('wp_loaded',array($this,'wp_die'));
                }else{
                    //incorrect secret key
                    die();
                }
            }
            if($settings->cron == 1){
                //If use Wp Cron
                add_action( 'prefix_'.$settings->cron_recurrence.'_event', array($this,'parse_referrers'));
                if ( ! wp_next_scheduled( 'prefix_'.$settings->cron_recurrence.'_event' )) {
                    wp_schedule_event( time(), $settings->cron_recurrence, 'prefix_'.$settings->cron_recurrence.'_event');
                }
            }
        }


        public function wp_die(){
            wp_die('Cron tasks successfully running');
        }

        /**
         * On the scheduled action hook, run a function or run if use own cron
         */
        public function parse_referrers() {
            // do parse every * time
            if (!class_exists('phpQuery')){
        		include_once(dirname( __FILE__ ) . '/../lib/phpQuery/phpQuery.php');
            }
            include_once(dirname( __FILE__ ) . '/../admin/block_domains_ips.php');
            global $WPMB_Blocks;
            global $WPMB_Config;
            global $wpdb;
            $settings = $WPMB_Config->configs;
            //get link for parse
            $referrers = $wpdb->get_results("
                SELECT *
                FROM " . $wpdb->prefix . "backlinks_cron
                LIMIT ". $settings->cron_parse_count ."
            ","OBJECT_K");
            if($referrers){
                $site_host = str_replace("www.", "", $_SERVER["HTTP_HOST"]);
                foreach($referrers as $referrer){
                    $document = phpQuery::newDocumentHTML($this->file_get_contents_curl($referrer->referrer));//get content
                    $follow = 0;
                    $find = false;
                    foreach($document->find('a') as $tag){ //go by each link
                        $href_data = @parse_url(pq($tag)->attr('href'));
                        if(strpos($href_data['host'],$site_host)!==FALSE){ //find the same host
                            $find = true;
                            $anchor_text = pq($tag)->text();
                            $rel = pq($tag)->attr('rel');
                            if($rel && strpos($rel,'nofollow')!==FALSE){
                                $follow = 0;
                            }else{
                                $follow = 1;
                            }
                            $this->move_to_main_table($referrer,$follow,$anchor_text);
                            break;
                        }
                    }

                    if($find === false){
                        /*Add referrer to ban list*/
                        $WPMB_Blocks->addBlockedReferrer($referrer->referrer);

                        if($settings->ban_domain){ //if After "X" referrals not found, block domain
                            $parse = parse_url($referrer->referrer);
                            $domain = $parse['scheme'] .'://'. $parse['host'];
                            $domain_links_count = $wpdb->get_var($wpdb->prepare("
                                SELECT COUNT(*)
                                FROM ".$wpdb->prefix ."backlinks_block_domain
                                WHERE domain=%s AND referrer!=''
                            ",$domain));
                            if($domain_links_count >= $settings->ban_domain){
                                $WPMB_Blocks->addBlockedDomain($domain);
                            }
                        }
                        //remove from cron table
                        $wpdb->delete( $wpdb->prefix . "backlinks_cron", array( 'id' => $referrer->id ), array( '%d' ) );
                    }
                }
            }
        }
        private function move_to_main_table($referrer,$follow,$anchor_text){
            global $wpdb;
            //remove from cron table
            $wpdb->delete( $wpdb->prefix . "backlinks_cron", array( 'id' => $referrer->id ), array( '%d' ) );

            //add to main table
            $referrer_url_data = parse_url($referrer->referrer);//parse referrer
            $wpdb->insert(
                $wpdb->prefix . "backlinks",
                array(
                    "domain" => $referrer_url_data['host'],
                    "referrer" => $referrer->referrer,
                    "anchor_text" => $anchor_text,
                    "site_url" => $referrer->site_url,
                    "time" => $referrer->time,
                    "follow" => $follow
                )
            );

        }


        private function file_get_contents_curl($url) {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 0);
            curl_setopt($ch, CURLOPT_USERAGENT,"MonitorBacklinksWP (+http://monitorbacklinks.com/blog/incoming-links/)");
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            $data = curl_exec($ch);
            curl_close($ch);

            return $data;
        }

    }
}

/*
 * Get users Urls
 */
if(!is_admin()){
    $GLOBALS['WPMB_Cron'] = new WPMB_Cron();
}
