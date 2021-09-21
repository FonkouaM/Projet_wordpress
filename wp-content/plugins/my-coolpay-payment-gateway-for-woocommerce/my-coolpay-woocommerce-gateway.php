<?php
/**
 * Plugin Name:       My-CoolPay - Payment gateway for WooCommerce 
 * Plugin URI:        https://www.my-coolpay.com/
 * Description:       My-CoolPay - Payment gateway for WooCommerce is a modern plugin that allows you to sell anywhere your customers are. Give yours customers the gift of mordern payment solution and let them pay you however they want by: Orange Money, MTN Mobile Money, VISA, MASTER CARD and My-CoolPay wallet.
 * Version:           1.1
 * Author:            Digital House International
 * Author URI:        https://digitalhouse-int.com/
 * License:           GPL v2
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       my-coolpay
 */

/**
 * Try to prevent direct access data leaks
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Check if WooCommerce is present and active
 **/
if ( ! in_array( 'woocommerce/woocommerce.php', 
      apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    return;
}

add_filter( 'woocommerce_payment_gateways', 'mycoolpay_add_to_woocommerce' );
/**
 * Add the gateway to WooCommerce Available Gateways
 * 
 * @param array $gateways all available WooCommerce gateways
 * @return array $gateways all WooCommerce gateways + my-coolpay gateway
 */
function mycoolpay_add_to_woocommerce ( $gateways ) {
    $gateways[] = 'Mycoolpay_Woocommerce_Gateway';
    return $gateways;
}

add_action( 'plugins_loaded', 'mycoolpay_gateway_init', 0);

/**
 * Method call when the plugin load
 * @return void 
 */

function mycoolpay_gateway_init() {

    class Mycoolpay_Woocommerce_Gateway extends WC_Payment_Gateway {

        const MYCOOLPAY_GATEWAY_ID = 'mycoolpay';
        const MYCOOLPAY_API_BASE_URL = 'https://my-coolpay.com/api';
        const MYCOOLPAY_API_PAYIN_URL = 'paylink';
        const MYCOOLPAY_TRANSACTION_REASON =  'Payment for my items';
        const MYCOOLPAY_EUR_VALUE = 650;
        const MYCOOLPAY_USD_VALUE = 550; 
        const MYCOOLPAY_MESSAGE_ERROR = 'An error has occured. Please try again later';
        const MYCOOLPAY_SERVER_ADDRESS = '15.236.140.89';
        const MYCOOLPAY_API_CALLBACK_URL = 'checkStatus';
    
        /**
         * Constructor for the gateway.
         */
        public function __construct()
        {
            // The global ID for this Payment method
            $this->id = Mycoolpay_Woocommerce_Gateway::MYCOOLPAY_GATEWAY_ID;
 
            //check if My-CoolPay icon image exists or not
            $image_url = WP_PLUGIN_URL.'/'. plugin_basename(dirname(__FILE__)).'/images/my_coolpay_operators.png';
            if (@getimagesize($image_url)) {
                //Show an image on the frontend
                $this->icon = $image_url;
            } else {
                //Show the name on the frontend
                $this->title = "My-CoolPay";
            }

            //button text used to perform the payment
            $this->order_button_text = __('Pay with My-CoolPay', Mycoolpay_Woocommerce_Gateway::MYCOOLPAY_GATEWAY_ID);

            // Boolean. Can be set to true if you want payment fields to show on the checkout
            $this->has_fields = false;

            // The title of the payment method for the admin page
            $this->method_title =  __( 'My-CoolPay - Payment gateway for WooCommerce',Mycoolpay_Woocommerce_Gateway::MYCOOLPAY_GATEWAY_ID);

            // The description for this Payment Gateway, shown on the actual Payment options page on the backend
            $this->method_description =  __( 'My-CoolPay - Payment gateway for WooCommerce is a modern plugin that allows you to sell anywhere your customers are.
                                              Give yours customers the gift of mordern payment solution and let them pay you however they want by : 
                                              Orange Money, MTN Mobile Money, VISA, MASTER CARD and My-CoolPay wallet.', Mycoolpay_Woocommerce_Gateway::MYCOOLPAY_GATEWAY_ID );

            // This basically defines your settings which are then loaded with init_settings()
            $this->init_form_fields();

            // After init_settings() is called, you can get the settings and load them into variables
            $this->init_settings();

            // Define user set variables
            $this->description  = $this->get_option( 'description' );
            $this->public_key   = $this->get_option( 'public_key' );
            $this->private_key  = $this->get_option( 'private_key' );
            $this->callback_url = $this->get_option( 'callback_url' );

            // Used to perfom plugin information updated by the admin
            add_action( 'woocommerce_update_options_payment_gateways_' . 
                        $this->id, array( $this, 'process_admin_options' ) );

        }

        /**
         * Generate a callback url only one time
         * 
         * @return void
         */
        public function generate_callback_url(){
            
            $old_callback_url_value = $this->get_option( 'callback_url' );

            if($old_callback_url_value === ''){
                $genarate_callback_url = md5(uniqid().mt_rand());
                $site_address = get_home_url();
                $this->update_option( 'callback_url', $site_address.'/wp-json/callback/'.$genarate_callback_url);    
            }
        }

        /**
         * Initialize Gateway Settings Form Fields
         * 
         * @return void
         */
        public function init_form_fields() {

            //generate a callback url
            $this->generate_callback_url();

            $this->form_fields = apply_filters( 'mycoolpay_form_fields', array(

                'enabled' => array(
                    'title'   => __( 'Enable/Disable', Mycoolpay_Woocommerce_Gateway::MYCOOLPAY_GATEWAY_ID),
                    'type'    => 'checkbox',
                    'label'   => __( 'Enable My-Coolpay', Mycoolpay_Woocommerce_Gateway::MYCOOLPAY_GATEWAY_ID),
                    'description' => __('Check if you want to activate this plugin or uncheck if you want to disable
                                        this payment plugin ', Mycoolpay_Woocommerce_Gateway::MYCOOLPAY_GATEWAY_ID),
                    'default' => 'yes',
                    'desc_tip'    => true,
                ),

                'description' => array(
                    'title'       => __( 'Description', Mycoolpay_Woocommerce_Gateway::MYCOOLPAY_GATEWAY_ID),
                    'type'        => 'textarea',
                    'description' => __( 'Payment method description that the customer will see on your checkout.',
                                        Mycoolpay_Woocommerce_Gateway::MYCOOLPAY_GATEWAY_ID),
                    'default'     => __( 'Pay safely using Orange money, Orange Money, MTN MoMo, VISA, MASTER CARD, My-CoolPay wallet', 
                                        Mycoolpay_Woocommerce_Gateway::MYCOOLPAY_GATEWAY_ID),
                    'desc_tip'    => true,
                    'custom_attributes' => array('readonly' => 'readonly'),
                ),

                'callback_url' => array(
                    'title'       => __( 'Callback url', Mycoolpay_Woocommerce_Gateway::MYCOOLPAY_GATEWAY_ID),
                    'type'        => 'textarea',
                    'description' => __( 'Copy this callback url and paste it in your My-CoolPay merchant account',
                                        Mycoolpay_Woocommerce_Gateway::MYCOOLPAY_GATEWAY_ID),
                    'default'     => __( '', Mycoolpay_Woocommerce_Gateway::MYCOOLPAY_GATEWAY_ID),
                    'desc_tip'    => true,
                    'custom_attributes' => array('readonly' => 'readonly'),
                ),

                'public_key' => array(
                    'title'       => __( 'Public key', Mycoolpay_Woocommerce_Gateway::MYCOOLPAY_GATEWAY_ID),
                    'type'        => 'text',
                    'description' => __( 'This is the public key of your merchant service on My-CoolPay', 
                                        Mycoolpay_Woocommerce_Gateway::MYCOOLPAY_GATEWAY_ID),
                    'default'     => '',
                    'desc_tip'    => true,
                ),

                'private_key' => array(
                    'title'       => __( 'Private key', Mycoolpay_Woocommerce_Gateway::MYCOOLPAY_GATEWAY_ID),
                    'type'        => 'textarea',
                    'description' => __( 'This is the service private key for your My-CoolPay service.',
                                        Mycoolpay_Woocommerce_Gateway::MYCOOLPAY_GATEWAY_ID),
                    'default'     => '',
                    'desc_tip'    => true,
                ),
                
            ));
        }
     
        /**
         * plublic key getter
         * 
         * @return void
         */
        public function get_private_key(){
            return $this->private_key;
        }

        /**
         * private key getter
         * 
         * @return void
         */
        public function get_public_key(){
            return $this->public_key;
        }

        /**
         * callback url getter
         * 
         * @return void
         */
        public function get_callback_url(){
            return $this->callback_url;
        }
        

        /**
         * Convert order amount to XAF
         *
         * @param float $amount
         * @return int
         */
        public function convert_amount_to_fcfa($amount){
            
            $new_amount = 0;
            // Check  the woocommerce currency activated
            if(get_woocommerce_currency() ==='XAF' || get_woocommerce_currency() ==='XOF'){
                //Round up to the next number
                $new_amount = ceil($amount);
            }
            elseif (get_woocommerce_currency() ==='EUR'){
                $new_amount = ceil($amount * Mycoolpay_Woocommerce_Gateway::MYCOOLPAY_EUR_VALUE) ;
            }
            elseif (get_woocommerce_currency() ==='USD'){
                $new_amount = ceil($amount * Mycoolpay_Woocommerce_Gateway::MYCOOLPAY_USD_VALUE) ;
            }else{
                $new_amount = null;
            }
            return  $new_amount;
        }

         /**
         * Get the website language
         * 
         * @return string
         *
         */
        public function get_website_language(){
            // get the website language
             $lang = get_locale();
         
             if($lang === 'fr_FR' ){
                return 'fr';
             }else{
                return 'en';
             }
         }

        /**
         * This function handles the processing of the order, telling WooCommerce 
         * what status the order should have and where customers go after it’s used.
         * 
         * @param int $order_id
         * @return array
         *
         */
        public function process_payment( $order_id ) {
            
            $order = wc_get_order( $order_id );

            // get the order item's name
            foreach ($order->get_items() as $item_key => $item ){
                $order_items_name = $order_items_name.", ".$item->get_name();
            }
            //remove the comma  at the beginning of a string
            $order_items_name = preg_replace('/^,/', '', $order_items_name);
           
            //convert and get the total amount in fcfa
            $total_amount = $this->convert_amount_to_fcfa($order->order_total);  
           
            if($total_amount === null){
                wc_add_notice( __('Payment error : ', 'woothemes') . 
                                  "THIS CURRENCY IS NOT ALLOWED ON MY-COOLPAY", 'error' );
                return;
            }

            // get the website language
            $lang = $this->get_website_language();

            //building the url used to get the payment link
            $url = Mycoolpay_Woocommerce_Gateway::MYCOOLPAY_API_BASE_URL.'/'.
                   $this->public_key.'/'.Mycoolpay_Woocommerce_Gateway::MYCOOLPAY_API_PAYIN_URL;
            
            $request_parameters= [
                "transaction_amount"=> $total_amount,
                "transaction_reason"=> substr($order_items_name, 0, 40),
                "app_transaction_ref"=> $order->order_key,
                "customer_name"=>$order->billing_last_name.' '.$order->billing_first_name,
                "customer_email"=> $order->billing_email,
                "customer_lang"=>  $lang
            ];

            // getting the payment link
            $response =  $this->get_payment_link($order, $url, $request_parameters);
            return $response;
        }


         /**
         * Used to get the payment link from my-coolpay
         * 
         * @param array $order
         * @param string $url
         * @param string $request_parameters
         * @return array
         *
         */
        public function get_payment_link($order, $url, $request_parameters){

            global $woocommerce;

            $request = wp_remote_post($url, array(
                'body' => $request_parameters
            ));

            $body = wp_remote_retrieve_body($request);

            $result = json_decode($body, true);

            if(isset($result["status"])){
                if($result["status"] === "success"){
                    if (isset($result["payment_url"])){
                        
                        $order->update_status( 'on-hold',
                         __( 'Awaiting confirmation of payment', Mycoolpay_Woocommerce_Gateway::MYCOOLPAY_GATEWAY_ID) );

                        // Remove cart
                        $woocommerce->cart->empty_cart();

                        $response = [
                            'result'    => 'success',
                            'redirect'  => $result['payment_url'] 
                        ];

                       return $response;
                    }
                }
                else if ($result["status"] === "error"){
                    if (isset($result["message"])){
                        wc_add_notice( __('Payment error : ', 'woothemes') . $result["message"], 'error' );
                        return;
                    }
                }
            }else{
                wc_add_notice( __('Payment error : ', 'woothemes'). Mycoolpay_Woocommerce_Gateway::MYCOOLPAY_MESSAGE_ERROR, 'error' );
                return;
            }
        }

    }

    
    /* ============================================ INCLUDE OTHER FILE =========================================== */

    //including the callback 
    include 'include/mycoolpay_callback.php';
    // including the order_key in wc admin order list
    include 'include/mycoolpay_update_wc_admin_order_list.php';

    /*=========================================================================================================== */
    
}


