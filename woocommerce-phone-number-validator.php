<?php
/**
 * Plugin Name: WooCommerce Phone Number Validator
 * Description: Add phone number check
 * Author: Jonas
 * Author URI: https://positronic.fr
 * Version: 0.1.0
 * Text Domain: woocommerce-phone-number-validator
 * Domain Path: /languages
 *
 * https://woocommerce.com/document/tutorial-customising-checkout-fields-using-actions-and-filters/
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( !function_exists('is_woocommerce_active') ){
    function is_woocommerce_active(){
        return in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')));
    }
}

// Check if WooCommerce is active and deactivate extension if it's not.
if ( ! is_woocommerce_active() ) {
	return;
}

/**
 * The WC_Shipping_Date global object
 *
 * @name $woocommerce-phone-number-validator
 * @global WC_Phone_Number_Validator $GLOBALS['woocommerce-phone-number-validator']
 */
$GLOBALS['woocommerce-phone-number-validator'] = new WC_Phone_Number_Validator();

/**
 * Main Plugin Class
 *
 * @since 0.1
 */
class WC_Phone_Number_Validator {

	/**
	 * Setup main plugin class
	 *
	 * @since  0.1.0
	 * @return \WC_Phone_Number_Validator
	 */
	public function __construct() {

		// Load core classes
		$this->load_classes();

		// Add actions and filters that require WC to be loaded
		add_action( 'woocommerce_init', array( $this, 'init' ) );
	}

	/**
	 * Load core classes
	 *
	 * @since 0.1.0
	 */
	public function load_classes()
    {
        require( 'vendor/autoload.php' );
	}

	/**
	 * Add actions and filters that require WC to be loaded
	 *
	 * @since 0.1.0
	 */
	public function init() {

        //load_plugin_textdomain( 'woocommerce-shipping-date', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

        // Custom validation for Billing Phone checkout field
        add_action('woocommerce_checkout_process', 'custom_validate_billing_phone');
        function custom_validate_billing_phone()
        {
            $shipping_country = $_POST['shipping_country'];
            $phone = $_POST['billing_phone'];

            if(substr_count($phone, '.') > 1) {
                wc_add_notice( __( '<strong>Le numéro de téléphone</strong> ne doit pas contenir de point.' ), 'error' );
                return;
            }

            if(isset($shipping_country) && isset($phone))
            {
                $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
                try {
                    $proto = $phoneUtil->parse($phone, $shipping_country);
                    $is_valid = $phoneUtil-> isValidNumberForRegion($proto, $shipping_country);
                    //die(var_export($is_valid, true));
                    if (!$is_valid) {
                        wc_add_notice( __( '<strong>Téléphone</strong> doit contenir un numéro de téléphone valide correspondant au pays de livraison.' ), 'error' );
                    }
                } catch (\libphonenumber\NumberParseException $e) {
                    wc_add_notice( __( 'Une erreur a eu lieu lors de la vérification du numéro de téléphone : '.$e->getMessage()), 'error' );
                }
            }
        }
	}
}
