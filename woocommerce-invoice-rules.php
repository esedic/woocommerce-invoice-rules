<?php
/**
 * Plugin Name: WooCommerce Invoices Conditions
 * Plugin URI:  https://spletodrom.si
 * Description: Custom integration with WooCommerce PDF Invoices & Packing Slips to handle B2B invoice conditions
 * Version: 1.0.0
 * Author:      Elvis Sedić
 * Author URI:  https://spletodrom.si
 * License:     GPL2
 * Text Domain: woocommerce-invoice-rules
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WIR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WIR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WIR_VERSION', '1.0.0');
define('WIR_TEMPLATE_BASE', get_stylesheet_directory() . '/woocommerce/');

// Include required files
if ( ! class_exists( 'Woocommerce_Vies_Data_Helper' ) ) {
    require_once __DIR__ . '/includes/class-helper.php';
}

if ( ! class_exists( 'Woocommerce_Invoice_Rules_Conditions' ) ) {
    require_once __DIR__ . '/includes/class-invoice-conditions.php';
}

/**
 * Main plugin class
 */
class Woocommerce_Invoice_Rules {
    /**
     * Instance of this class
     */
    protected static $instance = null;

    /**
     * Custom Order Statuses instance
     */
    public $order_statuses;

    /**
     * Invoice Conditions instance
     */
    public $invoice_conditions;

    /**
     * Constructor
     */
    public function __construct() {
        $this->init();
        
        // Load plugin text domain
        add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));
        
        // Check if WooCommerce is active
        add_action('admin_notices', array($this, 'check_woocommerce_active'));

    }

    /**
     * Initialize the plugin
     */
    private function init() {
        // Initialize invoice conditions
        $this->invoice_conditions = new Woocommerce_Invoice_Rules_Conditions();
    }

    /**
     * Return an instance of this class
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Load the plugin text domain for translation
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain('woocommerce-invoice-rules', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }
    

    /**
     * Check if WooCommerce is active
     */
    public function check_woocommerce_active() {
        if (!class_exists('WooCommerce')) {
            ?>
            <div class="error notice">
                <p><?php _e('WooCommerce Invoice Conditions requires WooCommerce to be installed and active.', 'woocommerce-invoice-rules'); ?></p>
            </div>
            <?php
        }
        
        if (!class_exists('WPO_WCPDF')) {
            ?>
            <div class="error notice">
                <p><?php _e('WooCommerce Invoice Conditions requires WooCommerce PDF Invoices & Packing Slips to be installed and active.', 'woocommerce-invoice-rules'); ?></p>
            </div>
            <?php
        }
    }
}

// Initialize the plugin
function woocommerce_invoice_rules() {
    return Woocommerce_Invoice_Rules::get_instance();
}

// Start the plugin
woocommerce_invoice_rules();