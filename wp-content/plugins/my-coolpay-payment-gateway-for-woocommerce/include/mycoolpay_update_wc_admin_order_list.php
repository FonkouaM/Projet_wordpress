<?php
/**
 * Adding a new column in woocommerce admin order list
 * 
 * @param array $columns
 * @return array $new_columns
 *
 */

add_filter( 'manage_edit-shop_order_columns', 'mycoolpay_add_order_list_new_column', 20);

function mycoolpay_add_order_list_new_column( $columns ) {

    $new_columns = array();

    foreach ( $columns as $column_name => $column_info ) {

        $new_columns[ $column_name ] = $column_info;

        if ( 'order_status' === $column_name ) {
            $new_columns['order_key'] = __( 'transaction reference', 'my-textdomain' );
        }
    }
    return $new_columns;
}

/**
 * Adding the content of the column added previously in the order list table
 * 
 * @param array $column
 * @return void
 */

add_action( 'manage_shop_order_posts_custom_column', 'mycoolpay_add_order_list_column_content' );

function mycoolpay_add_order_list_column_content( $column ) {

    global $post;

    if ( 'order_key' === $column ) {
        $order = wc_get_order( $post->ID );
        echo '<p>' . $order->get_order_key() . '</p>';
    }
}

/**
 * Make the order_key searchable in the admin order list
 * add order_key to woocommerce search fields
 * 
 * @param array $meta_keys
 * @return $meta_keys
 */

add_filter( 'woocommerce_shop_order_search_fields', 'mycoolpay_search_order_by_order_key', 10, 1 );
 
function mycoolpay_search_order_by_order_key ( $meta_keys ){
    $meta_keys[] = '_order_key';
    return $meta_keys;
}


