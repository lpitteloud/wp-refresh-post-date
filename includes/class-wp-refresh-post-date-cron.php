<?php
/**
 * The cron plugin class.
 *
 * @since      1.0.0
 * @package    WP_Refresh_Post_Date
 * @subpackage WP_Refresh_Post_Date/includes
 * @author     Laurent Pitteloud
 */
class WP_Refresh_Post_Date_Cron {

    const HOOK_NAME = 'wrpd_cron';

    const RECURRENCE_INTERVAL_5_MINUTES = 5;
    const RECURRENCE_INTERVAL_HOURLY = 60;
    const RECURRENCE_INTERVAL_TWICE_A_DAY = 720;
    const RECURRENCE_INTERVAL_DAILY = 1440;
    const RECURRENCE_INTERVAL_WEEKLY = 10080;
    const RECURRENCE_INTERVAL_MONTHLY = 302400;

    /**
     * @var
     */
    private $settings;

    /**
     * @var
     */
    private $postTypes;

    /**
     * @var
     */
    private $posts;

    public function __construct() {
        $this->settings = (array) get_option('wp-refresh-post-date-settings');

        $post_types = get_post_types(array('public' => true,), 'object');
        foreach ($post_types as  $post_type) {
            $field = "field_1_".$post_type->name;

            if (isset($this->settings[$field])) {
                $this->postTypes[] = $post_type->name;
            }
        }
    }

    public static function add_custom_cron_intervals($schedules) {
        $schedules['5_minutes'] = array(
            'interval'	=> 300,
            'display'	=> __('Toutes les 5 minutes', 'wp-refresh-post-date'),
        );
        $schedules['weekly'] = array(
            'interval'	=> 604800,
            'display'	=> __('Une fois par semaine', 'wp-refresh-post-date'),
        );
        $schedules['monthly'] = array(
            'interval'	=> 2635200,
            'display'	=> __('Une fois par mois', 'wp-refresh-post-date'),
        );

        return (array) $schedules;
    }

    public function run() {
        $interval = self::getIntervalFromSettings();

        if ($interval !== false) {
            $this->getPostsToUpdate($interval);

            if (is_array($this->posts) && count($this->posts) > 0) {
                $this->updatePosts($interval);
            }
        }
    }

    private function getPostsToUpdate($interval) {
        global $wpdb;

        $postTypes = array_map(function($type) {
            return '\''.$type.'\'';
        }, $this->postTypes);

        $sql = "(SELECT ID, post_date
            FROM $wpdb->posts p
            INNER JOIN  $wpdb->postmeta pm 
	            ON pm.post_id = p.id
	            AND pm.meta_key = 'wrpd-meta-box-checkbox' 
            WHERE p.post_type in (".implode(',', $postTypes).")
                  AND p.post_status = 'publish'
                  AND p.post_date < '" . current_time( 'mysql' ) . "' - INTERVAL " . $interval . " MINUTE
                  AND pm.meta_value = 1
                  ";
        $sql.= ")";

        $posts = $wpdb->get_results($sql);

        if (is_array($posts)) {
            $this->posts = array_map(function($post) {
                return $post->ID;
            }, $posts);
        }
    }

    private function updatePosts() {
        global $wpdb;

        $today = new \DateTime('UTC');
        $todayGMT = new \DateTime('GMT');

        $sql = "UPDATE $wpdb->posts SET ";
        $sql.= "post_date = CONCAT('".$today->format('Y-m-d')." ', TIME(post_date))";
        $sql.= ",post_date_gmt = CONCAT('".$todayGMT->format('Y-m-d')." ', TIME(post_date_gmt))";

        $field = "field_3_".WP_Refresh_Post_Date_Admin::DATE_UPDATE;
        $update = array_key_exists($field, $this->settings) ? $this->settings[$field] : false;

        if ($update) {
            $sql.= ",post_modified = CONCAT('".$today->format('Y-m-d')." ', TIME(post_modified))";
            $sql.= ",post_modified_gmt = CONCAT('".$todayGMT->format('Y-m-d')." ', TIME(post_modified_gmt))";
        }

        $sql.= " WHERE ID in (".implode($this->posts).");";

        $wpdb->query($sql);

        if ( function_exists( 'wp_cache_flush' ) ) {
            wp_cache_flush();
        }
    }

    /**
     * @return bool|int
     */
    private function getIntervalFromSettings()
    {
        switch ($this->settings['field_2_1']) {
            case WP_Refresh_Post_Date_Admin::RECURRENCE_EVERY_5_MINUTES:
                $minutes = self::RECURRENCE_INTERVAL_5_MINUTES;
                break;
            case WP_Refresh_Post_Date_Admin::RECURRENCE_HOURLY:
                $minutes = self::RECURRENCE_INTERVAL_HOURLY;
                break;
            case WP_Refresh_Post_Date_Admin::RECURRENCE_TWICE_A_DAY:
                $minutes = self::RECURRENCE_INTERVAL_TWICE_A_DAY;
                break;
            case WP_Refresh_Post_Date_Admin::RECURRENCE_DAILY:
                $minutes = self::RECURRENCE_INTERVAL_DAILY;
                break;
            case WP_Refresh_Post_Date_Admin::RECURRENCE_WEEKLY:
                $minutes = self::RECURRENCE_INTERVAL_WEEKLY;
                break;
            case WP_Refresh_Post_Date_Admin::RECURRENCE_MONTHLY:
                $minutes = self::RECURRENCE_INTERVAL_MONTHLY;
                break;
            case WP_Refresh_Post_Date_Admin::RECURRENCE_NEVER:
            default:
                return false;
                break;
        }

        return $minutes;
    }

    public static function logStart() {
        self::write_log('Start WPRD CRON');
    }

    public static function logEnd() {
        self::write_log('End WPRD CRON');
    }

    /**
     * @param $log
     */
    public static function write_log($log) {
        if ( true === WP_DEBUG ) {
            if ( is_array( $log ) || is_object( $log ) ) {
                error_log( print_r( $log, true ) );
            } else {
                error_log( $log );
            }
        }
    }
}