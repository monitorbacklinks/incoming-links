<?php
Class WPMB_Install{

    public static function install(){
        WPMB_Install::create_main_table();
        WPMB_Install::create_cron_table();
        WPMB_Install::create_block_ip_table();
        WPMB_Install::create_block_domain_table();
        WPMB_Install::create_config_option();
    }

    private static function create_main_table(){
        global $wpdb;
        $sql = "CREATE TABLE " . $wpdb->prefix . "backlinks (
          id mediumint(11) NOT NULL AUTO_INCREMENT,
          domain varchar(255) NOT NULL,
          referrer text NOT NULL,
          anchor_text varchar(255) NOT NULL,
          site_url text NOT NULL,
          time timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
          follow TINYINT(1) NOT NULL,
          highlight TINYINT(1) NOT NULL,
          UNIQUE KEY id (id)
        );";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    private static function create_cron_table(){
        global $wpdb;
        $sql = "CREATE TABLE " . $wpdb->prefix . "backlinks_cron (
          id mediumint(11) NOT NULL AUTO_INCREMENT,
          domain VARCHAR( 255 ) NOT NULL,
          referrer text NOT NULL,
          site_url text NOT NULL,
          time timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
          UNIQUE KEY id (id)
        );";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    private static function create_block_ip_table(){
        global $wpdb;
        $sql = "CREATE TABLE " . $wpdb->prefix . "backlinks_block_ip (
          id mediumint(11) NOT NULL AUTO_INCREMENT,
          IP  INT(11) UNSIGNED NOT NULL,
          UNIQUE KEY id (id)
        );";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    private static function create_block_domain_table(){
        global $wpdb;
        $sql = "CREATE TABLE " . $wpdb->prefix . "backlinks_block_domain (
          id mediumint(11) NOT NULL AUTO_INCREMENT,
          domain varchar(255) NOT NULL,
          referrer text NOT NULL,
          UNIQUE KEY id (id)
        );";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }


    public static function create_config_option(){
        //set default configs
        $options = array(
            'email_frequency'=>"0",
            'email_frequency_day' => "0",
            'email_frequency_hour_min' => "12:00",
            'cron'=>1,
            'cron_recurrence'=>'minutely',
            'cron_parse_count'=>1,
            'ban_domain'=>0,
            'items_per_page_main'=>10,
            'items_per_page_wait'=>10,
            'items_per_page_blocked'=>10,
        	'items_per_page_last_found' =>10,        		
            'exclude_domains_sing_excerpts'=>array(	'^/administrator/^', 																//joomla backend
            										'^/wp-admin/^',																		//wordpress backend
            										'^/admin/^',																		//other popular backends
            										'^/modcp/^','^/admincp/^',															//vbulletin backend
            										'/https?:\/\/(www.)?google.+.+\/$/',												//google searches http://www.google.com/ or http://www.google.com 
            										'/[&|\?](q|s|search)=(.*?)/',														//popular search queries for google, bing, sweetim and google cse
            										'/https?:\/\/.*(search\.|suche\.|mail\.|webmail\.|yandex\.ru|baidu\.com|translate\.).*\//',	//yandex, yahoo, baidu, google translate
            							            '/\/(search|s|yandsearch|l\.php|imgres|url)\?/',									//bing, yahoo, yandex, facebook like, google images
            							            '^crawler.php^', '^doubleclick.net^'												//other crawlers
        											),
            'secret_key'=> wp_hash( get_current_user_id( ) . get_bloginfo('name')  . uniqid("",true) ),
            'limit_links_domain' => 5,
          //  'mandrillApiKey' =>''
            'mailingList' => get_option( 'admin_email'),
        );
        add_option($name='wmpb_backlinks_config',json_encode($options), NULL, $autoload = 'yes');
    }

}
