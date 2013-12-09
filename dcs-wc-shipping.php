<?php
/**
 * Plugin Name: DCS WooCommerce Shipping
 * Description: Creates a shipping method specific to WarmBelly.com.
 * Version: 0.2
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
		if( !class_exists( 'WC_DCS_Warmbelly_Shipping_Method' ) ) {
			class WC_DCS_Warmbelly_Shipping_Method extends WC_Shipping_Method {
				/**
				 * Constructor for your shipping class
				 *
				 * @access public
				 * @return void
				 */
				public function __construct() {
					$this->id                 = 'dcs_warmbelly_shipping_method';
					$this->method_title       = 'Warm Belly Shipping Rate';
					$this->title 			  = 'Warm Belly Shipping Rate';
					$this->method_description = __( '<b>Warm Belly Shipping based on Total Number of Suits.</b><br /><table border="1"><tr><td>1-3 suits</td><td>$7 per suit</td></tr><tr><td>4 suits or more</td><td>Free</td></tr></table>' ); // 
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
										'default'       => __( 'Per Suit Rate', 'woocommerce' ),
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

					$dir = plugin_dir_path( __FILE__ );

					error_log( "Starting\n", 3, $dir."/dcs_wc_shipping.log" );
					//error_log( "Package: ". var_export($package,true)."\n", 3, $dir."/dcs_wc_shipping.log" );  
					//error_log( "Package ends.\n\n\n\n", 3, $dir."/dcs_wc_shipping.log" ); 
					//error_log( "Cart: ". var_export($woocommerce->cart->get_cart(),true)."\n", 3, $dir."/dcs_wc_shipping.log" );  
					//error_log( "Cart Ends. \n\n\n\n", 3, $dir."/dcs_wc_shipping.log" ); 

					$rate = array( 
						'id' => $this->id,
						'label' => "Free Shipping",
						'cost' => 0,
						'calc_tax' => 'per_order'
					);

					if( $woocommerce->cart->needs_shipping() )
					{
						$totalQuantity = 0;

						foreach( $woocommerce->cart->cart_contents as $item )
						{
							$totalQuantity += $item['quantity'];
						}

						error_log( "Shopping Cart Quantity: " . $totalQuantity . "\n", 3, $dir."/dcs_wc_shipping.log" );

						if( $totalQuantity < 4 )
						{
							if( $totalQuantity == 1 )
							{
								$rate = array( 
									'id' => $this->id,
									'label' => "Bulk Suit Rate",
									'cost' => 7.00,
									'calc_tax' => 'per_order'
								);
							}
							else if( $totalQuantity == 2 )
							{
								$rate = array( 
									'id' => $this->id,
									'label' => "Bulk Suit Rate",
									'cost' => 10.00,
									'calc_tax' => 'per_order'
								);
							}
							else 
							{
								$rate = array( 
									'id' => $this->id,
									'label' => "Bulk Suit Rate",
									'cost' => 13.00,
									'calc_tax' => 'per_order'
								);
							}
	
						}
					}

					error_log( "Finishing\n", 3, $dir."/dcs_wc_shipping.log" );

					$this->add_rate( $rate );
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
		$methods[] = 'WC_DCS_Warmbelly_Shipping_Method'; 
		return $methods;
	}
    add_filter( 'woocommerce_shipping_methods', 'dcs_add_your_shipping_method' );
}
