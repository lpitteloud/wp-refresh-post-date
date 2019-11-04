<?php
/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    WP_Refresh_Post_Date
 * @subpackage WP_Refresh_Post_Date/includes
 * @author     Laurent Pitteloud
 */
class WP_Refresh_Post_Date_Deactivator {

    /**
     * @since    1.0.0
     */
    public static function deactivate() {
        wp_clear_scheduled_hook(WP_Refresh_Post_Date_Cron::HOOK_NAME);
    }

}