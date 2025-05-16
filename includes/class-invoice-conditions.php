<?php
/**
 * Invoice Conditions Handler
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Woocommerce_Invoice_Rules_Conditions {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Add custom condition for PDF attachments
        add_filter('wpo_wcpdf_custom_attachment_condition', array($this, 'invoice_condition'), 10, 3);
 
        // Hook into checkout order processed action
        add_action('woocommerce_store_api_checkout_order_processed', array($this, 'custom_vat_after_checkout'), 10, 1);
    }
    
    /**
     * Custom attachment condition for PDF Invoices & Packing Slips
     * 
     * @param bool $condition The current attachment condition
     * @param WC_Order $order The order object
     * @param string $document_type The document type (invoice, packing-slip, etc.)
     * @return bool The modified attachment condition
     */
    public function invoice_condition($condition, $order, $document_type) {
        // Only apply custom logic for invoices
        if ($document_type !== 'invoice') {
            return $condition;
        }
        
        // Get customer ID from the order
        $customer_id = $order->get_customer_id();
        
        // If no customer ID (guest checkout), use default behavior
        if (!$customer_id) {
            return $condition;
        }
        
        // Check if the customer has a VAT number (B2B customer)
        $vat_number = get_user_meta($customer_id, 'vat_number', true);
        
        if (!empty($vat_number)) {
            // Set order status to "package_arrived" only if VAT number exists
            //return $order->get_status() === 'package_arrived';
        }
        
        // For non-B2B customers or users without VAT number, use default behavior
        return $condition;
    }

    /**
     * Validate VAT number after checkout
     * This function is triggered after the order is processed.
     * It checks the VAT number and performs actions based on its validity.
     */
    public function custom_vat_after_checkout( $order ) {
        // Ensure we have a WC_Order object (the hook passes WC_Order in WC 7.2+)
        if ( ! is_a( $order, 'WC_Order' ) ) {
            $order = wc_get_order( $order );
        }
        if ( ! $order ) {
            return;
        }

        // 1. Get the customer (user) ID from the order
        $user_id = $order->get_user_id();
        if ( ! $user_id ) {
            // Guest checkout or no user – nothing to do
            return;
        }

        // 2. Retrieve the VAT number stored in user meta
        $vat_number = get_user_meta( $user_id, 'vat_number', true );
        //error_log( 'VAT number: ' . $vat_number ); // Debugging line

        if ( empty( $vat_number ) ) {
            return; // no VAT number on file
        }
        $vat_number = sanitize_text_field( $vat_number ); 

        // 3. Validate the VAT number
        try {
            //$vat_info = getVatIdInfo( $vat_number );
            $vat_info = Woocommerce_Vies_Data_Helper::getVatIdInfo( $vat_number );

            //error_log( 'VAT info: ' . print_r( $vat_info, true ) ); // Debugging line
        } catch ( Exception $e ) {
            // Log or handle validation service error
            error_log( 'VAT validation error: ' . $e->getMessage() );
            return;
        }
        if ( is_wp_error( $vat_info ) ) {
            // Handle validation error
            return;
        }

        // 4. Act based on validation result
        // if ( ! empty( $vat_info['validVies'] ) && $vat_info['validFormat'] ) {
        if ( isset( $vat_info['validFormat'], $vat_info['validVies'] ) && $vat_info['validFormat'] === true && $vat_info['validVies'] === true ) {

            // if VAT ID isn't from Slovenia
            if($vat_info['countryCode'] !== 'SI') {
                error_log( 'VAT ID is not from Slovenia.' ); // Debugging line
            } else {
                error_log( 'VAT ID is from Slovenia.' ); // Debugging line
            }   

            // error_log( 'Message: ' . $vat_info['message'] ); // Debugging line
            // Example: VAT is valid – perform custom logic (e.g. adjust order, notify, etc.)
            // $order->add_order_note( 'Customer VAT is valid.' );
        } else {
            // Example: VAT invalid – handle this case
            // $order->add_order_note( 'Customer VAT is invalid.' );
        }

        // If you modified the order (e.g. changed tax), save it
        // $order->save();
    }

}