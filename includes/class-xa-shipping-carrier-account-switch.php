<?php

/**
 * Switch the Shipping service account Credentials
 */
if( !class_exists('Xa_Shipping_Carrier_Account_Switch') ) {
	class Xa_Shipping_Carrier_Account_Switch{
		
		public function __construct() {
			add_filter( 'wf_fedex_request', array( $this, 'xa_switch_fedex_account_backend' ), 9, 3 );		// Change the FedEx credentials while generating the label
			add_filter( 'xa_fedex_rate_request', array( $this, 'xa_switch_fedex_account_frontend'), 9, 2 );		// Change the FedEx credentials while fetching the rates
			add_filter( 'xa_multicarrier_carriers_accounts', array( $this, 'xa_get_multicarrier_vendor_accounts'), 9, 2 );		// Change the FedEx credentials while fetching the rates
		}
		
		public function xa_get_multicarrier_vendor_accounts($account_details, $package){
			$fist_product = current($package['contents']);
			$post_author_details	= ! empty($fist_product['data']->variation_id) ? $this->xa_get_post_author($fist_product['data']->variation_id) : $this->xa_get_post_author($fist_product['data']->get_id() );
			
			if( !empty($post_author_details->get('xa_fedex_account_number')) ){
				$account_details['fedex']['api_key'] 		= $post_author_details->get('xa_fedex_web_services_key');
				$account_details['fedex']['api_password']	= $post_author_details->get('xa_fedex_web_services_password');
				$account_details['fedex']['account_number'] = $post_author_details->get('xa_fedex_account_number');
				$account_details['fedex']['meter_number'] 	= $post_author_details->get('xa_fedex_meter_number');
			}

			if( !empty($post_author_details->get('xa_ups_account_number')) ){
				$account_details['ups']['key'] 	= $post_author_details->get('xa_ups_access_key');
				$account_details['ups']['password'] 	= $post_author_details->get('xa_ups_password');
				$account_details['ups']['account_number'] 	= $post_author_details->get('xa_ups_account_number');
				$account_details['ups']['username'] 	= $post_author_details->get('xa_ups_user_id');
			}
			
			if( !empty($post_author_details->get('usps_user_id')) ){
				$account_details['usps']['username'] 	= $post_author_details->get('usps_user_id');
				$account_details['usps']['password'] 	= $post_author_details->get('usps_password');
			}
			
			if( !empty($post_author_details->get('stamps_usps_username')) ){
				$account_details['stamps']['username'] 	= $post_author_details->get('stamps_usps_username');
				$account_details['stamps']['password'] 	= $post_author_details->get('stamps_usps_password');
			}

			if( !empty($post_author_details->get('dhl_account_number')) ){
				$account_details['dhl']['account_number'] 	= $post_author_details->get('dhl_account_number');
				$account_details['dhl']['siteid'] 	= $post_author_details->get('dhl_siteid');
				$account_details['dhl']['password'] 	= $post_author_details->get('dhl_password');
			}
			return $account_details;
		}

		/**
		 * Change the fedEx Credentials depending on the product author in backend (Order admin)
		 * @param array $request FedEx request
		 * @param object $order Wc_order
		 * @param array $fedex_package fedex package
		 * @return array FedEx request
		 */
		public function xa_switch_fedex_account_backend( $request, $order, $fedex_package ) {
			if( ! empty($fedex_package['packed_products']) ) {
				$wf_product = current($fedex_package['packed_products']);
				$post_author_details	= ! empty($wf_product->variation_id) ? $this->xa_get_post_author($wf_product->variation_id) : $this->xa_get_post_author($wf_product->id);
				if( ! empty($post_author_details->get('xa_fedex_account_number')) )
				{
					$request['WebAuthenticationDetail']['UserCredential']	=   array(
							'Key'		=>	$post_author_details->get('xa_fedex_web_services_key'),
							'Password'  =>	$post_author_details->get('xa_fedex_web_services_password'),
					);
					$request['ClientDetail']	= array(
						'AccountNumber'		=> $post_author_details->get('xa_fedex_account_number'),
						'MeterNumber'		=> $post_author_details->get('xa_fedex_meter_number'),
					);
				}

			}
			return $request;
		}
		
		
		/**
		 * Change the FedEx credentials depending on product author
		 * @param array $request FedEx request
		 * @param array $fedex_package Fedex Package
		 * @return array FedEx request
		 */
		public function xa_switch_fedex_account_frontend( $request, $fedex_packages ) {
			
			foreach( $fedex_packages as $fedex_package ) {
				if( ! empty($fedex_package['packed_products']) ) {
					$wf_product = current($fedex_package['packed_products']);
					$post_author_details	= ! empty($wf_product->variation_id) ? $this->xa_get_post_author($wf_product->variation_id) : $this->xa_get_post_author($wf_product->id);
					if( ! empty($post_author_details->get('xa_fedex_account_number')) )
					{
						$request['WebAuthenticationDetail']['UserCredential']	=   array(
								'Key'		=>	$post_author_details->get('xa_fedex_web_services_key'),
								'Password'  =>	$post_author_details->get('xa_fedex_web_services_password'),
						);
						$request['ClientDetail']	= array(
							'AccountNumber'		=> $post_author_details->get('xa_fedex_account_number'),
							'MeterNumber'		=> $post_author_details->get('xa_fedex_meter_number'),
						);
					}

				}
			}
			return $request;
		}
		/**
		 * Get the author details of the post
		 * @param int $id post id
		 * @return object WP_User
		 */
		public function xa_get_post_author($id){
			$post = get_post($id);
			$author = get_user_by('id', $post->post_author);
			return $author;
		}
	}
	new Xa_Shipping_Carrier_Account_Switch();
}

