<?php
/*
Plugin Name: Change Quantity on Checkout
Author: Amen Khaled
Author URI: https://www.linkedin.com/in/amen-khaled-b9a706131/
Version: 1.0.1
Description: Allows customers to change product quantity on the WooCommerce checkout page.
*/

// ----------------------------
// Add Quantity Input Beside Product Name

add_filter( 'woocommerce_checkout_cart_item_quantity', 'bbloomer_checkout_item_quantity_input', 9999, 3 );

function bbloomer_checkout_item_quantity_input( $product_quantity, $cart_item, $cart_item_key ) {
    $product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
    $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
    if ( ! $product->is_sold_individually() ) {
        $product_quantity = woocommerce_quantity_input( array(
            'input_name'  => 'shipping_method_qty_' . $product_id,
            'input_value' => $cart_item['quantity'],
            'max_value'   => $product->get_max_purchase_quantity(),
            'min_value'   => '0',
        ), $product, false );
        $product_quantity .= '<input type="hidden" name="product_key_' . $product_id . '" value="' . $cart_item_key . '">';
    }
    return $product_quantity;
}

// ----------------------------
// Detect Quantity Change and Recalculate Totals

add_action( 'woocommerce_checkout_update_order_review', 'bbloomer_update_item_quantity_checkout' );

function bbloomer_update_item_quantity_checkout( $post_data ) {
    parse_str( $post_data, $post_data_array );
    $updated_qty = false;
    foreach ( $post_data_array as $key => $value ) {
        if ( substr( $key, 0, 20 ) === 'shipping_method_qty_' ) {
            $id = substr( $key, 20 );
            WC()->cart->set_quantity( $post_data_array['product_key_' . $id], $post_data_array[$key], false );
            $updated_qty = true;
        }
    }
    if ( $updated_qty ) WC()->cart->calculate_totals();
}