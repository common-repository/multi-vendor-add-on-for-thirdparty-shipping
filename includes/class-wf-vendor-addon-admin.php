<?php
class class_wf_vendor_addon_admin{
	public function __construct() {
		$this->wf_init();
	}

	public function wf_init(){
		
		if(!defined('VENDOR_PLUGIN')){
			define( 'VENDOR_PLUGIN', $this->get_vendor_plugin() );
		}

		add_filter('wf_dhl_filter_label_packages', array($this, 'wf_vendor_label_packages'), 10, 4);
		add_filter('wf_filter_label_packages', array($this, 'wf_vendor_label_packages'), 10, 4);
		add_filter('wf_filter_label_from_address', array($this, 'wf_vendor_label_from_address'), 10, 4);
		
		// To Support vendor in UPS plugin
		$ups_settings = get_option( 'woocommerce_wf_shipping_ups_settings', null );
		// If Ship From Address Preference is set to vendor address then only execute the ups related functions
		if( $ups_settings['ship_from_address'] == 'vendor_address' ) {

			// Update the UPS credentials in rate requests
			add_filter( 'wf_ups_rate_request_data', array( $this, 'xa_change_ups_credentials_in_rate_request' ), 10, 3 );
			// Upadate the Package origin
			add_filter( 'wf_ups_filter_label_from_packages', array( $this, 'wf_vendor_label_packages' ), 10, 4 );
			// Split UPS Shipment packages based on vendor 
			add_filter( 'wf_ups_shipment_data', array( $this, 'xa_ups_split_shipment'),10, 3 );
			// Update UPS confirm shipment
			add_filter( 'wf_ups_shipment_confirm_request', array( $this, 'xa_ups_update_request_info_confirm_shipment' ), 10, 3 );
			// Update UPS accept shipment request
			add_filter( 'xa_ups_accept_shipment_xml_request', array( $this, 'xa_ups_modify_accept_shipment_xml_request' ), 10, 3 );
			// Void Shipment Request
			add_filter( 'xa_ups_void_shipment_xml_request', array( $this, 'xa_ups_void_shipment_xml_request'), 10, 3 );
		}
		// End of UPS support for vendor
		
		$splitcart = get_option('wc_settings_wf_vendor_addon_splitcart');
		if( $splitcart == 'sum_cart' ){
			add_filter('wf_filter_package_address', array($this, 'wf_splited_packages'), 10, 4);
		}else{
			add_filter('woocommerce_cart_shipping_packages', array($this, 'wf_splited_packages'), 9999, 4);
		}

		add_action ( 'woocommerce_edit_account_form' , array($this, 'xa_register_myaccount_fields') );
		add_action ( 'woocommerce_save_account_details' , array($this, 'xa_save_myaccount_fields') );

	}


	function  xa_register_myaccount_fields() {
		$user = wp_get_current_user(); ?>
		<h3>Vendor Options</h3>
		<table>
			<tr>
				<th><label for="tin_number">TIN Number</label></th>
				<td>
					<input type="text" name="tin_number" id="tin_number" value="<?php echo esc_attr( get_the_author_meta( 'tin_number', $user->ID ) ); ?>" class="regular-text" /><br />
					<span class="description">Please enter your TIN number here.</span>
				</td>
			</tr>
			
			<tr><th style="font-size: 16px">FedEx Account Details:</th></tr>
			<!--Fedex Account Details -->
			<tr>
				<th><label for="xa_fedex_account_number">FedEx Account Number</label></th>
				<td>
					<input type="text" name="xa_fedex_account_number" id="xa_fedex_account_number" value="<?php echo esc_attr( get_the_author_meta( 'xa_fedex_account_number', $user->ID ) ); ?>" class="regular-text" /><br />
					<span class="description">Please enter your FedEx Account number.</span>
				</td>
			</tr>
			<tr>
				<th><label for="xa_fedex_meter_number">FedEx Meter Number</label></th>
				<td>
					<input type="text" name="xa_fedex_meter_number" id="xa_fedex_meter_number" value="<?php echo esc_attr( get_the_author_meta( 'xa_fedex_meter_number', $user->ID ) ); ?>" class="regular-text" /><br />
					<span class="description">Please enter your FedEx Meter number.</span>
				</td>
			</tr>
			<tr>
				<th><label for="xa_fedex_web_services_key">FedEx Web Services Key</label></th>
				<td>
					<input type="text" name="xa_fedex_web_services_key" id="xa_fedex_web_services_key" value="<?php echo esc_attr( get_the_author_meta( 'xa_fedex_web_services_key', $user->ID ) ); ?>" class="regular-text" /><br />
					<span class="description">Please enter your FedEx Web Services Key.</span>
				</td>
			</tr>
			<tr>
				<th><label for="xa_fedex_web_services_password">FedEx Web Services Password</label></th>
				<td>
					<input type="text" name="xa_fedex_web_services_password" id="xa_fedex_web_services_password" value="<?php echo esc_attr( get_the_author_meta( 'xa_fedex_web_services_password', $user->ID ) ); ?>" class="regular-text" /><br />
					<span class="description">Please enter your FedEx Web Services Password.</span>
				</td>
			</tr>
			
			<!--UPS Account Details -->
			<tr><th style="font-size: 16px">UPS Account Details:</th></tr>
			<tr>
				<th><label for="xa_ups_user_id">UPS User Id</label></th>

				<td>
					<input type="text" name="xa_ups_user_id" id="xa_ups_user_id" value="<?php echo esc_attr( get_the_author_meta( 'xa_ups_user_id', $user->ID ) ); ?>" class="regular-text" /><br />
					<span class="description">Please enter your UPS User Id.</span>
				</td>
			</tr>
			<tr>
				<th><label for="xa_ups_password">UPS Password</label></th>
				<td>
					<input type="text" name="xa_ups_password" id="xa_ups_password" value="<?php echo esc_attr( get_the_author_meta( 'xa_ups_password', $user->ID ) ); ?>" class="regular-text" /><br />
					<span class="description">Please enter your UPS Password.</span>
				</td>
			</tr>
			<tr>
				<th><label for="xa_ups_access_key">UPS Access Key</label></th>
				<td>
					<input type="text" name="xa_ups_access_key" id="xa_ups_access_key" value="<?php echo esc_attr( get_the_author_meta( 'xa_ups_access_key', $user->ID ) ); ?>" class="regular-text" /><br />
					<span class="description">Please enter your UPS access key.</span>
				</td>
			</tr>
			<tr>
				<th><label for="xa_ups_account_number">UPS Account Number</label></th>
				<td>
					<input type="text" name="xa_ups_account_number" id="xa_ups_account_number" value="<?php echo esc_attr( get_the_author_meta( 'xa_ups_account_number', $user->ID ) ); ?>" class="regular-text" /><br />
					<span class="description">Please enter your UPS account number.</span>
				</td>
			</tr>

			<!-- USPS Account details -->
			<tr><th style="font-size: 16px">USPS Account Details:</th></tr>
			<tr>
				<th><label for="usps_user_id">USPS User Id</label></th>

				<td>
					<input type="text" name="usps_user_id" id="usps_user_id" value="<?php echo esc_attr( get_the_author_meta( 'usps_user_id', $user->ID ) ); ?>" class="regular-text" /><br />
					<span class="description">Please enter your USPS User Id.</span>
				</td>
			</tr>
			<tr>
				<th><label for="usps_password">USPS User Passwors</label></th>

				<td>
					<input type="text" name="usps_password" id="usps_password" value="<?php echo esc_attr( get_the_author_meta( 'usps_password', $user->ID ) ); ?>" class="regular-text" /><br />
					<span class="description">Please enter your USPS Password.</span>
				</td>
			</tr>

			<!-- Stamos Account details -->
			<tr><th style="font-size: 16px">Stamps Account Details:</th></tr>
			<tr>
				<th><label for="stamps_usps_username">Stamps user name</label></th>

				<td>
					<input type="text" name="stamps_usps_username" id="stamps_usps_username" value="<?php echo esc_attr( get_the_author_meta( 'stamps_usps_username', $user->ID ) ); ?>" class="regular-text" /><br />
					<span class="description">Please enter your Stamps user name.</span>
				</td>
			</tr>
			<tr>
				<th><label for="stamps_usps_password">Stamps Passwors</label></th>
				<td>
					<input type="text" name="stamps_usps_password" id="stamps_usps_password" value="<?php echo esc_attr( get_the_author_meta( 'stamps_usps_password', $user->ID ) ); ?>" class="regular-text" /><br />
					<span class="description">Please enter your Stamps Password.</span>
				</td>
			</tr>

			<!-- DHL Account details -->
			<tr><th style="font-size: 16px">DHL Account Details:</th></tr>
			<tr>
				<th><label for="dhl_account_number">DHL Account number</label></th>
				<td>
					<input type="text" name="dhl_account_number" id="dhl_account_number" value="<?php echo esc_attr( get_the_author_meta( 'dhl_account_number', $user->ID ) ); ?>" class="regular-text" /><br />
					<span class="description">Please enter your Stamps user name.</span>
				</td>
			</tr>
			<tr>
				<th><label for="dhl_siteid">DHL Site Id</label></th>
				<td>
					<input type="text" name="dhl_siteid" id="dhl_siteid" value="<?php echo esc_attr( get_the_author_meta( 'dhl_siteid', $user->ID ) ); ?>" class="regular-text" /><br />
					<span class="description">Please enter your DHL siteid.</span>
				</td>
			</tr>
			<tr>
				<th><label for="dhl_password">DHL Passwors</label></th>
				<td>
					<input type="text" name="dhl_password" id="dhl_password" value="<?php echo esc_attr( get_the_author_meta( 'dhl_password', $user->ID ) ); ?>" class="regular-text" /><br />
					<span class="description">Please enter your DHL Password.</span>
				</td>
			</tr>

		</table>
		<?php
	}



	function  xa_save_myaccount_fields( $user_id ) {
		if( isset ( $_POST[ 'tin_number' ])) {
			// Copy and paste this line for additional fields. Make sure to change 'tin_number' to the field ID. 
			update_user_meta( $user_id, 'tin_number', $_POST['tin_number'] );
			update_user_meta( $user_id, 'xa_fedex_account_number', $_POST['xa_fedex_account_number'] );
			update_user_meta( $user_id, 'xa_fedex_meter_number', $_POST['xa_fedex_meter_number'] );
			update_user_meta( $user_id, 'xa_fedex_web_services_key', $_POST['xa_fedex_web_services_key'] );
			update_user_meta( $user_id, 'xa_fedex_web_services_password', $_POST['xa_fedex_web_services_password'] );
			
			// Save UPS details
			update_user_meta( $user_id, 'xa_ups_user_id', $_POST['xa_ups_user_id'] );
			update_user_meta( $user_id, 'xa_ups_password', $_POST['xa_ups_password'] );
			update_user_meta( $user_id, 'xa_ups_access_key', $_POST['xa_ups_access_key'] );
			update_user_meta( $user_id, 'xa_ups_account_number', $_POST['xa_ups_account_number'] );
			
			//USPS
			update_user_meta( $user_id, 'usps_user_id', $_POST['usps_user_id'] );
			update_user_meta( $user_id, 'usps_password', $_POST['usps_password'] );

			//Stamps
			update_user_meta( $user_id, 'stamps_usps_username', $_POST['stamps_usps_username'] );
			update_user_meta( $user_id, 'stamps_usps_password', $_POST['stamps_usps_password'] );
			
			//DHL
			update_user_meta( $user_id, 'dhl_account_number', $_POST['dhl_account_number'] );
			update_user_meta( $user_id, 'dhl_siteid', $_POST['dhl_siteid'] );
			update_user_meta( $user_id, 'dhl_password', $_POST['dhl_password'] );
		}
	}




	function get_vendor_plugin(){
		$vendor_addon = '';
		if( isset($vendor_profile['_wcv_store_address1']) ){
			$vendor_addon = 'wc_vendors_pro';	
		}elseif( isset($vendor_profile['_wcv_store_address1']) ){
			$vendor_addon = 'dokan_lite';	
		}else{
			$vendor_addon = 'woothemes';
		}
		return $vendor_addon;
	}

	private function get_vendor_id_from_product($order_details){
		global $woocommerce;
		
		if( VENDOR_PLUGIN == 'product_vendor' && WC_Product_Vendors_Utils::is_vendor_product( ( $woocommerce->version <= 3.0 ) ? $order_details['data']->id : $order_details['data']->get_id() ) ){
			//get associated user with vendor.
			$woo_vendor = WC_Product_Vendors_Utils::get_vendor_id_from_product( ( $woocommerce->version <= 3.0 ) ? $order_details['data']->id : $order_details['data']->get_id() );
			$vendor = WC_Product_Vendors_Utils::get_vendor_data_by_id($woo_vendor);

			if( ! is_array($vendor['admins']) ){
				$vendor = explode(',', $vendor['admins']);
			}else{
				$vendor = $vendor['admins'];
			}

			if( !empty($vendor[0]) ){
				return $vendor[0]; //assume only one user associated with vendor, taking fist user.
			}
			// if not user found let retun post auther.
		}
		$post = get_post($order_details['data']->get_id());
		return $post->post_author;
	}

	public function wf_vendor_label_packages( $packages, $ship_from_address_context='vendor_address' ) {
		//if origin preference is not vendor address, Do nothing.
		if ($ship_from_address_context !== 'vendor_address')
			return $packages;

		$vendor_packages = array();
		
		foreach ($packages as $package) {
		
			foreach ($package['contents'] as $order_details) {
		
				$vendor_id = $this->get_vendor_id_from_product($order_details);
				
				$vendor_packages[$vendor_id]['contents'][] = $order_details;
				$vendor_packages[$vendor_id]['destination'] = $package['destination'];

				$vendor_address = $this->get_vendor_address( $vendor_id );
				$vendor_packages[$vendor_id]['origin'] = $this->wf_formate_origin_address($vendor_address);
			}
		
		}
		
		// Now the packages array will be indexed by vendor ID.
		return $vendor_packages;
	}

	private function get_vendor_address( $vndr_id ){
		$vendor_profile = get_user_meta($vndr_id);

		$vendor_details= array();
		switch (VENDOR_PLUGIN) {
			case 'dokan_lite':

				if( function_exists('dokan_get_seller_id_by_order') ){
					$dokan_profile = get_user_meta( $vndr_id, 'dokan_profile_settings', true );
				}

				//For older version of Dokan plugin.
				if( empty($dokan_profile['address']) ){
					$dokan_profile = isset( $vendor_profile['dokan_profile_settings'][0] ) ? unserialize( $vendor_profile['dokan_profile_settings'][0] ) : '';
				}
				
				$vendor_details['vendor_country'] 	= isset( $dokan_profile['address']['country'] ) ? $dokan_profile['address']['country'] : '';
				$vendor_details['vendor_fname']		= isset( $vendor_profile['billing_first_name'][0] ) ? $vendor_profile['billing_first_name'][0] : '' ;
				$vendor_details['vendor_lname']		= isset( $vendor_profile['billing_last_name'][0] ) ? $vendor_profile['billing_last_name'][0] : '';
				$vendor_details['vendor_company']	= isset( $dokan_profile['store_name'] ) ? $dokan_profile['store_name'] : '';
				$vendor_details['vendor_address1']	= isset( $dokan_profile['address']['street_1'] ) ? $dokan_profile['address']['street_1'] : '';
				$vendor_details['vendor_address2']	= isset( $dokan_profile['address']['street_2'] ) ? $dokan_profile['address']['street_2'] : '';
				$vendor_details['vendor_city']		= isset( $dokan_profile['address']['city'] ) ? $dokan_profile['address']['city'] : '';
				$vendor_details['vendor_state']		= isset( $dokan_profile['address']['state'] ) ? $dokan_profile['address']['state'] : '';
				$vendor_details['vendor_zip']		= isset( $dokan_profile['address']['zip'] ) ? $dokan_profile['address']['zip'] : '';
				$vendor_details['vendor_phone']		= isset( $dokan_profile['phone'] ) ? $dokan_profile['phone'] : '';
				$vendor_details['email']			= isset( $vendor_profile['billing_email'][0] ) ? $vendor_profile['billing_email'][0] : '';
				break;

			case 'wc_vendors_pro':
				$vendor_details['vendor_country'] 	= isset( $vendor_profile['_wcv_store_country'][0] ) ? $vendor_profile['_wcv_store_country'][0] : '';
				$vendor_details['vendor_fname']		= isset( $vendor_profile['first_name'][0] ) ? $vendor_profile['first_name'][0] : '';
				$vendor_details['vendor_lname']		= isset( $vendor_profile['last_name'][0] ) ? $vendor_profile['last_name'][0] : '';
				$vendor_details['vendor_company']	= isset( $vendor_profile['pv_shop_name'][0] ) ? $vendor_profile['pv_shop_name'][0] : '';
				$vendor_details['vendor_address1']	= isset( $vendor_profile['_wcv_store_address1'][0] ) ? $vendor_profile['_wcv_store_address1'][0] : '';
				$vendor_details['vendor_address2']	= isset( $vendor_profile['_wcv_store_address2'][0] ) ? $vendor_profile['_wcv_store_address2'][0] : '';
				$vendor_details['vendor_city']		= isset( $vendor_profile['_wcv_store_city'][0] ) ? $vendor_profile['_wcv_store_city'][0] : '';
				$vendor_details['vendor_state']		= isset( $vendor_profile['_wcv_store_state'][0] ) ? $vendor_profile['_wcv_store_state'][0] : '';
				$vendor_details['vendor_zip']		= isset( $vendor_profile['_wcv_store_postcode'][0] ) ? $vendor_profile['_wcv_store_postcode'][0] : '';
				$vendor_details['vendor_phone']		= isset( $vendor_profile['_wcv_store_phone'][0] ) ? $vendor_profile['_wcv_store_phone'][0] : '';
				$vendor_details['email']			= isset( $vendor_profile['billing_email'][0] ) ? $vendor_profile['billing_email'][0] : '';
				break;
			
			default:

				$vendor_details['vendor_country'] 	= isset( $vendor_profile['billing_country'][0] ) ? $vendor_profile['billing_country'][0] : '';
				$vendor_details['vendor_fname']		= isset( $vendor_profile['billing_first_name'][0] ) ? $vendor_profile['billing_first_name'][0] : '';
				$vendor_details['vendor_lname']		= isset( $vendor_profile['billing_last_name'][0] ) ? $vendor_profile['billing_last_name'][0] : '';
				$vendor_details['vendor_company']	= isset( $vendor_profile['billing_company'][0] ) ? $vendor_profile['billing_company'][0] : '';
				$vendor_details['vendor_address1']	= isset( $vendor_profile['billing_address_1'][0] ) ? $vendor_profile['billing_address_1'][0] : '';
				$vendor_details['vendor_address2']	= isset( $vendor_profile['billing_address_2'][0] ) ? $vendor_profile['billing_address_2'][0] : '';
				$vendor_details['vendor_city']		= isset( $vendor_profile['billing_city'][0] ) ? $vendor_profile['billing_city'][0] : '';
				$vendor_details['vendor_state']		= isset( $vendor_profile['billing_state'][0] ) ? $vendor_profile['billing_state'][0] : '';
				$vendor_details['vendor_zip']		= isset( $vendor_profile['billing_postcode'][0] ) ? $vendor_profile['billing_postcode'][0] : '';
				$vendor_details['vendor_phone']		= isset( $vendor_profile['billing_phone'][0] ) ? $vendor_profile['billing_phone'][0] : '';
				$vendor_details['email']			= isset( $vendor_profile['billing_email'][0] ) ? $vendor_profile['billing_email'][0] : '';
				break;
		}
		$vendor_details['tin_number']			= isset( $vendor_profile['tin_number'][0] ) ? $vendor_profile['tin_number'][0] : '';
		return $vendor_details;
	}

	//function to get vendor address for api request
	public function wf_vendor_label_from_address( $from_address , $package, $ship_from_address_context='vendor_address' ) {
		if( empty($package['contents']) ) {
			return $from_address;
		}
		//if origin preference is not vendor address , Do nothing.
		if ($ship_from_address_context !== 'vendor_address')
			return $from_address;

		if( empty($package['origin']) ){
			$vendor_id	=	$this->get_vendor_id_from_product( array_shift($package['contents']) );
			$package['origin'] = $this->wf_formate_origin_address( $this->get_vendor_address($vendor_id) );
		}
		
		if( empty($package['origin']['country']) || empty($package['origin']['postcode']) ){
			return $from_address;
		}

		$from_address = array(
			'name' 		=> $package['origin']['first_name'] . ' ' . $package['origin']['last_name'],
			'company' 	=> $package['origin']['company'],
			'phone' 	=> $package['origin']['phone'],
			'address_1'	=> $package['origin']['address_1'],
			'address_2'	=> $package['origin']['address_2'],
			'city' 		=> strtoupper($package['origin']['city']),
			'state' 	=> strlen($package['origin']['state']) == 2 ? strtoupper($package['origin']['state']) : '',
			'country' 	=> $package['origin']['country'],
			'postcode' 	=> str_replace(' ', '', strtoupper($package['origin']['postcode'])),
			'tin_number' 	=> $package['origin']['tin_number'],
			'email'		=> $package['origin']['email'],
		);
		return $from_address;
	}

	function wf_splited_packages($packages, $ship_from_address_context='' ){
		
		//if origin preference is not vendor address , Do nothing.
		if ( ( $ship_from_address_context != '' && $ship_from_address_context !== 'vendor_address' ) ) {
			return $packages;
		}
		
		$new_packages			  	= array();		
		//Init splitted package
		$splitted_packages		=	array();
		$vendor_id = '';
		// group items by vendor
		foreach ( WC()->cart->get_cart() as $item_key => $item ) {
			if ( $item['data']->needs_shipping() ) {
				$vendor_id	=	$this->get_vendor_id_from_product($item);
				$splitted_packages[$vendor_id][$item_key]	=	$item;
			}
		}
		

		// Add grouped items as packages 
		if(is_array($splitted_packages)){
			
			foreach($splitted_packages as $vendor_id => $splitted_package_items){
				$vendor_address = $this->get_vendor_address($vendor_id);

				$new_packages[] = array(
					'contents'		=> $splitted_package_items,
					'contents_cost'   => array_sum( wp_list_pluck( $splitted_package_items, 'line_total' ) ),
					'applied_coupons' => WC()->cart->get_applied_coupons(),
					'user'			=> array(
						 'ID' => $vendor_id
					),
					'origin'		 => $this->wf_formate_origin_address($vendor_address),
					'destination'	=> array(
						'country'	=> WC()->customer->get_shipping_country(),
						'state'	  => WC()->customer->get_shipping_state(),
						'postcode'   => WC()->customer->get_shipping_postcode(),
						'city'	   => WC()->customer->get_shipping_city(),
						'address'	=> WC()->customer->get_shipping_address(),
						'address_2'  => WC()->customer->get_shipping_address_2()
					)
				);
			}
		}

		return $new_packages;
	}

	private function wf_formate_origin_address($vendor_address){
		return array(
			'country' 		=> $vendor_address['vendor_country'],
			'first_name'	=> $vendor_address['vendor_fname'],
			'last_name'		=> $vendor_address['vendor_lname'],
			'company'		=> $vendor_address['vendor_company'],
			'address_1'		=> $vendor_address['vendor_address1'],
			'address_2'		=> $vendor_address['vendor_address2'],
			'city' 			=> $vendor_address['vendor_city'],
			'state'			=> $vendor_address['vendor_state'],
			'postcode' 		=> $vendor_address['vendor_zip'],
			'phone' 		=> $vendor_address['vendor_phone'],
			'email' 		=> $vendor_address['email'],
			'tin_number' 	=> isset($vendor_address['tin_number']) ? $vendor_address['tin_number'] : '',

		);
	}
	
	
	/**
	 * To update UPS credentials and shipper address in rate request.
	 * @param array $rate_request_data UPS credentials and origin address
	 * @param type $package UPS Packages of single vendor
	 * @return array Updated UPS credentials and origin address.
	 */
	public function xa_change_ups_credentials_in_rate_request( $rate_request_data, $main_package, $package ){
		$package = current($package);
		$items = current($package['Package']['items']);
		$item = is_array($items) ? current($items) : $items;
		$vendor = null;
		if( ! empty($item) ) {
			$author = $this->xa_get_post_author($item->get_id());
			foreach( $author->roles as $role ) {
				if( strstr( $role, 'vendor') || strstr( $role, 'seller') ) {			//seller for dokan vendor
					$vendor = $author;
					break;
				}
			}
			$ups_user_id = ! empty($vendor) ? $vendor->get('xa_ups_user_id') : null;
			if( ! empty($ups_user_id) ) {
				
				$vendor_address														= $this->get_vendor_address($author->ID);
				$formatted_vendor_address											= $this->wf_formate_origin_address($vendor_address);
				
				$rate_request_data	=	array(
					'user_id'			=>	$ups_user_id,
					'password'			=>	str_replace( '&', '&amp;', $vendor->get('xa_ups_password') ), // Ampersand will break XML doc, so replace with encoded version.
					'access_key'		=>	$vendor->get('xa_ups_access_key'),
					'shipper_number'	=>	$vendor->get('xa_ups_account_number'),
					'origin_addressline'=>	$formatted_vendor_address['address_1'].' '.$formatted_vendor_address['address_2'],
					'origin_postcode'	=>	$formatted_vendor_address['postcode'],
					'origin_city'		=>	$formatted_vendor_address['city'],
					'origin_state'		=>	$formatted_vendor_address['state'],
					'origin_country'	=>	$formatted_vendor_address['country'],
				);
			}
			
		}
		return $rate_request_data;
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
		
	
	/**
	 * Split the UPS Shipment based on vendors while Confirming the shipment .
	 * @param array $shipments Shipments
	 * @param object $order Order object
	 * @return array Shipment
	 */
	public function xa_ups_split_shipment( $shipments, $order ) {
		$order = wc_get_order($order);
		$items = $order->get_items();
		$vendor = null;
		foreach( $items as $item ) {
			
			$author = $this->xa_get_post_author($item->get_product_id());
			foreach( $author->roles as $role ) {
				if( strstr( $role, 'vendor') || strstr( $role, 'seller') ) {	//seller for dokan vendor
					$vendor = $author->ID;
					break;
				}
			}
		}
		
		// No vendor products exist in order then return
		if( empty($vendor) ) {
			return $shipments;
		}

		foreach( $shipments as $key => $shipment ) {
			$services = $shipment['shipping_service'];
			foreach( $shipment['packages'] as $package ) {
				$all_shipments[] = array(
					'shipping_service'	=> $services,
					'packages'			=> array($package),
				);
			}
		}

		return !empty($all_shipments) ? $all_shipments : $shipments;
	}
	
	/**
	 * Update vendor account info and address in UPS request while confirm shipment.
	 * @param array $request_arr UPS request array
	 * @param object $order wf_order object
	 * @param array $shipment Shipment
	 * @return array UPS request array
	 */
	public function xa_ups_update_request_info_confirm_shipment( $xml_request, $order, $shipment ) {

		$package	= current($shipment['packages']);
		$item		= current($package['Package']['items']);
		$author		= $this->xa_get_post_author($item->get_id());
		
		//If products belong to vendor and vendor UPS account is configured then only proceed
		$ups_account_number = $author->get('xa_ups_account_number');
		
		if( ! empty($ups_account_number) ) {
			$vendor_address														= $this->get_vendor_address($author->ID);
			$formatted_vendor_address											= $this->wf_formate_origin_address($vendor_address);
			$req_arr															= explode('<?xml version="1.0" ?>', $xml_request);
			$new_xml_request	=	'<?xml version="1.0" encoding="UTF-8"?>';
			$new_xml_request	.=	'<AccessRequest xml:lang="en-US">';
			$new_xml_request	.=	'<AccessLicenseNumber>'.$author->get('xa_ups_access_key').'</AccessLicenseNumber>';
			$new_xml_request	.=	'<UserId>'.$author->get('xa_ups_user_id').'</UserId>';
			$new_xml_request	.=	'<Password>'.$author->get('xa_ups_password').'</Password>';
			$new_xml_request	.=	'</AccessRequest>';
			
			$xml_request_obj_1													= new SimpleXMLElement($req_arr[1]);
			$xml_request_obj_1->Shipment->Shipper->Name							= $formatted_vendor_address['first_name'].' '.$formatted_vendor_address['last_name'];
			$xml_request_obj_1->Shipment->Shipper->AttentionName				= $formatted_vendor_address['company'];
			$xml_request_obj_1->Shipment->Shipper->PhoneNumber					= $formatted_vendor_address['phone'];
			$xml_request_obj_1->Shipment->Shipper->EMailAddress					= $formatted_vendor_address['email'];
			$xml_request_obj_1->Shipment->Shipper->ShipperNumber				= $ups_account_number;
			$xml_request_obj_1->Shipment->Shipper->Address->AddressLine1		= $formatted_vendor_address['address_1'];
			
			if( ! empty($formatted_vendor_address['address_2']) ) {
				$xml_request_obj_1->Shipment->Shipper->Address->AddressLine2	= $formatted_vendor_address['address_2'];
			}
			
			$xml_request_obj_1->Shipment->Shipper->Address->City				= $formatted_vendor_address['city'];
			$xml_request_obj_1->Shipment->Shipper->Address->StateProvinceCode	= $formatted_vendor_address['state'];
			$xml_request_obj_1->Shipment->Shipper->Address->CountryCode			= $formatted_vendor_address['country'];
			$xml_request_obj_1->Shipment->Shipper->Address->PostalCode			= $formatted_vendor_address['postcode'];
			
			if( !empty($xml_request_obj_1->Shipment->PaymentInformation->Prepaid->BillShipper->AccountNumber) ) {
				$xml_request_obj_1->Shipment->PaymentInformation->Prepaid->BillShipper->AccountNumber	= $ups_account_number;
			}
			$doc = new DOMDocument();
//			$doc->formatOutput = TRUE;					// To get formatted xml
			$doc->loadXML($xml_request_obj_1->asXML());
			$new_xml_request .= $doc->saveXML();
		}
		return ! empty($new_xml_request) ? $new_xml_request : $xml_request;
	}
	
	/**
	 * Update the UPS credentials in accept shipment XML request .
	 * @param xml $xml_request XML request for confirm shipment .
	 * @param string $shipment_id Shipment id for which shipment has to be accepted .
	 * @param object $order wc_order.
	 * @return xml XML request for accept shipment .
	 */
	public function xa_ups_modify_accept_shipment_xml_request( $xml_request, $shipment_id, $order_id ) {
		$order  = wc_get_order($order_id);

		$xml_request_array	= $order->get_meta( 'ups_created_shipments_xml_request_array', true );
		$stored_xml_request	= !empty($xml_request_array["$shipment_id"]) ? $xml_request_array["$shipment_id"] : null;
		if( ! empty($stored_xml_request) ) {
			$req_arr_0			=	strstr( $stored_xml_request, '<?xml version="1.0"?>', true );										// It will contain the xml with vendor details
			$req_arr_0			=	empty($req_arr_0) ? strstr( $stored_xml_request, '<?xml version="1.0" ?>', true ) : $req_arr_0;		// It will contain the xml with vendor details
			$req_arr_1			=	strstr( $xml_request, '<?xml version="1.0" ?>');													// It will contain the remaining xml elements
			$new_xml_request	= $req_arr_0.$req_arr_1;
		}
		return !empty($new_xml_request) ? $new_xml_request : $xml_request;
	}
	
	/**
	 * Update the UPS credentials in void shipment XML request .
	 * @param xml $xml_request Void Shipment XML Request.
	 * @param string $shipment_id Shipment Id for which shipment has to be voided .
	 * @param object $order wc_order object
	 * @return xml Updated void shipment XML request.
	 */
	public function xa_ups_void_shipment_xml_request( $xml_request, $shipment_id, $order_id ) {
		
		$order = wc_get_order($order_id);
		$stored_xml_request_array	= $order->get_meta( 'ups_created_shipments_xml_request_array', true );
		$stored_xml_request			= ! empty($stored_xml_request_array["$shipment_id"]) ? $stored_xml_request_array["$shipment_id"] : null;
		
		if( ! empty($stored_xml_request) ) {
			$req_arr_0			=	strstr( $stored_xml_request, '<?xml version="1.0"?>', true );		// xml with vendor details
			$req_arr_0			=	str_replace('<?xml version="1.0" encoding="UTF-8"?>', '<?xml version="1.0" ?>', $req_arr_0);
			$req_arr_1			=	strstr( $xml_request, '<?xml version="1.0" encoding="UTF-8" ?>');
			$new_xml_request	=	$req_arr_0.$req_arr_1;
		}
		
		return ! empty($new_xml_request) ? $new_xml_request : $xml_request;
	}
}
new class_wf_vendor_addon_admin;