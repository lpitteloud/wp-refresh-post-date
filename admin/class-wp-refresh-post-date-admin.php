<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WP_Refresh_Post_Date
 * @subpackage WP_Refresh_Post_Date/admin
 * @author     Laurent Pitteloud
 */
class WP_Refresh_Post_Date_Admin {
    const RECURRENCE_NEVER = 'never';
    const RECURRENCE_EVERY_5_MINUTES = '5_minutes';
    const RECURRENCE_HOURLY = 'hourly';
    const RECURRENCE_TWICE_A_DAY = 'twicedaily';
    const RECURRENCE_DAILY = 'daily';
    const RECURRENCE_WEEKLY = 'weekly';
    const RECURRENCE_MONTHLY = 'monthly';

    const DATE_CREATE = 'date_create';
    const DATE_UPDATE = 'date_update';

    /**
     * The authorized delay values
     *
     * @since   1.0.0
     * @access  private
     * @var     array
     */
    private $recurrences;

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

        $this->recurrences = array(
            self::RECURRENCE_NEVER => __('Jamais'),
            self::RECURRENCE_EVERY_5_MINUTES => __('Toutes les 5 minutes'),
            self::RECURRENCE_HOURLY => __('Une fois par heure'),
            self::RECURRENCE_TWICE_A_DAY => __('Deux fois par jour'),
            self::RECURRENCE_DAILY => __('Une fois par jour'),
            self::RECURRENCE_WEEKLY => __('Une fois par semaine'),
            self::RECURRENCE_MONTHLY => __('Une fois par mois'),
        );

    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/plugin-name-admin.css', array(), $this->version, 'all' );

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/plugin-name-admin.js', array( 'jquery' ), $this->version, false );

    }

    /**
     * Register custom admin meta box
     *
     * @since    1.0.0
     */
    public function add_meta_boxes() {

        add_meta_box(
            'wrpd-meta-box', __('Rafraîchir la date automatiquement tous les X jours', 'wp-refresh-post-date'),
            array($this, 'meta_box_markup'),
            get_post_types(array('public' => true,)),
            'side',
            'high',
            null
        );

    }

    public function add_admin_menu() {

        add_options_page( __('WP Refresh Post Date', 'wp-refresh-post-date' ), __('WP Refresh Post Date', 'wp-refresh-post-date' ), 'manage_options', 'wp-refresh-post-date', array($this, 'display_options_page') );

    }

    public function init_admin() {

        register_setting( 'wp-refresh-post-date-group', 'wp-refresh-post-date-settings', array($this, 'validate_and_sanitize_settings') );

        add_settings_section( 'section-1', __( 'Post Types', 'wp-refresh-post-date' ), array($this, 'section_1_callback'), 'wp-refresh-post-date' );
        add_settings_section( 'section-2', __( 'Fréquence', 'wp-refresh-post-date' ), array($this, 'section_2_callback'), 'wp-refresh-post-date' );
        add_settings_section( 'section-3', __( 'Champs', 'wp-refresh-post-date' ), array($this, 'section_3_callback'), 'wp-refresh-post-date' );

        $post_types = get_post_types(array('public' => true,), 'object');
        foreach ($post_types as  $post_type) {
            add_settings_field( 'field-1-'.$post_type->name, $post_type->label, array($this, 'field_1_callback'), 'wp-refresh-post-date', 'section-1', array($post_type->label, $post_type->name) );
        }

        add_settings_field( 'field-2-1', __( 'Remonter les posts', 'wp-refresh-post-date' ), array($this, 'field_2_callback'), 'wp-refresh-post-date', 'section-2' );

        add_settings_field( 'field-3-1', __( 'Date de création', 'wp-refresh-post-date' ), array($this, 'field_3_callback'), 'wp-refresh-post-date', 'section-3', array(__('Date de création', 'wp-refresh-post-date'), self::DATE_CREATE, true) );
        add_settings_field( 'field-3-2', __( 'Date de modification', 'wp-refresh-post-date' ), array($this, 'field_3_callback'), 'wp-refresh-post-date', 'section-3', array(__('Date de modification', 'wp-refresh-post-date'), self::DATE_UPDATE) );

        add_filter( 'update_option_wp-refresh-post-date-settings', array(__CLASS__, 'run_after_change'), 10, 2 );
    }

    public function display_options_page() {
        ?>
        <div class="wrap">
            <h2><?php _e('Réglages WP Refresh Post Date', 'wp-refresh-post-date'); ?></h2>
            <form action="options.php" method="POST">
                <?php settings_fields('wp-refresh-post-date-group'); ?>
                <?php do_settings_sections('wp-refresh-post-date'); ?>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * THE SECTIONS
     */
    public function section_1_callback() {
        _e( 'Sélectionnez les post types concernés par la mise à jour automatique :', 'wp-refresh-post-date' );
    }

    public function section_2_callback() {
        _e( 'Définissez la fréquence de rafraîchissement :', 'wp-refresh-post-date' );
    }

    public function section_3_callback() {
        _e( 'Choisissez les champs à mettre à jour :', 'wp-refresh-post-date' );
    }

    /**
     * THE FIELDS
     */
    public function field_1_callback($args) {

        $settings = (array) get_option( 'wp-refresh-post-date-settings' );
        $field = "field_1_".$args[1];
        $value = array_key_exists($field, $settings) ? $settings[$field] : false;

        echo '<input type="checkbox" name="wp-refresh-post-date-settings['.$field.']" value="1"'.($value === true ? ' checked="checked"' : '').'/>';
    }

    public function field_2_callback() {

        $settings = (array) get_option( 'wp-refresh-post-date-settings' );
        $field = "field_2_1";
        $selected = array_key_exists($field, $settings) ? esc_attr( $settings[$field] ) : 'never';

        echo '<select name="wp-refresh-post-date-settings['.$field.']">';
        foreach ($this->recurrences as $value => $recurrence) {
            echo '<option value="'.$value.'"'.($selected == $value ? ' selected="selected"' : '').'>'.$recurrence.'</option>';
        }
        echo '</select>';
    }

    /**
     * @param $args
     */
    public function field_3_callback($args) {
        $disabled = isset($args[2]);
        $settings = (array) get_option( 'wp-refresh-post-date-settings' );
        $field = "field_3_".$args[1];
        $value = array_key_exists($field, $settings) ? $settings[$field] : false;

        echo '<input type="checkbox" name="wp-refresh-post-date-settings['.$field.']" value="1"'.($value === true || $disabled === true ? ' checked="checked"' : '').($disabled === true ? ' disabled' : '').'/>';
    }

    /**
     * @param $input
     * @return array
     */
    public function validate_and_sanitize_settings( $input )
    {
        $output = array();

        $post_types = get_post_types(array('public' => true,), 'object');

        foreach ($post_types as $post_type) {
            if (array_key_exists('field_1_'.$post_type->name, $input)) {
                $output['field_1_'.$post_type->name] = true;
            }
        }

        if (array_key_exists('field_2_1', $input)) {
            if (array_key_exists($input['field_2_1'], $this->recurrences)) {
                $output['field_2_1'] = $input['field_2_1'];
            } else {
                add_settings_error(
                    'wp-refresh-post-date-settings',
                    'invalid-field_2_1',
                    __('Vous avez entré un champ invalide.', 'wp-refresh-post-date')
                );
            }
        }

        $dates = array(self::DATE_CREATE, self::DATE_UPDATE);
        foreach ($dates as $date) {
            if (array_key_exists('field_3_'.$date, $input)) {
                $output['field_3_'.$date] = true;
            }
        }

        return $output;
    }

    /**
     * @param $old_values
     * @param $new_values
     */
    public static function run_after_change($old_values, $new_values) {
        if ($old_values['field_2_1'] != $new_values['field_2_1']) {
            if (false !== ($timestamp = wp_next_scheduled( WP_Refresh_Post_Date_Cron::HOOK_NAME ))) {
                wp_unschedule_event( $timestamp, WP_Refresh_Post_Date_Cron::HOOK_NAME );
            }

            wp_schedule_event(current_time('timestamp'), $new_values['field_2_1'], WP_Refresh_Post_Date_Cron::HOOK_NAME);
        }
    }

    /**
     * Display admin meta box
     *
     * @param $object
     * @since    1.0.0
     */
    public function meta_box_markup($object) {
        $settings = (array) get_option( 'wp-refresh-post-date-settings' );

        $settings_field = 'field_1_'.$object->post_type;
        $display_field = array_key_exists($settings_field, $settings) && $settings[$settings_field] ? true : false;

        if ($display_field) :
            $timestamp = wp_next_scheduled( WP_Refresh_Post_Date_Cron::HOOK_NAME );

            wp_nonce_field(basename(__FILE__), "wrpd-meta-box-nonce");
            $checkbox_value = get_post_meta($object->ID, "wrpd-meta-box-checkbox", true);
            ?>
            <div>
                <label for="wrpd-meta-box-checkbox"><?php echo __('Activé:', 'wp-refresh-post-date'); ?></label>
                <input name="wrpd-meta-box-checkbox" type="checkbox" value="true"<?php echo ($checkbox_value ? ' checked' : ''); ?> />
            </div>
            <?php if ($checkbox_value) :
                $now = time();
                $timediff = $now > $timestamp ? __('Prochainement', 'wp-refresh-post-date') : human_time_diff( $now, $timestamp );
                ?>
                <p>
                    <?php printf( _x( 'La date de création et de mise à jour sera rafraîchie automatiquement dans %s', '%s = human-readable time difference', 'wp-refresh-post-date' ), $timediff ); ?>
                </p>
            <?php
            endif;
        else :
            ?><p><?php
                echo sprintf(__('Ce type de post n\'est pas configuré pour être rafraîchi.'), 'wp-refresh-post-date');
            ?></p><?php
        endif;

        ?><p>
            <a href="<?php echo admin_url('options-general.php?page=wp-refresh-post-date'); ?>">
                <?php echo __('Réglages', 'wp-refresh-post-date'); ?>
            </a>
        </p><?php
    }

    /**
     * Save custom admin meta box
     *
     * @param $post_id
     * @param $post
     * @param $update
     * @return mixed
     * @since    1.0.0
     */
    public function save_meta_box($post_id, $post, $update) {
        if (!isset($_POST['wrpd-meta-box-nonce']) || !wp_verify_nonce($_POST['wrpd-meta-box-nonce'], basename(__FILE__)))
            return $post_id;

        if (!current_user_can('edit_post', $post_id))
            return $post_id;

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return $post_id;

        $meta_box_checkbox_value = isset($_POST['wrpd-meta-box-checkbox']) ? 1 : 0;
        update_post_meta($post_id, 'wrpd-meta-box-checkbox', $meta_box_checkbox_value);

        return $post_id;
    }

}