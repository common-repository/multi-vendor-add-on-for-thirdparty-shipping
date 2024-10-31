<?php

/* 
 * Send the label to respective vendors
 */

if( ! class_exists('Xa_Send_Label_To_Vendors') ) {
	class Xa_Send_Label_To_Vendors {

		/**
		 * Constructor of class Xa_Send_Label_To_Vendors which is responsible for sending label to the vendors.
		 */
		public function __construct() {
			add_filter( 'xa_fedex_add_email_addresses_to_send_label', array( $this, 'fedex_add_vendor_email_to_send_fedex_label'), 10, 4 );
		}

		/**
		 * Add Vendor email address to send the FedEx Label to the vendor.
		 * @param $email_ids array Array of email ids.
		 * @param $shipment_id str Shipment Id.
		 * @param $order object WC_Order Object.
		 * @param $settings array Shipping Carrier Settings.
		 * @return array Array of email ids.
		 */
		public function fedex_add_vendor_email_to_send_fedex_label( $email_ids, $shipment_id, $order, $settings ) {

			$order_id = $order->get_id();

			if( in_array( 'vendor', $settings['auto_email_label']) ) {
				$request 		= get_post_meta( $order_id, "wf_woo_fedex_request_$shipment_id", true);
				if( ! in_array( $request['RequestedShipment']['Shipper']['Contact']['EmailAddress'], $email_ids) ) {
					$email_ids[] 	= $request['RequestedShipment']['Shipper']['Contact']['EmailAddress'];
				}
			}

			return $email_ids;
		}
	}

	new Xa_Send_Label_To_Vendors();
}