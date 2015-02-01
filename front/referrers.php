<?php
if(!class_exists('WPMB_Referrers') && class_exists('WPMB_Config') ){

    Class WPMB_Referrers extends WPMB_Config{

        protected $blockedIps;
        protected $blockedDomains;

        function __construct(){
            add_action( 'init', array( &$this, 'init' ) );
        }

        public function init(){
            global $WPMB_Config;

            $referrer = (isset($_SERVER['HTTP_REFERER'])?esc_url($_SERVER['HTTP_REFERER']):'');
            $site_host = str_replace("www.", "", $_SERVER["HTTP_HOST"]);
            $ip = $_SERVER['SERVER_ADDR'];
            $site_url = 'http'.(empty($_SERVER['HTTPS'])?'':'s').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

            /*
             * Check if browser sends referrer url or not
             */
            if ($referrer == "") return false;
            $referrer_url_data = parse_url($referrer);//parse referrer

            /*
             * Check if not the current site or its www canonical version
             */
            if(strpos($referrer_url_data['host'],$site_host)!==FALSE) return false;

            /*
             * Check if host = IP - the most of them are spam
             */
            if(ip2long($referrer_url_data['host']) != -1 && ip2long($referrer_url_data['host']) !== FALSE) {
                // it's valid ip address, so return false..
                return false;
            }
            /*
             * Validate all domains containing at least one dot ".", return 0 or 1
             */
            if (!preg_match('/([0-9a-z-]+\.)?[0-9a-z-]+\.[a-z]{2,7}/', $referrer_url_data['host'])) {
                //Domain invalid
                return false;
            }

            /*
             * Check "Exclude domains using excerpts" - settings from admin side
             */
            if(
                isset($WPMB_Config->configs->exclude_domains_sing_excerpts)
                && count($WPMB_Config->configs->exclude_domains_sing_excerpts)
                && $referrer_url_data['path']
            ){
                foreach($WPMB_Config->configs->exclude_domains_sing_excerpts as $excerpt){
                	if (preg_match($excerpt,$referrer)) {
                        //we get url from admin side
                        return false;
                    }
                }
            }

            /*
             * Include block functions
             */
            include_once(dirname( __FILE__ ) . '/../admin/block_domains_ips.php');
            global $WPMB_Blocks;

            /*
             * Check if domain not in block list
             */
            $hosts = array();
            foreach($WPMB_Blocks->getBlockedDomains() as $domain){
                $parse = parse_url($domain->domain);
                $hosts[] = $parse['host'];
            }
            if(in_array( $referrer_url_data['host'] , $hosts )) return false;
            /*
             * Check if ip not in block list
             */
            $ips = array();
            foreach($WPMB_Blocks->getBlockedIps() as $ip_tmp){
                $ips[] = $ip_tmp->ip;
            }
            if(in_array( $ip , $ips )) return false;

            /*
            * Check referrer capabilities
            */
            if(is_user_logged_in() && isset($WPMB_Config->configs->ignore_roles_referer)){
                global $current_user;
                $roles = $current_user->roles;
                $user_role = array_shift($roles);
                if(in_array($user_role,$WPMB_Config->configs->ignore_roles_referer)){
                    //in admin settings we ignore current role for referer ...
                    return false;
                }
            }

            /*
             * Check if referrer not in ban/block list
             */
            if($this->check_referrer_in_block($referrer)) return false;

            /*
             * Check if link already checked and exist in main db table
             */
            if($this->check_exist_referrer($referrer)) return false;
            /*
             * Check if link already checked and exist in cron db table
             */
            if($this->check_exist_referrer_in_cron($referrer)) return false;

            /*
             * Check if Links/Domain allow add one more link
             */
            if($this->check_links_per_domain($referrer_url_data)) return false;

            /*
             * All checks done and we can add referrer ro cron table for check follow by cron
             */
            $this->add_to_cron($referrer,$site_url,$referrer_url_data);
        }

        /*
         * return true if full
         */
        private function check_links_per_domain($referrer_url_data){
            global $wpdb;
            global $WPMB_Config;
           $count = $wpdb->get_var($wpdb->prepare("
                SELECT sum(counts.count) as count
                FROM (
                    (SELECT COUNT(*) as count FROM " . $wpdb->prefix . "backlinks as main WHERE main.domain = %s)
                        UNION
                    (SELECT COUNT(*) as count FROM " . $wpdb->prefix . "backlinks_cron as cron WHERE cron.domain = %s)
                ) as counts
            ",$referrer_url_data['host'],$referrer_url_data['host']));
            if($count && (int)$WPMB_Config->configs->limit_links_domain <= (int)$count){
                return true;
            }else{
                return false;
            }
        }

        private function check_exist_referrer($referrer){
            global $wpdb;
            $count = $wpdb->get_var($wpdb->prepare("
                SELECT COUNT(*) as count
                FROM " . $wpdb->prefix . "backlinks
                WHERE REPLACE(referrer,'www.','') = %s
            ",str_replace('www.','',$referrer)));
            if($count){
                return true;
            }else{
                return false;
            }
        }

        private function check_referrer_in_block($referrer){
            global $wpdb;
            $count = $wpdb->get_var($wpdb->prepare("
                SELECT COUNT(*)
                FROM " . $wpdb->prefix . "backlinks_block_domain
                WHERE REPLACE(referrer,'www.','') = %s
            ",str_replace('www.','',$referrer)));
            if($count){
                return true;
            }else{
                return false;
            }
        }

        private function check_exist_referrer_in_cron($referrer){
            global $wpdb;
            $count = $wpdb->get_var($wpdb->prepare("
                SELECT COUNT(*)
                FROM " . $wpdb->prefix . "backlinks_cron
                WHERE REPLACE(referrer,'www.','') = %s
            ",str_replace('www.','',$referrer)));
            if($count){
                return true;
            }else{
                return false;
            }
        }

        private function add_to_cron($referrer,$site_url,$referrer_url_data){
            global $wpdb;
            $wpdb->insert(
                $wpdb->prefix . 'backlinks_cron',
                array(
                    'referrer' => $referrer,
                    'site_url' => $site_url,
                    'domain' => $referrer_url_data['host'],
                )
            );
        }

    }
}

/*
 * Get users Urls
 */
if(!is_admin()){
    $GLOBALS['WPMB_Referers'] = new WPMB_Referrers();
}

?>