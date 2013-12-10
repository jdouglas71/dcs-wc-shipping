<?php
/**
 * Plugin Name: DCS WooCommerce Warm Belly Shipping
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
					$this->method_description = __( '<b>Warm Belly Shipping based on Total Number of Suits.</b><br /><table border="1"><tr><td>1 suit</td><td>$7</td></tr><tr><td>2 suits</td><td>$10</td></tr><tr><td>3 suits</td><td>$13</td></tr><tr><td>4 suits or more</td><td>Free</td></tr></table>' ); // 
					$this->enabled            = "yes"; // This can be added as an setting but for this example its forced enabled
					$this->countries		  = array( "United States" );
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
						'label' => "3 day US Priority Mail",
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
									'label' => "3 day US Priority Mail",
									'cost' => 7.00,
									'calc_tax' => 'per_order'
								);
							}
							else if( $totalQuantity == 2 )
							{
								$rate = array( 
									'id' => $this->id,
									'label' => "3 day US Priority Mail",
									'cost' => 10.00,
									'calc_tax' => 'per_order'
								);
							}
							else 
							{
								$rate = array( 
									'id' => $this->id,
									'label' => "3 day US Priority Mail",
									'cost' => 13.00,
									'calc_tax' => 'per_order'
								);
							}
	
						}
					}

					error_log( "Finishing\n", 3, $dir."/dcs_wc_shipping.log" );

					$this->add_rate( $rate );
				}

				/**
				 * is_available function.
				 *
				 * @access public
				 * @param mixed $package
				 * @return bool
				 */
				function is_available( $package ) {
					global $woocommerce;

					if ($this->enabled=="no") return false;

					if ($this->availability=='including') :

						if (is_array($this->countries)) :
							if ( ! in_array( $package['destination']['country'], $this->countries) ) return false;
						endif;

					else :

						if (is_array($this->countries)) :
							if ( in_array( $package['destination']['country'], $this->countries) ) return false;
						endif;

					endif;

					$state = $package['destination']['state'];
					if( $state == "AK" || $state == "HI" )
					{
						return false;
					}

					return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', true );
				}
			}
		}

		if( !class_exists( 'WC_DCS_Warmbelly_FedEx_Shipping_Method' ) ) {
			class WC_DCS_Warmbelly_FedEx_Shipping_Method extends WC_Shipping_Method {
				/**
				 * Constructor for your shipping class
				 *
				 * @access public
				 * @return void
				 */
				public function __construct() {
					$this->id                 = 'dcs_warmbelly_fedex_shipping_method';
					$this->method_title       = 'Warm Belly FedEx Shipping Rate';
					$this->title 			  = 'Warm Belly FedEx Shipping Rate';
					$this->method_description = __( '<b>Warm Belly FedEx Shipping.</b><br /><table><tr><td>$20.00 per suit (US Lower 48 only).<br />$35.00 (US - AK & HI)</td></tr></table><br />' ); // 
					$this->enabled            = "yes"; // This can be added as an setting but for this example its forced enabled
					$this->countries 		  = array( "United States" );
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
										'default'       => __( 'FedEx Per Suit Rate', 'woocommerce' ),
										'desc_tip'      => true
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

					$rate = 20.00;

					$state = $package['destination']['state'];
					error_log( "State: ".$state."\n", 3, $dir."/dcs_wc_shipping.log" );
					if( $state == "AK" || $state == "HI" )
					{
						$rate = 35.00;
					}
					error_log( "Rate: ".$rate."\n", 3, $dir."/dcs_wc_shipping.log" );

					if( $woocommerce->cart->needs_shipping() )
					{
						$totalQuantity = 0;

						foreach( $woocommerce->cart->cart_contents as $item )
						{
							$totalQuantity += $item['quantity'];
						}

						error_log( "Shopping Cart Quantity: " . $totalQuantity . "\n", 3, $dir."/dcs_wc_shipping.log" );

						$rate = array( 
							'id' => $this->id,
							'label' => "2 Day FedEx Delivery",
							'cost' => ($totalQuantity * $rate),
							'calc_tax' => 'per_order'
						);

						$this->add_rate( $rate );
					}

					error_log( "Finishing\n", 3, $dir."/dcs_wc_shipping.log" );
				}

				/**
				 * is_available function.
				 *
				 * @access public
				 * @param mixed $package
				 * @return bool
				 */
				function is_available( $package ) {
					global $woocommerce;

					if ($this->enabled=="no") return false;

					if ($this->availability=='including') :

						if (is_array($this->countries)) :
							if ( ! in_array( $package['destination']['country'], $this->countries) ) return false;
						endif;

					else :

						if (is_array($this->countries)) :
							if ( in_array( $package['destination']['country'], $this->countries) ) return false;
						endif;

					endif;

					return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', true );
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
		$methods[] = 'WC_DCS_Warmbelly_FedEx_Shipping_Method'; 
		return $methods;
	}
    add_filter( 'woocommerce_shipping_methods', 'dcs_add_your_shipping_method' );
}
