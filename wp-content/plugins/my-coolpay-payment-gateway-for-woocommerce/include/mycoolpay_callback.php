<?php

add_action('rest_api_init', 'mycoolpay_callback_url');

/**
 * Use to define the callback url route 
 * and to call the appropriate function
 * @return void
 */

function mycoolpay_callback_url(){

    $mycoolpay_gateway = new Mycoolpay_Woocommerce_Gateway();
    //get the end of the callback url choosen by the admin
    $end_of_callback_url = explode('callback/', $mycoolpay_gateway->get_callback_url());
  
    // defines the route
    register_rest_route( 'callback', $end_of_callback_url[1] ,array(
        'methods'  => 'POST',
        'callback' => 'mycoolpay_update_order_status',
        'permission_callback' => '__return_true'
    ));
}


/**
 * Change a order status after the payment has been performed 
 * 
 * @param $request
 * @return array
 */

function mycoolpay_update_order_status($request) {
   
   // checks the client ip address
    if($_SERVER['REMOTE_ADDR'] === Mycoolpay_Woocommerce_Gateway::MYCOOLPAY_SERVER_ADDRESS){

        $parameters = $request->get_body();
        $data= '';
        //get the data according to the content-type of the request 
        if($_SERVER["CONTENT_TYPE"] === 'application/json'){
            $data = json_decode($parameters, true);
        }else{
            parse_str($parameters, $data);
        }

        //add log data
        mycoolpay_add_log_data($data);

        $mycoolpay_gateway = new Mycoolpay_Woocommerce_Gateway();
        $private_key = $mycoolpay_gateway->get_private_key();
        $public_key = $mycoolpay_gateway->get_public_key();

        //check if it is the right signature
        $signature = mycoolpay_check_signature($data, $private_key);
        
        //add log data
        mycoolpay_add_log_data($signature);
        
        if ($signature === true){
           
            //gets a order by the order key
            $order_id = wc_get_order_id_by_order_key( $data["app_transaction_ref"]);

            if($order_id){
                $order = wc_get_order($order_id);
                if($order){
                    
                    if( $data["transaction_status"] === 'SUCCESS'){
                        // Payment complete
                        $order->update_status( 'completed', __( 'successful payment', 
                        Mycoolpay_Woocommerce_Gateway::MYCOOLPAY_GATEWAY_ID) );
                        
                        $result = [
                            "status" => "success",
                            "message" => "Order updated",
                            "transaction_status" => "success"

                        ];
                        
                    }else if($data["transaction_status"] === 'CANCELED'){
                        
                        $order->update_status( 'cancelled', 
                        __( 'payment cancelled', Mycoolpay_Woocommerce_Gateway::MYCOOLPAY_GATEWAY_ID) );
    
                        $result = [
                            "status" => "success",
                            "message" => "Order updated",
                            "transaction_status" => "cancelled"
                        ];
    
                    }else if($data["transaction_status"] === 'FAILED'){
                        
                        $order->update_status( 'failed', 
                        __( 'failed payment', Mycoolpay_Woocommerce_Gateway::MYCOOLPAY_GATEWAY_ID) );
                        
                        $result = [
                            "status" => "success",
                            "message" => "Order updated",
                            "transaction_status" => "failed"
                        ];
                    }

                }else{
                    $result = [
                        "status" => "error",
                        "message" => "Order not found"
                    ];
                }

            }else{
                
                $result = [
                    "status" => "error",
                    "message" => "Order not found"
                ];    
            }

        }else{
            $result = [
                "status" => "error",
                "message" => "Invalid signature"
            ];
        }

    }else{
        $result = [
            "status" => "error",
            "message" => "unauthorized server"
        ];
    }

    $response= new WP_REST_Response($result);
    $response->set_status(200);
    return $response;
}

/**
 * check the signature of the request
 *
 * @param array $data
 * @param string $private_key
 * @return boolean
 */
function mycoolpay_check_signature($data, $private_key){
    $signature_received = $data["signature"];
    
    $new_signature = $data["transaction_ref"].$data["transaction_type"].$data["transaction_amount"].
                     $data["transaction_currency"].$data["transaction_operator"].$private_key;
    $new_md5_signature = md5($new_signature);

    if($signature_received === $new_md5_signature){
        return true;
    }else{
        return false;
    }
}


/**
 * add data received to the log file
 *
 * @param array $data
 * @return void
 */
function mycoolpay_add_log_data($data){
    // add data in the wordpress log file 
    if (!function_exists('write_log')) {
        function write_log ( $log )  {
            if ( true === WP_DEBUG ) {
                if ( is_array( $log ) || is_object( $log ) ) {
                    error_log( print_r( $log, true ) );
                } else {
                    error_log( $log );
                }
            }
        }
    }
    write_log($data);
}