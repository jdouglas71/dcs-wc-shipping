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
if( in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) ) 
{
	function dcs_wc_shipping_method_init()
	{
		if( !class_exists( 'WC_DCS_Flat_Shipping_Method' ) ) {
			class WC_DCS_Flat_Shipping_Method extends WC_Shipping_Method {
				/**
				 * Constructor for your shipping class
				 *
				 * @access public
				 * @return void
				 */
				public function __construct() {
					$this->id                 = 'dcs_flat_shipping_method';
					$this->method_title       = '% Based Flat Rate';
					$this->title 			  = 'Percentage Based Flat Rate';
					$this->method_description = __( '<b>Flat Rate Shipping based on Order Total.</b><br /><table border="1"><tr><td>$1-$50</td><td>$4.95</td></tr><tr><td>$51-$125</td><td>$7.95</td></tr><tr><td>$126+</td><td>$10.95</td></tr></table>' ); // 
					$this->enabled            = "yes"; // This can be added as an setting but for this example its forced enabled
					$this->init();
				}
		
				/**
				 * Init your settings
				 *
				 * @access public
				 * @return void
				 */
				function init() 
				{
					// Load the settings API
					$this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
					$this->init_settings(); // This is part of the settings API. Loads settings you previously init.
		
					// Save settings in admin if you have any defined
					add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
				}

				/**
				 * Initialise Gateway Settings Form Fields
				 *
				 * @access public
				 * @return void
				 */
				function init_form_fields() {
					global $woocommerce;
			
					$this->form_fields = array(
						'enabled' => array(
										'title'         => __( 'Enable/Disable', 'woocommerce' ),
										'type'          => 'checkbox',
										'label'         => __( 'Enable this shipping method', 'woocommerce' ),
										'default'       => 'yes',
									),
						'title' => array(
										'title'         => __( 'Method Title', 'woocommerce' ),
										'type'          => 'text',
										'description'   => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
										'default'       => __( 'Percentage Based Flat Rate', 'woocommerce' ),
										'desc_tip'      => true
									),
						'availability' => array(
										'title'         => __( 'Availability', 'woocommerce' ),
										'type'          => 'select',
										'default'       => 'all',
										'class'         => 'availability',
										'options'       => array(
											'all'       => __( 'All allowed countries', 'woocommerce' ),
											'specific'  => __( 'Specific Countries', 'woocommerce' ),
										),
									),
						'countries' => array(
										'title'         => __( 'Specific Countries', 'woocommerce' ),
										'type'          => 'multiselect',
										'class'         => 'chosen_select',
										'css'           => 'width: 450px;',
										'default'       => '',
										'options'       => $woocommerce->countries->countries,
									),
						);
				}

		
				/**
				 * calculate_shipping function.
				 *
				 * @access public
				 * @param mixed $package
				 * @return void
				 */
				public function calculate_shipping( $package ) 
				{
					global $woocommerce;

					$totalCost = $woocommerce->cart->get_cart_total();
					$cost = 4.95;

					if( ($totalCost > 50) && ($totalCost < 125) )
					{
						$cost = 7.95;
					}
					else if( $totalCost > 125 )
					{
						$cost = 10.95;
					}

					$rate = array( 
						'id' => $this->id,
						'label' => $this->title,
						'cost' => $cost,
						'calc_tax' => 'per_order'
					);
				}
			}
		}
	}
	add_action( 'woocommerce_shipping_init', 'dcs_wc_shipping_method_init' );

	/**
	 * 
	 */
    function dcs_add_your_shipping_method( $methods ) 
	{
		$methods[] = 'WC_DCS_Flat_Shipping_Method'; 
		return $methods;
	}
    add_filter( 'woocommerce_shipping_methods', 'dcs_add_your_shipping_method' );
}
