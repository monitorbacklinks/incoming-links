<?php
if(!class_exists('WPMB_Blocks')){

    Class WPMB_Blocks{

        protected $blockedIps;
        protected $blockedDomains;

        /*
        * Ip methods -----------------------------------------
        */
        public function addBlockedIp($ip = 0 ){
            global $wpdb;
            global $WPMB_Blocks;
            global $WPMB_Settings;
            if(!$ip && $_POST['ip']){
                $ip = htmlspecialchars($_POST['ip']);
            }

            if(!filter_var($ip, FILTER_VALIDATE_IP)) {
                //invalid ip
                if(isset($_POST['ajax'])){
                    echo json_encode(array('status'=>false,'message'=>__('IP address invalid.','wpmbil')));
                    die();
                }
                return array('status'=>false,'message'=>__('IP address invalid.','wpmbil'));
            }
            $ips = array();
            foreach($WPMB_Blocks->getBlockedIps() as $ip_tmp){
                $ips[] = $ip_tmp->ip;
            }
            if(!in_array($ip,$ips)){
                //insert ip to ban
                $wpdb->insert(
                    $wpdb->prefix . "backlinks_block_ip",
                    array(
                        "IP" => ip2long($ip)
                    )
                );
                if($wpdb->insert_id){
                    //success
                    if(isset($_POST['ajax'])){
                        echo json_encode(array('status'=>true,'message'=>__('IP address successful blocked.','wpmbil'),'id'=> 'add_block_ip','ip'=> $ip));
                        die();
                    }
                    return array('status'=>true,'message'=>__('IP address successful blocked.','wpmbil'),'id'=> 'add_block_ip');
                }else{
                    //error
                    if(isset($_POST['ajax'])){
                        echo json_encode(array('status'=>false,'message'=>__('Error, can not add IP to block.','wpmbil')));
                        die();
                    }
                    return array('status'=>false,'message'=>__('Error, can not add IP to block.','wpmbil'));
                }
            }else{
                //already blocked
                if(isset($_POST['ajax'])){
                    echo json_encode(array('status'=>false,'message'=>__('IP address already blocked.','wpmbil')));
                    die();
                }
                return array('status'=>false,'message'=>__('IP address already blocked.','wpmbil'));
            }
        }

        public function getBlockedIps($perPage=999999){
            global $wpdb;
            //Filter by pagination
                $paged = max(1,(isset($_GET['pagedIp'])?$_GET['pagedIp']:'1'));
                $from = $perPage * $paged - $perPage;
                $limit = 'LIMIT '.$from.', '.$perPage;
            //---end Filter by pagination
            $ips = $wpdb->get_results("
                        SELECT id, INET_NTOA(IP) as ip
                        FROM " . $wpdb->prefix . "backlinks_block_ip ".$limit
                    ,"OBJECT_K");
            if(is_array($ips)){
                $this->blockedIps = $ips;
            }else{
                $this->blockedIps = array();
            }
            return $this->blockedIps;
        }

        public function getCountBlockedIps(){
            global $wpdb;
            return  $wpdb->get_var("SELECT count(*) FROM ".$wpdb->prefix."backlinks_block_ip");
        }

        public function deleteBlockedIp($id=0){
            if(!$id && $_POST['id']){
                $id = (int)$_POST['id'];
            }
            if(is_int($id)){
                global $wpdb;
                if($wpdb->delete( $wpdb->prefix . "backlinks_block_ip", array( 'id' => $id ), array( '%d' ) )){
                    //success
                    if(isset($_POST['ajax'])){
                        echo json_encode(array('status'=>true,'message'=>__('IP address successful removed from block list.','wpmbil'),'id'=>$id));
                        die();
                    }
                    return array('status'=>true,'message'=>__('IP address successful removed from block list.','wpmbil'));
                }else{
                    //error
                    if(isset($_POST['ajax'])){
                        echo json_encode(array('status'=>false,'message'=>__('Error, can not remove IP address from block list.','wpmbil')));
                        die();
                    }
                    return array('status'=>false,'message'=>__('Error, can not remove IP address from block list.','wpmbil'));
                }
            }else{
                //already blocked
                if(isset($_POST['ajax'])){
                    echo json_encode(array('status'=>false,'message'=>__('Error, incorrect data.','wpmbil')));
                    die();
                }
                return array('status'=>false,'message'=>__('Error, incorrect data.','wpmbil'));
            }
        }


        /*
         * Domain methods-----------------------------------------
         */
        public function addBlockedDomain($domain=''){
            global $wpdb;
            global $WPMB_Settings;
            global $WPMB_Blocks;
            if(!$domain && $_POST['domain']){
                $domain = htmlspecialchars($_POST['domain']);
            }
            $domain_parse = parse_url($domain);
            if(!isset($domain_parse['scheme'])){
                $domain = 'http://'.$domain;
            }
            $domain_parse = parse_url($domain);
            $domain = $domain_parse['scheme'] .'://'. $domain_parse['host'];

            if(!filter_var($domain, FILTER_VALIDATE_URL) || !preg_match('/([0-9a-z-]+\.)?[0-9a-z-]+\.[a-z]{2,7}/', $domain_parse['host'])) {
                //invalid url
                if(isset($_POST['ajax'])){
                    echo json_encode(array('status'=>false,'message'=>__('Domain address invalid.','wpmbil')));
                    die();
                }
                return array('status'=>false,'message'=>__('Domain address invalid.','wpmbil'));
            }
            $domains = array();
            foreach($WPMB_Blocks->getBlockedDomains() as $domain_tmp){
                $domains[] = $domain_tmp->domain;
            }
            if(!in_array($domain,$domains)){
                //insert domain to ban
                $wpdb->insert(
                    $wpdb->prefix . "backlinks_block_domain",
                    array(
                        'domain' =>  $domain 
                    )
                );
                if($wpdb->insert_id){
                    //success
                    if(isset($_POST['ajax'])){
                        echo json_encode(array('status'=>true,'message'=>__('Domain address successful blocked.','wpmbil'),'id'=> 'add_block_domain','domain'=> $domain));
                        die();
                    }
                    return array('status'=>true,'message'=>__('Domain address successful blocked.','wpmbil'),'id'=> 'add_block_domain','domain'=> $domain);
                }else{
                    //error
                    if(isset($_POST['ajax'])){
                        echo json_encode(array('status'=>false,'message'=>__('Error, can not add Domain to block.','wpmbil')));
                        die();
                    }
                    return array('status'=>false,'message'=>__('Error, can not add Domain to block.','wpmbil'));
                }
            }else{
                //already blocked
                if(isset($_POST['ajax'])){
                    echo json_encode(array('status'=>false,'message'=>__('Domain address already blocked.','wpmbil')));
                    die();
                }
                return array('status'=>false,'message'=>__('Domain address already blocked.','wpmbil'));
            }
        }

        public function getBlockedDomains($perPage=999999){
            global $wpdb;
            //Filter by pagination
                $paged = max(1,(isset($_GET['pagedDomains'])?$_GET['pagedDomains']:'1'));
                $from = $perPage * $paged - $perPage;
                $limit = 'LIMIT '.$from.', '.$perPage;
            //---end Filter by pagination
            $domains = $wpdb->get_results("
                        SELECT id,domain
                        FROM " . $wpdb->prefix . "backlinks_block_domain
                        WHERE domain!='' AND referrer='' ".$limit
                    ,"OBJECT_K");
            if(is_array($domains)){
                $this->blockedDomains = $domains;
            }else{
                $this->blockedDomains = array();
            }
            return $this->blockedDomains;
        }

        public function getCountBlockedDomains(){
            global $wpdb;
            return  $wpdb->get_var("SELECT count(*) FROM ".$wpdb->prefix."backlinks_block_domain  WHERE domain!='' AND referrer=''");
        }

        public function deleteBlockedDomain($id=0){
            if(!$id && $_POST['id']){
                $id = (int)$_POST['id'];
            }
            if(is_int($id)){
                global $wpdb;
                if($wpdb->delete( $wpdb->prefix . "backlinks_block_domain", array( 'id' => $id ), array( '%d' ) )){
                    //success
                    if(isset($_POST['ajax'])){
                        echo json_encode(array('status'=>true,'message'=>__('Domain address successful removed from block list.','wpmbil'),'id'=>$id));
                        die();
                    }
                    return array('status'=>true,'message'=>__('Domain address successful removed from block list.','wpmbil'));
                }else{
                    //error
                    if(isset($_POST['ajax'])){
                        echo json_encode(array('status'=>false,'message'=>__('Error, can not remove Domain address from block list.','wpmbil')));
                        die();
                    }
                    return array('status'=>false,'message'=>__('Error, can not remove Domain address from block list.','wpmbil'));
                }
            }else{
                //already blocked
                if(isset($_POST['ajax'])){
                    echo json_encode(array('status'=>false,'message'=>__('Error, incorrect data.','wpmbil')));
                    die();
                }
                return array('status'=>false,'message'=>__('Error, incorrect data.','wpmbil'));
            }
        }

        /*
        * Blocked Referrers methods-----------------------------------------
        */
        public function getBlockedReferrers($perPage=999999){
            global $wpdb;
            //Filter by pagination
                $paged = max(1,(isset($_GET['pagedRef'])?$_GET['pagedRef']:'1'));
                $from = $perPage * $paged - $perPage;
                $limit = 'LIMIT '.$from.', '.$perPage;
            //---end Filter by pagination
            $referrers = $wpdb->get_results("
                        SELECT id,domain,referrer
                        FROM " . $wpdb->prefix . "backlinks_block_domain
                        WHERE domain!='' AND referrer!=''".$limit
                    ,"OBJECT_K");
            if(is_array($referrers)){
                $this->blockedReferrers = $referrers;
            }else{
                $this->blockedReferrers = array();
            }
            return $this->blockedReferrers;
        }

        public function getCountBlockedReferrers(){
            global $wpdb;
            return  $wpdb->get_var("SELECT count(*) FROM ".$wpdb->prefix."backlinks_block_domain WHERE domain!='' AND referrer!=''");
        }

        public function addBlockedReferrer($referrer=''){
            global $wpdb;
            global $WPMB_Blocks;

            if(!$referrer && $_POST['referrer']){
                $referrer = htmlspecialchars($_POST['referrer']);
            }

            $referrer_parse = parse_url($referrer);
            if(!$referrer_parse['scheme']){
                $referrer = 'http://'.$referrer;
            }
                        
            $referrer_parse = parse_url($referrer);
            $domain = $referrer_parse['scheme'] .'://'. $referrer_parse['host'];
            if(!filter_var($domain, FILTER_VALIDATE_URL) || !preg_match('/([0-9a-z-]+\.)?[0-9a-z-]+\.[a-z]{2,7}/', $referrer_parse['host'])) {
                //invalid url
                if(isset($_POST['ajax'])){
                    echo json_encode(array('status'=>false,'message'=>__('Link address invalid.','wpmbil')));
                    die();
                }
                return array('status'=>false,'message'=>__('Link address invalid.','wpmbil'));
            }
            $referrers = array();
            foreach($WPMB_Blocks->getBlockedReferrers() as $referrer_tmp){
                $referrers[] = $referrer_tmp->domain;
            }
            if(!in_array($referrer,$referrers)){
                $parse = parse_url($referrer);
                $domain = $parse['scheme'] .'://'. $parse['host'];

                //insert referrer to ban
                $wpdb->insert(
                    $wpdb->prefix . "backlinks_block_domain",
                    array(
                        'domain' =>  $domain,
                        'referrer' =>  $referrer
                    )
                );
                if($wpdb->insert_id){
                    //success
                    if(isset($_POST['ajax'])){
                        echo json_encode(array('status'=>true,'message'=>__('Link(referrer) successful blocked.','wpmbil'),'id'=> 'add_block_referrer','domain'=> $domain,'referrer'=> $referrer));
                        die();
                    }
                    return array('status'=>true,'message'=>__('Link(referrer) successful blocked.','wpmbil'),'id'=> 'add_block_referrer','domain'=> $domain,'referrer'=> $referrer);
                }else{
                    //error
                    if(isset($_POST['ajax'])){
                        echo json_encode(array('status'=>false,'message'=>__('Error, can not add Link to block.','wpmbil')));
                        die();
                    }
                    return array('status'=>false,'message'=>__('Error, can not add Link to block.','wpmbil'));
                }
            }else{
                //already blocked
                if(isset($_POST['ajax'])){
                    echo json_encode(array('status'=>false,'message'=>__('Link already blocked.','wpmbil')));
                    die();
                }
                return array('status'=>false,'message'=>__('Link already blocked.','wpmbil'));
            }
        }

        public function deleteBlockedReferrer($id=0){
            if(!$id && $_POST['id']){
                $id = (int)$_POST['id'];
            }
            if(is_int($id)){
                global $wpdb;
                if($wpdb->delete( $wpdb->prefix . "backlinks_block_domain", array( 'id' => $id ), array( '%d' ) )){
                    //success
                    if(isset($_POST['ajax'])){
                        echo json_encode(array('status'=>true,'message'=>__('Link(referrer) successful removed from block list.','wpmbil'),'id'=>$id));
                        die();
                    }
                    return array('status'=>true,'message'=>__('Link(referrer) successful removed from block list.','wpmbil'));
                }else{
                    //error
                    if(isset($_POST['ajax'])){
                        echo json_encode(array('status'=>false,'message'=>__('Error, can not remove Link(referrer) from block list.','wpmbil')));
                        die();
                    }
                    return array('status'=>false,'message'=>__('Error, can not remove Link(referrer) from block list.','wpmbil'));
                }
            }else{
                //already blocked
                if(isset($_POST['ajax'])){
                    echo json_encode(array('status'=>false,'message'=>__('Error, incorrect data.','wpmbil')));
                    die();
                }
                return array('status'=>false,'message'=>__('Error, incorrect data.','wpmbil'));
            }
        }
        public function url_display($url,$length){
        	$url = preg_replace('/\?.*/', '', $url);
        	if (strlen($url) > $length){
        		$url = substr($url,0,$length)." ...";
        	}
        	return $url;
        }
    }
    if(!isset($GLOBALS['WPMB_Blocks'])){
        $GLOBALS['WPMB_Blocks'] = new WPMB_Blocks();
    }
}

