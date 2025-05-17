<?php
/**
 * Invoice Conditions Helper
 */

namespace ESR\WooInvoiceRules;
use \SoapClient;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Woocommerce_Vies_Data_Helper {
        
    /**
     * Validate VAT ID using VIES
     * @param string $vat_number The VAT ID to validate
     * @return array An array containing the validation result and message
     */
    public static function getVatIdInfo($vat_number) {
        $vat_number = strtoupper(trim($vat_number));

        if (!preg_match('/^([A-Z]{2})([0-9A-Z]+)$/', $vat_number, $matches)) {
            return [
                'validFormat' => false,
                'validVies' => null,
                'countryCode' => null,
                'message' => 'Invalid VAT ID format.'
            ];
        }

        $countryCode = $matches[1];
        $vatNumber = $matches[2];

        try {
            $client = new SoapClient("https://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl");
            $result = $client->checkVat([
                'countryCode' => $countryCode,
                'vatNumber' => $vatNumber
            ]);

            return [
                'validFormat' => true,
                'validVies' => $result->valid,
                'countryCode' => $countryCode,
                'message' => $result->valid ? 'Valid VAT ID.' : 'Invalid according to VIES.'
            ];
        } catch (Exception $e) {
            return [
                'validFormat' => true,
                'validVies' => null,
                'countryCode' => $countryCode,
                'message' => 'VIES service error: ' . $e->getMessage()
            ];
        }
    }

}