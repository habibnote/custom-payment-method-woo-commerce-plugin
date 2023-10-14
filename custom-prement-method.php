<?php 
/*
 * Plugin Name:       Paypal Custom Prement Method
 * Plugin URI:        https://me.habibnote.com
 * Description:       This plugin used for a custom prement method in woocommerce
 * Version:           0.0.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Md. Habib
 * Author URI:        https://me.habibnote.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 * Text Domain:       CPM
*/

if( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * The main plugin class
 */
final class Custom_Prement_Method{

    /**
     * Plugin Version
     */
    const version = '1.0';
    
    /**
     * class contructor
     */
    private function __construct() {
        $this->define_constants();
        
        register_activation_hook( CMP_FILE, [$this, 'activate'] );
        add_action( 'plugins_loaded', [$this, 'init_plugin'] );
        add_action( 'admin_notices', [$this, 'cmp_admin_notice_activeated'] );
        add_action( 'admin_menu', [$this, 'add_html_editor_menu'] );
    }

    // Function to display the admin page
    function paypal_custom_payment_setthing_call_back() {
        if ( ! current_user_can('manage_options' ) ) {
            wp_die( 'You do not have permission to access this page.' );
        }

        // Process form submission
        if ( isset( $_POST['html_code'] ) ) {
            // Execute the HTML code (for demonstration purposes)
            $html_code = wp_kses_post($_POST['html_code']);
            update_option( 'cmp_html_code', $html_code );
        }
        ?>
        <div class="wrap">
            <h2>HTML Editor Page For PayPal Custom Payment method.</h2>
            <form method="post">
                <?php 
                    if( get_option( 'cmp_html_code' ) ) {
                        $html_code_ = get_option( 'cmp_html_code' );
                    }
                ?>
                <textarea name="html_code" rows="30" cols="100"> <?php echo $html_code_;?> </textarea>
                <p>Enter your HTML code above.</p>
                <input type="submit" class="button button-primary" value="Execute">
            </form>
        </div>
        <?php
    }

    // Function to add a menu item in the admin dashboard
    function add_html_editor_menu() {
        add_menu_page(
            __( 'PayPal', 'CMP'), 
            __( 'PayPal', 'CMP'), 
            'manage_options', 
            'paypal-settthing', 
            [$this, 'paypal_custom_payment_setthing_call_back'],
            'dashicons-money-alt',
            30
        );
    }


    /**
     * For admin notice
     */
    public function cmp_admin_notice_activeated() {
        ?>
            <div class="notice notice-success is-dismissable">
                <p><?php _e( 'PayPal Custom Payment method plugin is Activeted.', 'CMP' )?></p>
            </div>
        <?php
    }

    /**
     * Intialize the plugin
     */
    public function init_plugin() {
        include( dirname( CMP_FILE ) . '/classes/CMP.php');
        add_filter( 'woocommerce_payment_gateways', [$this, 'add_custom_payment_method'] );
    }

    /**
     * Do stuff upon plugin activation
     * 
     * @return void
     */
    public function activate() {
        $installed = get_option( 'cmp_installed' );

        if( ! $installed ) {
            update_option( 'cmp_installed', time() );
        }
        update_option( 'cmp_version', CMP_VERSION );
    }

    /**
     * Define the require plugin constant
     * 
     * @return void
     */
    public function define_constants() {
        define( 'CMP_VERSION', self::version );
        define( 'CMP_FILE', __FILE__ );
        define( 'CMP_PATH', __DIR__ );
        define( 'CMP_URL', plugins_url( '', CMP_FILE ) );
        define( 'CMP_ASSETS', CMP_URL . '/assets' );
    }

    function add_custom_payment_method($methods) {
        $methods[] = 'CMP';
        return $methods;
    }

    /**
     * Intializes a singleton instance
     * 
     * @return \Custom_Prement_Method
     */
    public static function init() {
        static $instance = false;

        if( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }
}

/**
 * Initializes the main plugin
 * 
 * @return \Custom_Prement_Method
 */
function custom_prement_method() {
    return Custom_Prement_Method::init();
}

// Check if WooCommerce is active
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    custom_prement_method();
} else {
    add_action('admin_notices', 'cmp_admin_notice');
    
    function cmp_admin_notice() {
        ?>
            <div class="notice notice-error is-dismissible">
                <p><?php _e( 'To get PayPal Custom payment method plugin you need to install and activated woocommerce plugin', 'CMP' );?></p>
            </div>
        <?php
    }
}

