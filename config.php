<?php

if(!class_exists('WPMB_Config')){

    Class WPMB_Config{

        public $configs; //string
        public $plugin_path; //string
        public $plugin_url; //string

        public function __construct()
        {
            $this->getPluginPath();

            // add new intervals to cron
            add_filter( 'cron_schedules', array($this,'add_cron_intervals') );

            // get plugin config
            add_action( 'init', array($this,'getPluginConfigs'),1 );

            //include scripts
            add_action( 'admin_print_scripts-index.php', array( $this, 'includeScripts'));
            add_action( 'admin_print_scripts-toplevel_page_wpmb-dashboard', array( $this, 'includeScripts' ));
            add_action( 'admin_print_scripts-incoming-links_page_wpmb-settings', array( $this, 'includeScripts' ));


            //include styles
            add_action( 'admin_print_styles-index.php', array( $this, 'includeStyles'));
            add_action( 'admin_print_styles-toplevel_page_wpmb-dashboard', array( $this, 'includeStyles' ));
            add_action( 'admin_print_styles-incoming-links_page_wpmb-settings', array( $this, 'includeStyles' ));
        }

        /*
        * Include script for settings actions
        */
        public function includeScripts(){
            wp_enqueue_script( 'wpmb_js', plugins_url('/admin/js/wpmb.js', __FILE__), array('jquery') );
        }
        public function includeStyles(){
        	wp_enqueue_style( 'wpmb_css',plugins_url('/admin/css/wpmb.css', __FILE__), false, '1.0.0' );
        }

        private function getPluginPath(){
            /*
            * Set Plugin Path
            */
            $this->plugin_path = dirname(__FILE__);

            /*
             * Set Plugin URL
             */
            $this->plugin_url = plugins_url('', __FILE__);
        }

        public function getPluginConfigs(){
            /*
             * Get base configs
             */
            if(get_option('wmpb_backlinks_config')){
                $this->configs = json_decode(get_option('wmpb_backlinks_config'));
            }else{
                $this->configs = array();
            }
        }

        public function add_cron_intervals( $schedules ) {

            $schedules['minutely'] = array( // Provide the programmatic name to be used in code
                'interval' => 60, // Intervals are listed in seconds
                'display' => __('Every 60 Seconds','wpmbil') // Easy to read display name
            );
            $schedules['5minute'] = array( // Provide the programmatic name to be used in code
                'interval' => 360, // Intervals are listed in seconds
                'display' => __('Every 5 minutes','wpmbil') // Easy to read display name
            );
            $schedules['twicehourly'] = array( // Provide the programmatic name to be used in code
                'interval' => 1800, // Intervals are listed in seconds
                'display' => __('Twice per Hour','wpmbil') // Easy to read display name
            );
            $schedules['weekly'] = array( // Provide the programmatic name to be used in code
                'interval' => 604800, // Intervals are listed in seconds
                'display' => __('Weekly','wpmbil') // Easy to read display name
            );
            $schedules['monthly'] = array( // Provide the programmatic name to be used in code
                'interval' => 2629743, // Intervals are listed in seconds
                'display' => __('Monthly','wpmbil') // Easy to read display name
            );

            return $schedules; // Do not forget to give back the list of schedules!
        }
    }

}
$GLOBALS['WPMB_Config'] = new WPMB_Config();


