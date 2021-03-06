<?php

namespace EventManagerIntegration\Helper;

class ApiUrl
{
    /**
     * Return API url with filter parameters
     */
    public static function buildApiUrl()
    {
        global $wpdb;
        $dbTable = $wpdb->prefix . "integrate_occasions";
        $occasion = $wpdb->get_results(
            "SELECT $dbTable.start_date
            FROM        $dbTable
            LEFT JOIN   $wpdb->posts ON ($wpdb->posts.ID = $dbTable.event_id)
            WHERE       $wpdb->posts.post_type = 'event'
            AND         $wpdb->posts.post_status = 'publish'
            ORDER BY    $dbTable.start_date
            ASC LIMIT 1", ARRAY_A);

        $fromDate = (is_array($occasion) && isset($occasion[0]['start_date']) && strtotime($occasion[0]['start_date']) < strtotime('now')) ? date('Y-m-d', strtotime($occasion[0]['start_date'])) : date('Y-m-d');
        $daysAhead = !empty(get_field('days_ahead', 'options')) ? absint(get_field('days_ahead', 'options')) : 30;
        $toDate = date('Y-m-d', strtotime("midnight now + {$daysAhead} days"));
        $importLocation = get_field('event_import_from_location', 'option');
        // Get nearby events from location
        $point = get_field('event_import_geographic', 'option');
        $latLng = ($point && $importLocation == 'point') ? '&latlng=' . $point['lat'] . ',' . $point['lng'] : '';
        $distance = (get_field('event_geographic_distance', 'option') && $importLocation == 'point') ? '&distance=' . get_field('event_geographic_distance', 'option') : '';
        // Get events within area
        $areaLatLng = '';
        $areaPoints = get_option('event_import_area', 'option');
        if ($importLocation == 'area' && is_array($areaPoints) && !empty($areaPoints)) {
            foreach ($areaPoints as $areaPoint) {
                if (isset($areaPoint['lat']) && isset($areaPoint['lng'])) {
                    $areaLatLng .= '&arealatlng[]=' . $areaPoint['lat'] . ',' . $areaPoint['lng'];
                }
            }
        }
        // Filter by selected groups
        $groups = '';
        $selectedGroups = json_decode(json_encode(get_field('event_filter_group', 'option')), true);
        if (is_array($selectedGroups) && !empty($selectedGroups)) {
            $selectedGroups = array_column($selectedGroups, 'slug');
            $allGroups = array_column(get_option('event_user_groups', array()), 'id', 'slug');
            // Create array with selected group IDs
            $groupIds = array_map(function ($a) use ($allGroups) {
                return isset($allGroups[$a]) ? $allGroups[$a] : null;
            }, $selectedGroups);
            $groups = '&group-id=' . implode(',', array_filter($groupIds));
        }

        // Adds internal event parameter
        $internal = (get_field('event_internal_events', 'option')) ? '&internal=1' : '';

        // Build API-url
        if ($apiUrl = get_field('event_api_url', 'option')) {
            return rtrim($apiUrl, '/') . '/event/time?start=' . $fromDate . '&end=' . $toDate . $latLng . $distance . $areaLatLng . $groups . $internal;
        }

        return;
    }
}