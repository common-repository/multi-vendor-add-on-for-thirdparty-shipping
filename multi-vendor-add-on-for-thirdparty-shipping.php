<?php
/*
	Plugin Name: Multi-Vendor Add-On for XA Shipping Plugins
	Plugin URI: https://www.xadapter.com/product/multi-vendor-addon/
	Description: XA Vendor Plugin Addon for Print shipping labels via FedEx and DHL Shipping API.
	Version: 1.2.2
	Author: PluginHive
	Author URI: https://www.pluginhive.com/
	WC requires at least: 2.6.0
	WC tested up to: 3.4
*/

if(!defined('VENDOR_PLUGIN') ){
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	$plugin_list = array(
		'product_vendor' => 'woocommerce-product-vendors/woocommerce-product-vendors.php', 
		'dokan_lite' => 'dokan-lite/dokan.php', 
		'wf_product_vendor' => 'wf-product-vendor/product-vendor-map.php',
		'wc_vendors_pro' => 'wc-vendors-pro/wcvendors-pro.php'
	);
	foreach ($plugin_list as $plugin_name => $slug) {
		if ( is_plugin_active($slug) ){
			define('VENDOR_PLUGIN',$plugin_name);
			break;
		}
	}
}


class wf_vendor_addon_setup {

	public function __construct() {
		add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'plugin_action_links'));
		require_once 'includes/class-xa-shipping-carrier-account-switch.php';
		require_once 'includes/class-xa-send-label-to-vendor.php';
		include_once('includes/class-wf-vendor-addon.php');
		include_once('includes/class-wf-vendor-addon-admin.php');
	}

	public function plugin_action_links($links) {
		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=' . wf_get_settings_url() . '&tab=wf_vendor_addon' ) . '">' . __( 'Settings', 'wf-shipping-vendor-addon' ) . '</a>',
			'<a href="https://www.xadapter.com/category/product/woocommerce-fedex-multi-vendor-addon/" target="_blank">' . __('Documentation', 'wf-shipping-vendor-addon') . '</a>',
			'<a href="https://wordpress.org/support/plugin/multi-vendor-add-on-for-thirdparty-shipping" target="_blank">' . __('Support', 'wf-shipping-vendor-addon') . '</a>'
		);
		return array_merge($plugin_links, $links);
	}

}

new wf_vendor_addon_setup();


/* Add Vendor Option in settings and in Print label request.
 * 
 */
add_filter('wf_filter_label_ship_from_address_options', 'wf_vendor_label_ship_from_address_options', 10, 4);

if (!function_exists('wf_get_settings_url')){
		function wf_get_settings_url(){
			return version_compare(WC()->version, '2.1', '>=') ? "wc-settings" : "woocommerce_settings";
		}
}
//Add vendor address option to shipping address options if vendor plugin is enabled.
if(!function_exists('wf_vendor_label_ship_from_address_options')){
	function wf_vendor_label_ship_from_address_options($args) {
		if( defined('VENDOR_PLUGIN') && VENDOR_PLUGIN !='' ){
			$args['vendor_address'] = __('Vendor Address', 'wf-shipping-vendor-addon');
		}
		return $args;
	}
}

/*
* Option to change Shipping name.
* default is set to seller company name.
*/
add_filter('woocommerce_shipping_package_name', 'xa_change_shipping_name', 10, 3 );
if(!function_exists('xa_change_shipping_name')){
	function xa_change_shipping_name( $name, $shipping_number, $package){
		if( !empty($package['origin']) )
			return !empty( $package['origin']['company'] ) ? $package['origin']['company'] : $package['origin']['first_name'] ;
		else
			return $name;
	}
}

/**
 * Add Vendor option to Send label in email.
 */
add_filter( 'ph_fedex_filter_label_send_in_email_to_options', function($args) {
	$args['vendor'] = __( 'Vendor', 'wf-shipping-vendor-addon' );
	return $args;
} );