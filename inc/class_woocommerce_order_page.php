<?php

/**
 * Woocomerce Order add Meta
 */
class cs_woocommerce_order_meta{
    function __construct(){
        // Adding Meta container admin shop_order pages
        add_action( 'add_meta_boxes', array($this , 'mv_add_meta_boxes') );
        // Save the data of the Meta field
        add_action( 'save_post', array($this , 'mv_save_wc_order_other_fields'), 10, 1 );
    }

    function mv_add_meta_boxes(){
        add_meta_box( 'mv_other_fields', __('Power Body Response','woocommerce'), array($this, 'mv_add_other_fields_for_packaging'), 'shop_order', 'normal', 'core' );
    }

    function mv_add_other_fields_for_packaging(){
        global $post;
        echo '<input type="hidden" name="mv_other_meta_field_nonce" value="' . wp_create_nonce() . '">';
        require CHI_PATH.'/templates/woocomerce_meta_box.php';

    }

    function mv_save_wc_order_other_fields( $post_id ) {
        // Check if our nonce is set.
        if ( ! isset( $_POST[ 'mv_other_meta_field_nonce' ] ) ) {
            return $post_id;
        }
        $nonce = $_REQUEST[ 'mv_other_meta_field_nonce' ];
        //Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $nonce ) ) {
            return $post_id;
        }
        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }
        // Check the user's permissions.
        if ( 'page' == $_POST[ 'post_type' ] ) {
            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return $post_id;
            }
        } else {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return $post_id;
            }
        }
        update_post_meta( $post_id, '_my_field_slug', $_POST[ 'my_field_name' ] );
    }

}
new cs_woocommerce_order_meta();




