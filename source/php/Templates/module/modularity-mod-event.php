<?php

$fields = json_decode(json_encode(get_fields($module->ID)));
$events = \EventManagerIntegration\Module\EventModule::getEvents($module);

$descr_limit = $fields->mod_event_descr_limit;

$fields->mod_event_fields = is_array($fields->mod_event_fields) ? $fields->mod_event_fields : array();

$date = $fields->mod_event_date_format;

?>

<div class="<?php echo implode(' ', apply_filters('Modularity/Module/Classes', array('box', 'box-panel'), $module->post_type, $args)); ?>">
    <?php if (!$module->hideTitle && !empty($module->post_title)) { ?>
        <h4 class="box-title"><?php echo apply_filters('the_title', $module->post_title); ?></h4>
    <?php } ?>

        <ul>
        <?php if (! $events) : ?>
            <li><?php _e('No events found', 'event-integration'); ?></li>
        <?php else: ?>
        <?php foreach ($events as $event) : ?>
            <li>
            <?php if (in_array('image', $fields->mod_event_fields)) : ?>
                <?php echo get_the_post_thumbnail( $event->ID, 'large', array('class' => 'image-responsive')); ?>
            <?php endif; ?>

            <?php if (! empty($event->post_title)) : ?>
                <a href="<?php echo get_page_link($event->ID); ?>" class="link-item"><?php echo $event->post_title ?></a>
            <?php endif; ?>

			<?php if (in_array('start_date', $fields->mod_event_fields) && ! empty($event->start_date)) : ?>
			<?php $start_date = \EventManagerIntegration\Module\EventModule::formatDate($module, $event->start_date); ?>
                <p class="date text-sm text-dark-gray"><?php echo sprintf(__('Start %s', 'event-integration'), $start_date) ?></p>
            <?php endif; ?>

			<?php if (in_array('end_date', $fields->mod_event_fields) && ! empty($event->end_date)) : ?>
				<?php $end_date = \EventManagerIntegration\Module\EventModule::formatDate($module, $event->end_date); ?>
                <p class="date text-sm text-dark-gray"><?php echo sprintf(__('End %s', 'event-integration'), $end_date) ?></p>
            <?php endif; ?>

			<?php if (in_array('door_time', $fields->mod_event_fields) && ! empty($event->door_time)) : ?>
				<?php $door_time = \EventManagerIntegration\Module\EventModule::formatDate($module, $event->door_time); ?>
            	<p class="date text-sm text-dark-gray"><?php echo sprintf(__('Door time %s', 'event-integration'), $door_time); ?></p>
            <?php endif; ?>

           <?php if (in_array('location', $fields->mod_event_fields) && get_post_meta($event->ID, 'location', true)) : ?>
                <?php $location = get_post_meta($event->ID, 'location', true); ?>
                <p><?php echo sprintf(__('Location: %s', 'event-integration'), $location['title']) ?></p>
            <?php endif; ?>

            <?php if (in_array('description', $fields->mod_event_fields) && ! empty($event->post_content)) : ?>
                <p>
                <?php echo \EventManagerIntegration\Helper\QueryEvents::stringLimiter($event->post_content, $descr_limit); ?>
                </p>
            <?php endif; ?>
            </li>
        <?php endforeach; ?>
        <?php endif; ?>
        </ul>
</div>
