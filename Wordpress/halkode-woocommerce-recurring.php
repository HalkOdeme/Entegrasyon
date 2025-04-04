<?php

if (!defined('ABSPATH')) {
    exit;
}

add_filter( 'product_type_options', 'add_new_recurring_product_type' );



function add_new_recurring_product_type( $types ){



    $types['recurring'] = array(

        'id'            => '_recurring',

        'wrapper_class' => 'show_if_simple',

        'label'         => __( 'Recurring', 'woocommerce' ),

        'description'   => __( 'Product will be set as recurring', 'woocommerce' ),

        'default'       => 'no',

    );



    return $types;

}



// function add_custom_class_script_for_recurring() {



//    

//         jQuery( document ).ready( function( $ ) {



//             $( 'input#_recurring' ).change( function() {

//                 var is_gift_card = $( 'input#_recurring:checked' ).size();



//                 $( '.show_if_recurring' ).hide();

//                 $( '.show_if_recurring' ).hide();



//                 if ( is_gift_card ) {

//                     $( '.hide_if_recurring' ).hide();

//                 }

//                 if ( is_gift_card ) {

//                     $( '.show_if_recurring' ).show();

//                 }

//             });

//             $( 'input#_recurring' ).trigger( 'change' );

//         });

//     </script><?php



// }

// add_action( 'admin_head', 'add_custom_class_script_for_recurring' );



add_action("save_post_product", "save_recurring_checkbox_value", 10, 3);

function save_recurring_checkbox_value($post_ID, $product, $update) {



    update_post_meta($product->ID, "_recurring", isset($_POST["_recurring"]) ? "yes" : "no");



}



add_filter('woocommerce_product_data_tabs', 'halkode_recurring_product_settings_tabs' );

function halkode_recurring_product_settings_tabs( $tabs ){





    $tabs['halkode_recurring'] = array(

        'label'    => 'Recurring',

        'target'   => 'halkode_recurring_product_data',

        'class'    => array('show_if_recurring'),

        //'priority' => 21,

    );

    return $tabs;



}



add_action( 'woocommerce_product_data_panels', 'halkode_recurring_product_panels' );

function halkode_recurring_product_panels(){



    echo '<div id="halkode_recurring_product_data" class="panel woocommerce_options_panel hidden">';



    woocommerce_wp_text_input( array(

        'id'                => 'payment_duration',

        'value'             => get_post_meta( get_the_ID(), 'payment_duration', true ),

        'label'             => 'No of Payments',

        'description'       => ''

    ) );



    woocommerce_wp_select( array(

        'id'          => 'payment_cycle',

        'value'       => get_post_meta( get_the_ID(), 'payment_cycle', true ),

        //'wrapper_class' => 'show_if_downloadable',

        'label'       => 'Order Frequency Cycle',

        'options'     => array( '' => 'Please select', 'D' => 'Daily', 'M' => 'Monthly', 'Y' => 'Yearly'),

    ) );



    woocommerce_wp_text_input( array(

        'id'                => 'payment_interval',

        'value'             => get_post_meta( get_the_ID(), 'payment_interval', true ),

        'label'             => 'Order Frequency Interval',

        'description'       => ''

    ) );



    echo '</div>';



}



add_action( 'woocommerce_process_product_meta', 'halkode_recurring_save_fields', 10, 2 );

function halkode_recurring_save_fields( $id, $post ){

    update_post_meta( $id, 'payment_duration', $_POST['payment_duration'] );

    update_post_meta( $id, 'payment_cycle', $_POST['payment_cycle'] );

    update_post_meta( $id, 'payment_interval', $_POST['payment_interval'] );

}



function remove_all_cart_item_if_recurring_product_add( $valid, $product_id, $quantity ) {

    $is_recurring = get_post_meta($product_id, "_recurring", true);

    if( $is_recurring == 'yes'){

        if(!WC()->cart->is_empty()){

            WC()->cart->empty_cart();

            wc_add_notice( "You cannot have another item in your cart for recurring payments.", 'error' );

        }

    }else{

        if( ! empty ( WC()->cart->get_cart() )){

            foreach( WC()->cart->get_cart() as $cart_item ) {

                $cart_product_id = $cart_item['product_id'];

                $is_recurring_cart = get_post_meta($cart_product_id, "_recurring", true);

                if( $is_recurring_cart == 'yes'){

                    //WC()->cart->empty_cart();

                    wc_add_notice( "You cannot have another item in your cart for recurring payments.", 'error' );

                    return false;

                }

            }

        }

    }







    return $valid;



}

add_filter( 'woocommerce_add_to_cart_validation', 'remove_all_cart_item_if_recurring_product_add', 10, 3 );



function halkode_recurring_product_sold_individually( $individually, $product ){

    $is_recurring = get_post_meta($product->id, "_recurring", true);



    if($is_recurring == 'yes')

        return true;

    return $individually;

}

add_filter( 'woocommerce_is_sold_individually', 'halkode_recurring_product_sold_individually', 10, 2 );



//add_action('woocommerce_before_add_to_cart_quantity', 'woocommerce_before_add_to_cart_quantity_halkode');

function woocommerce_before_add_to_cart_quantity_halkode(){

    global $product;

    $payment_duration =get_post_meta( $product->get_id(), 'payment_duration', true );

    $payment_cycle= get_post_meta( $product->get_id(), 'payment_cycle', true);

    $payment_interval = get_post_meta( $product->get_id(), 'payment_interval', true );



    if(empty($payment_duration) || empty($payment_cycle) || empty($payment_interval))

        return;



    if($payment_cycle == "D")

        $payment_cycle ="Daily";

    elseif($payment_cycle == "M")

        $payment_cycle ="Monthly";

    elseif($payment_cycle == "Y")

        $payment_cycle ="Yearly";



    echo "<p>Payment Duration: ".$payment_duration."</p>";

    echo "<p>Payment Cycle: ".$payment_cycle."</p>";

    echo "<p>Payment Interval: ".$payment_interval."</p>";

}