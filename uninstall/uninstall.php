<?php
Class WPMB_Uninstall{

    public function uninstall(){
        WPMB_Uninstall::drop_main_table();
        WPMB_Uninstall::drop_cron_table();
        WPMB_Uninstall::drop_block_ip_table();
        WPMB_Uninstall::drop_block_domain_table();
        WPMB_Uninstall::remove_config_option();
    }

    private function drop_main_table(){
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "backlinks");
    }

    private function drop_cron_table(){
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "backlinks_cron");
    }

    private function drop_block_ip_table(){
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "backlinks_block_ip");
    }

    private function drop_block_domain_table(){
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "backlinks_block_domain");
    }

    private function remove_config_option(){
        delete_option('wmpb_backlinks_config');
    }
}

