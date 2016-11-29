<?php

namespace EventManagerIntegration\Widget;

class DisplayEvents extends \WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'display_events',
            __('Upcoming events', 'event-integration'),
            array(
                "description" => __('Display upcoming events from Event Manager API.', 'event-integration')
            )
        );
    }

    /**
    * Outputs the content for the current Display Events widget instance.
    *
    * @param array $args     Widget arguments.
    * @param array $instance Saved values from database.
    */
    public function widget($args, $instance)
    {
        if ( ! isset( $args['widget_id'] ) ) {
            $args['widget_id'] = $this->id;
        }
        $title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( 'Recent Posts' );
        $title = apply_filters( 'widget_title', $title, $instance, $this->id_base );
        $limit = isset($instance['limit']) ? intval($instance['limit']) : null;
        $days_ahead = isset($instance['days_ahead']) ? $instance['days_ahead'] : null;
        $show_content = isset( $instance['show_content'] ) ? $instance['show_content'] : false;
        $show_location = isset( $instance['show_location'] ) ? $instance['show_location'] : false;
        $content_limit = isset($instance['content_limit']) ? $instance['content_limit'] : null;

        $start_date = date('Y-m-d H:i:s', strtotime("today midnight"));
        $end_date = date('Y-m-d H:i:s', strtotime("tomorrow midnight +$days_ahead days") - 1);
        $events = $this->getEventsQuery($start_date, $end_date, $limit);

        ?>
        <?php echo $args['before_widget']; ?>
        <?php if ( $title ) {
            echo $args['before_title'] . $title . $args['after_title'];
        } ?>
        <ul>
        <?php if (!$events) : ?>
            <li><?php _e('No events found', 'event-integration'); ?></li>
        <?php else: ?>
        <?php foreach ($events as $event) : ?>
            <li>
            <?php if (! empty($event->post_title && isset($event->post_title))) : ?>
                <a href="<?php echo get_page_link($event->ID); ?>"><?php echo $event->post_title ?></a>
            <?php endif; ?>

            <?php if (! empty($event->start_date && isset($event->start_date))) : ?>
                <span class="event-date"><?php echo sprintf(__('Start: %s', 'event-integration'), $event->start_date) ?></span>
            <?php endif; ?>

            <?php if (! empty($event->end_date && isset($event->end_date))) : ?>
                <span class="event-date"><?php echo sprintf(__('End: %s', 'event-integration'), $event->end_date) ?></span>
            <?php endif; ?>

            <?php if (! empty($event->door_time && isset($event->door_time))) : ?>
               <span class="event-date"><?php echo sprintf(__('Door time: %s', 'event-integration'), $event->door_time) ?></span>
            <?php endif; ?>

            <?php if ($show_location && get_post_meta($event->ID, 'location', true)) : ?>
                <?php $location = get_post_meta($event->ID, 'location', true); ?>
                <span><?php echo sprintf(__('Location: %s', 'event-integration'), $location['post_title']) ?></span>
            <?php endif; ?>

            <?php if ($show_content && ! empty($event->post_content && isset($event->post_content))) : ?>
                <span>
                <?php
                    echo $this->descriptionLimit( $event->post_content, $content_limit );
                ?>
                </span>
            <?php endif; ?>

            </li>
        <?php endforeach; ?>
        <?php endif; ?>
        </ul>
        <?php echo $args['after_widget'];

    }

    /**
    * Handles updating the settings for the current Display Events widget instance.
    *
    * @param array $new_instance Values just sent to be saved.
    * @param array $old_instance Previously saved values from database.
    *
    * @return array Updated safe values to be saved.
    */
    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (! empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['limit'] = absint($new_instance['limit']);
        $instance['days_ahead'] = absint($new_instance['days_ahead']);
        $instance['show_location'] = isset( $new_instance['show_location'] ) ? (bool) $new_instance['show_location'] : false;
        $instance['show_content'] = isset( $new_instance['show_content'] ) ? (bool) $new_instance['show_content'] : false;
        $instance['content_limit'] = absint($new_instance['content_limit']);
        return $instance;
    }

    /**
    * Outputs the settings form for the Display Events widget.
    *
    * @param array $instance Previously saved values from database.
    */
    public function form($instance)
    {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : '';
        $limit = isset( $instance['limit'] ) ? $instance['limit'] : 5;
        $days_ahead = isset( $instance['days_ahead'] ) ? absint( $instance['days_ahead'] ) : 5;
        $show_content = isset( $instance['show_content'] ) ? (bool) $instance['show_content'] : false;
        $show_location = isset( $instance['show_location'] ) ? (bool) $instance['show_location'] : false;
        $content_limit = isset( $instance['content_limit'] ) ? $instance['content_limit'] : 300;
        ?>
        <p><label for="<?php echo esc_attr($this->get_field_id('title'));?>"><?php esc_attr_e('Title:', 'event-integration'); ?></label>
        <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title);?>"></p>

        <p><label for="<?php echo esc_attr($this->get_field_id('limit'));?>"><?php esc_attr_e('Number of events to show:', 'event-integration'); ?></label>
        <input class="tiny-text" id="<?php echo esc_attr($this->get_field_id('limit')); ?>" name="<?php echo esc_attr($this->get_field_name('limit')); ?>" type="number" value="<?php echo esc_attr($limit);?>"></p>

        <p><label for="<?php echo esc_attr($this->get_field_id('days_ahead'));?>"><?php esc_attr_e('Number of days ahead:', 'event-integration'); ?></label>
        <input id="<?php echo esc_attr($this->get_field_id('days_ahead')); ?>" name="<?php echo esc_attr($this->get_field_name('days_ahead')); ?>" type="number" min="0" size="5" value="<?php echo esc_attr($days_ahead);?>"></p>

        <p><input class="checkbox" type="checkbox"<?php checked( $show_location ); ?> id="<?php echo $this->get_field_id( 'show_location' ); ?>" name="<?php echo $this->get_field_name( 'show_location' ); ?>" />
        <label for="<?php echo $this->get_field_id( 'show_location' ); ?>"><?php _e( 'Display location' ); ?></label></p>

        <p><input class="checkbox" type="checkbox"<?php checked( $show_content ); ?> id="<?php echo $this->get_field_id( 'show_content' ); ?>" name="<?php echo $this->get_field_name( 'show_content' ); ?>" />
        <label for="<?php echo $this->get_field_id( 'show_content' ); ?>"><?php _e( 'Display description' ); ?></label></p>

        <p><label for="<?php echo esc_attr($this->get_field_id('content_limit'));?>"><?php esc_attr_e('Description letter limit:', 'event-integration'); ?></label>
        <input id="<?php echo esc_attr($this->get_field_id('content_limit')); ?>" name="<?php echo esc_attr($this->get_field_name('content_limit')); ?>" type="number" min="0" size="5" value="<?php echo esc_attr($content_limit);?>"></p>

        <?php
    }


    /**
     * Get events from database within given date range
     * @param  string $start_date start date range
     * @param  string $end_date   end date range
     * @param  int    $limit      maximum events to get
     * @return array              result with events
     */
    public function getEventsQuery($start_date, $end_date, $limit)
    {
        global $wpdb;
        $db_table = $wpdb->prefix . "integrate_occasions";
        $query = "
        SELECT      *, $wpdb->posts.ID AS ID
        FROM        $wpdb->posts
        LEFT JOIN   $db_table ON ($wpdb->posts.ID = $db_table.event_id)
        WHERE       $wpdb->posts.post_type = %s
                    AND $wpdb->posts.post_status = %s
                    AND ($db_table.start_date BETWEEN %s AND %s OR $db_table.end_date BETWEEN %s AND %s)
                    GROUP BY $db_table.start_date, $db_table.end_date
                    ORDER BY $db_table.start_date ASC
                    LIMIT %d
        ";
        // LEFT JOIN   $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id)
        $postType = 'event';
        $postStatus = 'publish';
        $completeQuery = $wpdb->prepare($query, $postType, $postStatus, $start_date, $end_date, $start_date, $end_date, $limit);
        $events = $wpdb->get_results($completeQuery);

        // TA BORT
        // echo "<pre>";
        // print_r($events);

        return $events;
    }

    /**
     * TA BORT
     * Get events as JSON and decode results
     */
    public function getEvents($date)
    {
        $ch = curl_init();
        $options = [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL            => 'http://api.helsingborg.se/json/wp/v2/event/time?'.$date,
        ];

        curl_setopt_array($ch, $options);
        $events = json_decode(curl_exec($ch));
        curl_close($ch);

        if(!$events || (is_object($events) && $events->code == 'Error')) {
           return false;
        }

        return $events;
    }

    /**
     * Limits event description
     * @param  string $string content string to limit
     * @param  int    $limit  maximum number of letters
     * @return string
     */
    public function descriptionLimit($string, $limit)
    {
        if(strlen($string) <= $limit || $limit == 0) {
            return $string;
        } else {
            $y = mb_substr($string, 0, $limit, "utf-8") . '...';
            return $y;
        }
    }

}
