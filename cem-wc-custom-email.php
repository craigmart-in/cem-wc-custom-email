<?php

/**
 * Plugin Name: CEM WC Custom Email
 * Plugin URI: https://github.com/craigmart-in/
 * Description: Customizes emails from woocommerce.
 * Version: 1.1.1
 * Author: Craig Martin
 * Author URI: https://craigmart.in
 * Text Domain: cem-wc-custom-email
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Check if WooCommerce is active
if ( ! cem_wc_download_instructions::is_woocommerce_active() )
	return;

/**
 * The cem_wc_download_instructions global object
 * @name $cem_wc_download_instructions
 * @global cem_wc_download_instructions $GLOBALS['cem_wc_download_instructions']
 */
$GLOBALS['cem_wc_download_instructions'] = new cem_wc_download_instructions();

class cem_wc_download_instructions {

    public function __construct() {
        add_action('woocommerce_email', array( $this, 'alter_woocommerce_email_action_priority'), 10, 1);        

        // add custom field to invoice email
        add_action( 'woocommerce_email_after_order_table', array( $this, 'coupon_code'), 10, 2 );
    }

    public function coupon_code( $order, $sent_to_admin ) {
        if ( $sent_to_admin == false ) {
            return;
        }

        if( $order->get_used_coupons() ) {
            $coupons_count = count( $order->get_used_coupons() );
            $coupons_list = implode(', ', $order->get_used_coupons());

            echo '<h3>Coupon Details</h3>';
            echo '<p><strong>Coupons used (' . $coupons_count . ') :</strong> ' . $coupons_list . '</p>';
        } // endif get_used_coupons
    }

    public function alter_woocommerce_email_action_priority($wc_email) {
        remove_action( 'woocommerce_email_order_details', array( $wc_email, 'order_downloads' ), 10 );
        add_action( 'woocommerce_email_order_details', array( $wc_email, 'order_downloads' ), 8, 4 );
        add_action( 'woocommerce_email_order_details', array( $this, 'download_instrctions'), 9, 2 );
    }

    public function download_instrctions( $order, $sent_to_admin ) {
        // Copied from class-wc-emails.php
        $show_downloads = $order->has_downloadable_item() && $order->is_download_permitted() && ! $sent_to_admin && ! is_a( $email, 'WC_Email_Customer_Refunded_Order' );
		if ( ! $show_downloads ) {
			return;
		}
        
        ?>
        <h3><a href="https://www.talkitrockit.com/faq/#download-instructions" target="_blank">Download Instructions</a></h3>
        <p>
            Please make sure to be on a PC or Mac before downloading.<br/>
            For best results, please do not use WiFi, a tablet, or a phone.
        </p>
        <br/>
        <?php
    }
    
    /** Helper Methods ******************************************************/


    /**
     * Checks if WooCommerce is active
     *
     * @since  1.0
     * @return bool true if WooCommerce is active, false otherwise
     */
    public static function is_woocommerce_active() {

        $active_plugins = (array) get_option( 'active_plugins', array() );

        if ( is_multisite() )
            $active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );

        return in_array( 'woocommerce/woocommerce.php', $active_plugins ) || array_key_exists( 'woocommerce/woocommerce.php', $active_plugins );
    }
}
?>
