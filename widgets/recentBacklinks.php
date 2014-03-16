<?php
/*
 * Recent Backlinks Widget
 */
class WPMB_Recent_Backlinks_Widget extends WP_Widget {

    /**
     * Register widget with WordPress.
     */
    function __construct() {
        parent::__construct(
            'recent_backlinks_widget', // Base ID
            __('Recent Backlinks', 'wpmbil'), // Name
            array( 'description' => __( 'Display most recent backlinks.', 'wpmbil' ), ) // Args
        );
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance ) {
        global $wpdb;
        global $WPMB_Config;
        $title = apply_filters( 'widget_title', $instance['title'] );
        echo ' <!-- BEGIN Incoming Links Widget - http://monitorbacklinks.com/blog/incoming-links/ -->';
        echo $args['before_widget'];
        if ( ! empty( $title ) ) echo $args['before_title'] . $title . $args['after_title'];

        $backlinks = $wpdb->get_results($wpdb->prepare("SELECT domain,referrer,time FROM (SELECT * FROM ".$wpdb->prefix."backlinks ORDER BY time DESC) AS sorted GROUP BY domain ORDER BY time DESC LIMIT %d",$instance[ 'count' ]));
        if(count($backlinks)){
            ob_start();
            ?>
             <p></p>
            <ul>
            <?php
            foreach($backlinks as $backlink){
                ?>
                <li>
                    <div><small><em><?php echo esc_html($backlink->time); ?></em></small></div>
                    <a href="<?php echo esc_html($backlink->referrer); ?>" target="_blank" rel="nofollow"><?php echo esc_html($backlink->domain); ?></a>
                </li>
                <?php
            }
            ?>
            </ul>
            <?php

            if((int)$instance[ 'disable_credits' ] == 0) echo '<p><div style="text-align:right;width:100%;font-size:0.72em;">'.__('powered by','wpmbil').' <a href="http://monitorbacklinks.com/blog/incoming-links/" rel="nofollow" style="text-decoration:none;font-size:1em;">Incoming Links</a></div></p>';

            $widget_content = ob_get_contents();
            ob_end_clean();
            echo apply_filters( 'widget_html_content', $widget_content );
        }

        echo $args['after_widget'];
        echo '<!-- END Incoming Links Widget -->';
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance ) {
        $title = ( isset( $instance[ 'title' ] ) ? $instance[ 'title' ] : __( 'Recent backlinks', 'wpmbil' ) );
        $count = ( isset( $instance[ 'count' ] ) ? $instance[ 'count' ] : 10 );
        $disable_credits = (isset( $instance[ 'disable_credits' ] ) ? $instance[ 'disable_credits' ] : 0);
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:','wpmbil' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'Count:','wpmbil' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" type="number" required="required" value="<?php echo esc_attr( $count ); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'disable_credits' ); ?>"><?php _e( 'Disable "credits" on frontend:','wpmbil' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'disable_credits' ); ?>" name="<?php echo $this->get_field_name( 'disable_credits' ); ?>" type="checkbox" <?php checked( $disable_credits, 1 ); ?> value="1">
        </p>


    <?php
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['count'] = ( ! empty( $new_instance['count'] ) ) ? strip_tags( $new_instance['count'] ) : 10;
        $instance['disable_credits'] = ( ! empty( $new_instance['disable_credits'] ) ) ? strip_tags( $new_instance['disable_credits'] ) : 0;
        return $instance;
    }

} // class WPMB_Recent_Backlinks_Widget



// register WPMB_Recent_Backlinks_Widget widget
function register_Wpmb_Recent_Backlinks_Widget() {
    register_widget( 'WPMB_Recent_Backlinks_Widget' );
}
add_action( 'widgets_init', 'register_Wpmb_Recent_Backlinks_Widget' );

