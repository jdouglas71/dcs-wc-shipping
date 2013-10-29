<?php
/**
 * Plugin Name: DCS WooCommerce Shipping
 * Description: Creates a flat rate shipping method for woocommerce based on the order total.
 * Version: 0.1
 * Author: Jason Douglas
 * Author URI: http://douglasconsulting.net
 * License: GPL2
 */

/**
* Check if WooCommerce is active
*/
if ( in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) ) 
{
    // Put your plugin code here
}
