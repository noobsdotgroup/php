<?php
//https://codex.wordpress.org/File_Header

/**
 * Class WC_Gateway_UPIPayment file.
 *
 * @package WooCommerce\Gateways
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

//http://www.amp-what.com/unicode/search/hand
add_filter( 'woocommerce_endpoint_order-received_title', 'misha_thank_you_title' );
 
function misha_thank_you_title( $old_title ){
//https://rudrastyh.com/woocommerce/thank-you-page.html	
//https://usersinsights.com/woocommerce-thank-you-page/
//https://nicola.blog/2015/01/21/customize-the-thank-you-page-in-woocommerce/
$order_id = wc_get_order_id_by_order_key( $_GET['key'] );
$order = new WC_Order( $order_id );
$payment_method = $order->get_payment_method();
if ( $payment_method === 'upipayment'): 	
 	return 'Order Received! &#9996;';
else:
	return $old_title;
endif;	
 
}
 
 /**
 * Custom text on the receipt page.
 */
//https://isabelcastillo.com/customize-the-text-on-woocommerce-thank-you-page-order-received-page 
function isa_order_received_text( $text, $order ) {
$first_name = $order->get_billing_first_name();

$payment_method = $order->get_payment_method();

if ( $payment_method === 'upipayment'): 
    $new = $text . '<br/><br/>We will notify you about status of your payment & order shortly!<br/><br/>till then, Have a nice day to you '.$first_name.' :)';
    return $new;
else:
	return $text;	
endif;	
}
add_filter('woocommerce_thankyou_order_received_text', 'isa_order_received_text', 10, 2 );

//Custom fields Start

add_filter( 'manage_edit-shop_order_columns', 'custom_shop_order_column', 20 );
function custom_shop_order_column($columns){
    $reordered_columns = array();

    // Inserting columns to a specific location
    foreach( $columns as $key => $column){
        $reordered_columns[$key] = $column;
        if( $key ==  'order_total' ){
            // Inserting after "Status" column
            $reordered_columns['cstupiid'] = __( '<strong>Customer&apos;s UPI ID</strong>','theme_domain');
            $reordered_columns['cstcontact'] = __( '<strong>Contact Customer</strong>','theme_domain');
        }
    }
    return $reordered_columns;
}

/**
 * Adjusts the styles for the new 'Profit' column.
 */
 //https://www.skyverge.com/blog/add-woocommerce-orders-list-column/
function sv_wc_cogs_add_order_profit_column_style() {

    $css = '
	th#cstupiid {width: 16ch !important;}
	th#cstcontact {width: 19ch !important;}
a.sqborder {
    display: inline-block;
    border: 1px solid #0071a1;
    border-radius: 4px;
    padding: 2px 4px 2px 4px;
    background: #f3f5f6;
    margin-right: 2px;
}
	a.sqborder span.dashicons {
    margin-top: 2px;
    margin-bottom: 2px;
	}
/*https://iconify.design/icon-sets/fa/whatsapp.html*/
.waicon::before {
content: url(https://api.iconify.design/fa-whatsapp.svg?color=%230073aa&width=20&height=20);
}
	';
    wp_add_inline_style( 'woocommerce_admin_styles', $css );
}
add_action( 'admin_print_styles', 'sv_wc_cogs_add_order_profit_column_style' );

/**
 * Display field value on the order edit page
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', 'my_custom_checkout_field_display_admin_order_meta', 10, 1 );

function my_custom_checkout_field_display_admin_order_meta($order){
        if( $upiid = $order->get_meta('_upiid') ) {
			echo '<p><strong>'.__('Customer&apos; UPI ID').':</strong> <br/>' . $upiid . '</p>';
		}
}

// Adding custom fields meta data for each new column (example)
add_action( 'manage_shop_order_posts_custom_column' , 'custom_orders_list_column_content', 20, 2 );
function custom_orders_list_column_content( $column, $post_id )
{
global $the_order;
$phone = $the_order->get_billing_phone();
$phone_wp_dashicon = '<span class="dashicons dashicons-phone"></span> '.$phone.'';
			 
$sms_wp_dashicon = '<span class="dashicons dashicons-admin-comments"></span>';
		 
$wa_wp_dashicon = '<span class="dashicons waicon"></span>';

$wa_url = '<a class="sqborder" href="https://wa.me/91'.$phone.'" target="_blank">';	
	
/*if( wp_is_mobile() ):
	$wa_url = '<a class="sqborder" href="https://wa.me/91'.$phone.'">';
 else:
	$wa_url = '<a class="sqborder" href="https://web.whatsapp.com/send?phone=91'.$phone.'&text&source&data" target="_blank">';
endif;
*/
			 
$email = $the_order->get_billing_email();
$email_wp_dashicon = '<span class="dashicons dashicons-email-alt"></span> '.$email.'';
	
    switch ( $column )
    {
        case 'cstupiid' :
            // Get custom post meta data
            //$my_var_one = get_post_meta( $post_id, '_the_meta_key1', true );
			$cstupiid = get_post_meta( $post_id, '_upiid', true );
			
            if(!empty($cstupiid))
                echo $cstupiid;

            // Testing (to be removed) - Empty value case
            else
                echo '';

            break;

        case 'cstcontact' :
            // Get custom post meta data
			 
            if(!empty($phone) && !empty($email))
            
				echo '
<a class="sqborder" href="mailto:'.$email.'">'.$email_wp_dashicon.'</a></strong>				
<a class="sqborder" href="tel:'.$phone.'">'.$phone_wp_dashicon.'</a></strong>
<a class="sqborder" href="sms:'.$phone.'">'.$sms_wp_dashicon.'</a></strong>
'.$wa_url.$wa_wp_dashicon.'</a></strong>
				';

            // Testing (to be removed) - Empty value case
            else
                echo '';

            break;
    }
}

//https://stackoverflow.com/questions/53865862/validate-and-save-additional-checkout-field-for-specific-payment-gateway-in-wooc

add_filter( 'woocommerce_gateway_description', 'gateway_bacs_appended_custom_text_fields', 10, 2 );
function gateway_bacs_appended_custom_text_fields( $description, $payment_id ){
$payment_gateway_data = WC()->payment_gateways->payment_gateways()['upipayment'];	
$mobcsthelp = $payment_gateway_data->get_option('mobcsthelp');
$wanumcsthelp = $payment_gateway_data->get_option('wanumcsthelp');
$emailcsthelp = $payment_gateway_data->get_option('emailcsthelp');

$phone_wp_dashicon = '<span class="dashicons dashicons-phone"></span> '.$mobcsthelp.'';			 
$sms_wp_dashicon = '<span class="dashicons dashicons-admin-comments"></span>';		 
$wa_wp_dashicon = '<span class="dashicons waicon"></span>';
$email_wp_dashicon = '<span class="dashicons dashicons-email-alt"></span> '.$emailcsthelp.'';

if( wp_is_mobile() ):
	$wanumcsthelp_url = '<a class="sqborder" href="https://wa.me/91'.$wanumcsthelp.'?text=I%20need%20help.">';
 else:
	$wanumcsthelp_url = '<a class="sqborder" href="https://web.whatsapp.com/send?phone=91'.$wanumcsthelp.'&text=I%20need%20help.&source&data" target="_blank">';
endif;

     if( $payment_id === 'upipayment' ){

        ob_start(); // Start buffering
				
        echo '<div class="upipayments-fields">				
		';

        woocommerce_form_field( 'upiid', array(
            //'type'          => 'text',
			'type'          => 'email',
            'label'         => __("<strong>Enter UPI ID that you paid through (for reference)</strong>", "woocommerce"),
            'class'         => array('form-row-wide'),
            'required'      => true,
			'placeholder'	=> 'e.g. abc@upi, etc',			
        ), '');

        echo '			
		<details>
		<summary>Need any help/support ? Contact us.</summary>
		<div style="margin:16px 0 16px 0;">';
		
		if($emailcsthelp):
			echo '<a class="sqborder" href="mailto:'.$emailcsthelp.'">'.$email_wp_dashicon.'</a></strong>';
		endif;
		if($mobcsthelp):
			echo '<a class="sqborder" href="tel:'.$mobcsthelp.'">'.$phone_wp_dashicon.'</a></strong>';
		endif;
		if($mobcsthelp):
			echo '<a class="sqborder" href="sms:'.$mobcsthelp.'">'.$sms_wp_dashicon.'</a></strong>';
		endif;
		if($wanumcsthelp):
			echo ''.$wanumcsthelp_url.$wa_wp_dashicon.'</a></strong>';
		endif;
		
		echo '</div>
		</details>
		<div style="margin:4px 0 4px 0;"><i>*We&apos;ll notify you about status of your payment & order shortly after you place the order</i></div>
		</div>
		';

        $description .= ob_get_clean(); // Append  buffered content
    }
    return $description;
}


// Process the field (validation)
add_action('woocommerce_checkout_process', 'upiid_checkout_field_validation');
function upiid_checkout_field_validation() {
if ( $_POST['payment_method'] === 'upipayment' && isset($_POST['upiid']) && empty($_POST['upiid']) )
    wc_add_notice( __( 'Please enter UPI ID that you paid through (for payment reference). It&apos;s a required field!' ), 'error' );
}

// Save "UPI ID" to the order as custom meta data
add_action('woocommerce_checkout_create_order', 'save_upiid_to_order_meta_data', 10, 4 );
function save_upiid_to_order_meta_data( $order, $data ) {
    if( $data['payment_method'] === 'upipayment' && isset( $_POST['upiid'] ) ) {
        $order->update_meta_data( '_upiid', sanitize_text_field( $_POST['upiid'] ) );
    }
}

//Custom fields End

//https://pippinsplugins.com/display-messages-in-the-dashboard-with-admin-notices/
//https://developer.wordpress.org/reference/hooks/admin_notices/
//https://codex.wordpress.org/Plugin_API/Action_Reference/activated_plugin
function pippin_admin_notices() {
//$order = new WC_Order( $order_id );
//$payment_method = $order->get_payment_method();
$payment_gateway_data = WC()->payment_gateways->payment_gateways()['upipayment'];	
if( $payment_gateway_data->get_option('enabled') == 'no' ):
ob_start(); 
?>
<div class="notice notice-success is-dismissible">
	<p>Thanks for installing <a href="https://noobs.group/upi-payment-plugin-woocommerce/" target="_blank">UPI Payment Gateway</a> plugin for WooCommerce. Now, It's time to enable & setup this gateway for use. from here: &rarr; <a href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=checkout&section=upipayment' ); ?>">UPI Payments (Setup)</a> &larr;</p>
</div>
<?php
echo ob_get_clean();
endif;
}
add_action('admin_notices', 'pippin_admin_notices');

//https://rudrastyh.com/wordpress/plugin_action_links-plugin_row_meta.html
add_filter( 'plugin_action_links', 'misha_settings_link', 10, 2 ); 
function misha_settings_link( $links_array, $plugin_file_name ){
	// $plugin_file_name is plugin-folder/plugin-name.php
 
	// if you use this action hook inside main plugin file, use basename(__FILE__) to check
	if( strpos( $plugin_file_name, basename(__FILE__) ) ) {
		// we can add one more array element at the beginning with array_unshift()
		array_unshift( $links_array, '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=upipayment' ). '">Settings</a>' );
	}
 
	return $links_array;
}

//https://docs.woocommerce.com/document/conditional-tags/
function upipayments_hook_css() {
?>
<?php if(is_checkout()): ?>
<style>
.upiqrcode{	
		background-repeat: no-repeat; background-size: cover; background-position: center;  width:100% !important; height:200px !important; max-width:200px !important; display:block !important; margin:auto !important; border: 1px solid lightgray !important;
		}
.upipayments-fields input[type="email"] {
	width: 96%;
	}
#payment .payment_methods li .payment_box {
	padding: 4px !important;
	}
#payment .payment_methods li img {
    max-height: 10px !important;
    margin-top: 10px !important;
    margin-bottom: 10px !important;
}	
.upipayments p:first-of-type{display:inline-block !important;}
.upipayments, .upipayments-fields{font-size:14px !important;text-align:center!important;}
.upipayments-fields details{font-style: italic !important;margin:16px 0 0 0!important;padding:0!important;}
.upipayments-fields details summary{margin:0 !important;padding:0!important;cursor:pointer!important;}	
#payment .place-order {
    margin-top: 4px !important;
}
a.sqborder {
    display: inline-block;
    border: 1px solid #0071a1;
    border-radius: 4px;
    padding: 2px 4px 2px 4px;
    background: #f3f5f6;
    margin-right: 2px;
	color:#0073aa !important;
	text-decoration: none !important;
}
	a.sqborder span.dashicons {
    margin-top: 2px;
    margin-bottom: 2px;
	}
/*https://iconify.design/icon-sets/fa/whatsapp.html*/
.waicon::before {
content: url(https://api.iconify.design/fa-whatsapp.svg?color=%230073aa&width=20&height=20);
}
</style>
<?php endif; ?>
<?php
}
add_action('wp_head', 'upipayments_hook_css');

function upipayments_hook_script() {
?>
<script>
//https://stackoverflow.com/questions/5781355/setting-an-attribute-named-required-and-any-value-with-jquery-doesnt-work
document.getElementById("upiid").required = true;

//https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input/email
//https://blog.revillweb.com/jquery-disable-button-disabling-and-enabling-buttons-with-jquery-5e3ffe669ece	

//document.getElementById("upiid").setAttribute("pattern", ".+@");

//document.getElementById("upiid").setAttribute("title", "Please enter some valid UPI ID or VPA, e.g. abc@upi, etc");	

//https://itnext.io/https-medium-com-joshstudley-form-field-validation-with-html-and-a-little-javascript-1bda6a4a4c8c

const emailField = document.getElementById('upiid');
const okButton = document.getElementById('place_order');
  
emailField.addEventListener('keyup', function (event) {
  isValidEmail = emailField.checkValidity();
  
  if ( isValidEmail ) {
    okButton.disabled = false;
  } else {
    okButton.disabled = true;
  }
});

</script>
<?php
}
add_action('wp_footer', 'upipayments_hook_script');

add_action('plugins_loaded', 'woocommerce_gateway_upipayment_init', 0);
function woocommerce_gateway_upipayment_init() {
	if ( !class_exists( 'WC_Payment_Gateway' ) ) return;
	/**
 	 * Localisation
	 */
	load_plugin_textdomain('wc-gateway-upipayment', false, dirname( plugin_basename( __FILE__ ) ) . '/languages');

include_once( plugin_dir_path( __FILE__ ) . 'helper-functions/my-account-pay-later-link-button.php' );

/**
 * UPI Payment Gateway.
 *
 * Provides a UPI Payment Gateway, mainly for testing purposes.
 *
 * @class       WC_Gateway_UPIPayment
 * @extends     WC_Payment_Gateway
 * @version     2.1.0
 * @package     WooCommerce/Classes/Payment
 */
class WC_Gateway_UPIPayment extends WC_Payment_Gateway {

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		$this->id                 = 'upipayment';
		$this->icon               = apply_filters( 'woocommerce_upipayment_icon', plugins_url( '/upipg-icon.svg' , __FILE__ ) );
		//$this->has_fields         = false;
		//Because I want custom fields for this Gateway
		$this->has_fields         = true;
		$this->method_title       = _x( 'UPI Payments', 'UPI Payment method', 'woocommerce' );
		$this->method_description = __( 'Collect payments through UPI applications. e.g. BHIM app, Google pay, Paytm, Phonepe, Whatsapp, etc', 'woocommerce' );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables.
		//in admin you can not do this
		//Better here before call
		
		$this->anyname        = $this->get_option( 'anyname' );		
		$this->vpa        	  = $this->get_option( 'vpa' );
		$this->mobcsthelp     = $this->get_option( 'mobcsthelp' );
		$this->wanumcsthelp        = $this->get_option( 'wanumcsthelp' );
		$this->emailcsthelp        = $this->get_option( 'emailcsthelp' );
		
		if(!is_admin()):
		//https://stackoverflow.com/questions/22249615/get-woocommerce-carts-total-amount
		global $woocommerce;

		$totalamount = $woocommerce->cart->cart_contents_total+$woocommerce->cart->tax_total;
		$order_total = wp_kses_data( WC()->cart->get_total() );
		$bhim_upi_pay_url = 'upi://pay?pn=' . ucwords($this->anyname) . '&pa=' . lcfirst($this->vpa) . '&tn=UPIPayment&am=' . $totalamount . '&cu=INR';
		$upiqrcode = 'https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=' . urlencode($bhim_upi_pay_url) . '&choe=UTF-8&chld=L|0';
		$confirmation_input = '
		<style>
		.upiqrcode{ background-image: url("'.$upiqrcode.'"); }
		</style>
		<div style="margin:4px 0 4px 0 !important">&bull;</div>
	
		';	
		
		endif;
		
		$this->title        = $this->get_option( 'title' );
		
		if(!wp_is_mobile()):			
			$this->description  = '<div class="upipayments" align="center"><p>'.$this->get_option( 'pcdescription' ).'</p><div class="upiqrcode" style="margin-top:8px !important;"></div>'.$confirmation_input.'</div>';
		else:
			$this->description  = '<div class="upipayments" align="center"><p>'.$this->get_option( 'mobdescription' ).'</p><a href="#" onclick="window.location=&apos;'.$bhim_upi_pay_url.'&apos;;return false;"><div class="upiqrcode" style="margin-top:8px !important;"></div></a>'.$confirmation_input.'</div>';
		endif;		
		
		$this->instructions = $this->get_option( 'instructions' );

		// Actions.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_upipayment', array( $this, 'thankyou_page' ) );

		// Customer Emails.
		add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {

		$this->form_fields = array(
			'enabled'      => array(
				'title'   => __( 'Enable/Disable', 'woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable UPI Payments', 'woocommerce' ),
				'default' => 'no',
			),
			'title'        => array(
				'title'       => __( 'Title', 'woocommerce' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
				'default'     => _x( 'UPI Payments', 'UPI Payment method', 'woocommerce' ),
				'desc_tip'    => true,
			),
			'pcdescription'  => array(
				'title'       => __( 'Description (for PC)', 'woocommerce' ),
				'type'        => 'textarea',
				'description' => __( 'Payment method description that the customer will see on your checkout.', 'woocommerce' ),
				'default'     => __( '<strong>Scan</strong> below QR code & Pay using any UPI application installed on your mobile. e.g. BHIM app, Google pay, Paytm, Phonepe, Whatsapp, etc', 'woocommerce' ),
				'desc_tip'    => true,
			),
			'mobdescription'  => array(
				'title'       => __( 'Description (for Mobile devices)', 'woocommerce' ),
				'type'        => 'textarea',
				'description' => __( 'Payment method description that the customer will see on your checkout.', 'woocommerce' ),
				'default'     => __( '<strong>Click on</strong>/<strong>Scan</strong> below QR code & Pay using any UPI application installed on your mobile. e.g. BHIM app, Google pay, Paytm, Phonepe, Whatsapp, etc', 'woocommerce' ),
				'desc_tip'    => true,
			),
			
			'instructions' => array(
				'title'       => __( 'Instructions', 'woocommerce' ),
				'type'        => 'textarea',
				'description' => __( 'Instructions that will be added to the thank you page and emails.', 'woocommerce' ),
				'default'     => '',
				'desc_tip'    => true,
			),
			
			'anyname'        => array(
				'title'       => __( 'Store name OR Your name<span style="color:red;">*</span>', 'woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Enter Store name OR Your name here.', 'woocommerce' ),
				'default'     => '',
				'desc_tip'    => true,
			//	'disabled'    => true,
				'custom_attributes' => array( 'required'    => '', ),
			),			
			
			'vpa'        => array(
				'title'       => __( 'UPI ID (VPA)<span style="color:red;">*</span>', 'woocommerce' ),
				'type'        => 'email',
				'description' => __( 'Enter your UPI ID or VPA here, e.g. siradhana@upi.', 'woocommerce' ),
				'default'     => '',
				'desc_tip'    => true,
			//	'disabled'    => true,
				'custom_attributes' => array( 'required'    => '', ),
			),
			
			'mobcsthelp'        => array(
				'title'       => __( 'Mobile/Telephone number (for Customer Support)<span style="color:red;">*</span>', 'woocommerce' ),
				'type'        => 'tel',
				'description' => __( 'Mobile/Telephone number for Customer Support.', 'woocommerce' ),
				'default'     => '',
				'desc_tip'    => true,
			//	'disabled'    => true,
				'custom_attributes' => array( 'required'    => '', ),
			),
			
			'wanumcsthelp'        => array(
				'title'       => __( '<span style="color:red;">(Optional)</span> WhatsApp No. (for Customer Support)', 'woocommerce' ),
				'type'        => 'tel',
				'description' => __( 'Enter WhatsApp No. for Customer Support (Optional)', 'woocommerce' ),
				'default'     => '',
				'desc_tip'    => true,
			//	'disabled'    => true,
			//	'custom_attributes' => array( 'required'    => '', ),
			),

			'emailcsthelp'        => array(
				'title'       => __( '<span style="color:red;">(Optional)</span> Email ID (for Customer Support)', 'woocommerce' ),
				'type'        => 'email',
				'description' => __( 'Enter Email ID for Customer Support.', 'woocommerce' ),
				'default'     => '',
				'desc_tip'    => true,
			//	'disabled'    => true,
			//	'custom_attributes' => array( 'required'    => '', ),
			),

		);
	}

	/**
	 * Output for the order received page.
	 */
	public function thankyou_page() {
		if ( $this->instructions ) {
			echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) );
		}
	}

	/**
	 * Add content to the WC emails.
	 *
	 * @access public
	 * @param WC_Order $order Order object.
	 * @param bool     $sent_to_admin Sent to admin.
	 * @param bool     $plain_text Email format: plain text or HTML.
	 */
	public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		if ( $this->instructions && ! $sent_to_admin && 'upipayment' === $order->get_payment_method() && $order->has_status( 'on-hold' ) ) {
			echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) . PHP_EOL );
		}
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 */
	public function process_payment( $order_id ) {

		$order = wc_get_order( $order_id );

		if ( $order->get_total() > 0 ) {
			// Mark as on-hold (we're awaiting the upi payment).
			$order->update_status( apply_filters( 'woocommerce_upipayment_process_payment_order_status', 'on-hold', $order ), _x( 'Awaiting UPI Payment', 'UPI Payment method', 'woocommerce' ) );
		} else {
			$order->payment_complete();
		}

		// Remove cart.
		WC()->cart->empty_cart();

		// Return thankyou redirect.
		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);
	}
}

	/**
 	* Add the Gateway to WooCommerce
 	**/
	function woocommerce_add_gateway_upipayment_gateway($methods) {
		$methods[] = 'WC_Gateway_UPIPayment';
		return $methods;
	}
	
	add_filter('woocommerce_payment_gateways', 'woocommerce_add_gateway_upipayment_gateway' );

} // woocommerce_gateway_upipayment_init CLOSED
