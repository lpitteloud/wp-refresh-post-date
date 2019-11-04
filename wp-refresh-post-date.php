<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/lpitteloud/wp-refresh-post-date
 * @since             1.0.0
 * @package           WP-Refresh-Post-Date
 *
 * @wordpress-plugin
 * Plugin Name:       WP Refresh Post Date
 * Description:       Provide UI to schedule post date updates
 * Version:           1.0.0
 * Author:            Laurent Pitteloud
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-refresh-post-date
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-refresh-post-date-activator.php
 */
function activate_wprd() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-refresh-post-date-activator.php';
    WP_Refresh_Post_Date_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-refresh-post-date-deactivator.php
 */
function deactivate_wprd() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-refresh-post-date-deactivator.php';
    WP_Refresh_Post_Date_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wprd' );
register_deactivation_hook( __FILE__, 'deactivate_wprd' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-refresh-post-date.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wrpd() {

    $plugin = new WP_Refresh_Post_Date();
    $plugin->run();

}
run_wrpd();

function run_wrpd_cron() {
    WP_Refresh_Post_Date_Cron::logStart();

    $cron = new WP_Refresh_Post_Date_Cron();
    $cron->run();

    WP_Refresh_Post_Date_Cron::logEnd();
}
add_action( WP_Refresh_Post_Date_Cron::HOOK_NAME, 'run_wrpd_cron' );
