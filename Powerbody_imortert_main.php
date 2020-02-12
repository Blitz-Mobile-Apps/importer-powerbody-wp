<?php
/**
* @package w_a_p_l
* @version 1.0
*/
/*
Plugin Name: Powerbody Importer
Plugin URI: #
Description: Powerbody Importer
Version: 1.0
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: ul_pro
Author URI: #
*/
/*
Copyright (C) Year  Author  Email : charlestsmith888@gmail.com
Woocommerce Advanced plugin layout is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.
Woocommerce Advanced plugin layout is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with Woocommerce Advanced plugin layout; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define('CHI_PATH', dirname(__FILE__));
$plugin = plugin_basename(__FILE__);
define('CHI_URL', plugin_dir_url($plugin));

// menu
require CHI_PATH.'/inc/Class_powerbody.php';
require CHI_PATH.'/inc/class_woocommerce_order_page.php';
require CHI_PATH.'/inc/pb_menu.php';
require CHI_PATH.'/inc/main_class.php';



function Generate_Featured_Image2( $image_url, $post_id = ''){
    $upload_dir = wp_upload_dir();
    $image_data = wp_remote_fopen($image_url);
    $filename   = basename($image_url); // Create image file name
    if(wp_mkdir_p($upload_dir['path']))     $file = $upload_dir['path'] . '/' . $filename;
    else                                    $file = $upload_dir['basedir'] . '/' . $filename;
    file_put_contents($file, $image_data);
    $wp_filetype = wp_check_filetype($filename, null );
    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title' => sanitize_file_name($filename) ,
        'post_content' => '',
        'post_status' => 'inherit'
    );
    $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
    $res1= wp_update_attachment_metadata( $attach_id, $attach_data );
    return $attach_id;
}

function wp_exist_post_by_title( $title ) {
  global $wpdb;
  $sql = "SELECT ID FROM " . $wpdb->prefix . "posts WHERE post_title = '" . $title . "' && post_type = 'product'";
  $result = $wpdb->get_row($sql);
  if($wpdb->num_rows > 0)
    return $result;
  else
    return false;
}


function create_product_variation( $product_id, $variation_data ){
// Get the Variable product object (parent)
$product = wc_get_product($product_id);

$variation_post = array(
    'post_title'  => $product->get_title(),
    'post_name'   => 'product-'.$product_id.'-variation',
    'post_status' => 'publish',
    'post_parent' => $product_id,
    'post_type'   => 'product_variation',
    'guid'        => $product->get_permalink()
);

// Creating the product variation
$variation_id = wp_insert_post( $variation_post );

// Get an instance of the WC_Product_Variation object
$variation = new WC_Product_Variation( $variation_id );

// Iterating through the variations attributes
foreach ($variation_data['attributes'] as $attribute => $term_name )
{
    $taxonomy = 'pa_'.$attribute; // The attribute taxonomy

    // If taxonomy doesn't exists we create it (Thanks to Carl F. Corneil)
    if( ! taxonomy_exists( $taxonomy ) ){
        register_taxonomy(
            $taxonomy,
            'product_variation',
            array(
                'hierarchical' => false,
                'label' => ucfirst( $taxonomy ),
                'query_var' => true,
                'rewrite' => array( 'slug' => '$taxonomy') // The base slug
            )
        );
    }

    // Check if the Term name exist and if not we create it.
    if( ! term_exists( $term_name, $taxonomy ) )
        wp_insert_term( $term_name, $taxonomy ); // Create the term

    $term_slug = get_term_by('name', $term_name, $taxonomy )->slug; // Get the term slug

    // Get the post Terms names from the parent variable product.
    $post_term_names =  wp_get_post_terms( $product_id, $taxonomy, array('fields' => 'names') );

    // Check if the post term exist and if not we set it in the parent variable product.
    if( ! in_array( $term_name, $post_term_names ) )
        wp_set_post_terms( $product_id, $term_name, $taxonomy, true );

    // Set/save the attribute data in the product variation
    update_post_meta( $variation_id, 'attribute_'.$taxonomy, $term_slug );
}

if (!empty($variation_data['featureimg'])) {
    update_post_meta( $variation_id, '_thumbnail_id', $variation_data['featureimg']);
}

## Set/save all other data

// SKU
if( ! empty( $variation_data['sku'] ) )
    $variation->set_sku( $variation_data['sku'] );

// Prices
if( empty( $variation_data['sale_price'] ) ){
    $variation->set_price( $variation_data['regular_price'] );
} else {
    $variation->set_price( $variation_data['sale_price'] );
    $variation->set_sale_price( $variation_data['sale_price'] );
}
$variation->set_regular_price( $variation_data['regular_price'] );

// Stock
if( ! empty($variation_data['stock_qty']) ){
    $variation->set_stock_quantity( $variation_data['stock_qty'] );
    $variation->set_manage_stock(true);
    $variation->set_stock_status('');
} else {
    $variation->set_manage_stock(false);
}

$variation->set_weight(''); // weight (reseting)

$variation->save(); // Save the data
}


/**
 * Create a taxonomy
 *
 * @uses  Inserts new taxonomy object into the list
 * @uses  Adds query vars
 *
 * @param string  Name of taxonomy object
 * @param array|string  Name of the object type for the taxonomy object.
 * @param array|string  Taxonomy arguments
 * @return null|WP_Error WP_Error if errors, otherwise null.
 */
function cs_taxonomy_brands() {

    $labels = array(
        'name'                  => _x( 'Supplier', 'Taxonomy Supplier', 'text-domain' ),
        'singular_name'         => _x( 'Suppliers', 'Taxonomy Suppliers', 'text-domain' ),
        'search_items'          => __( 'Search Supplier', 'text-domain' ),
        'popular_items'         => __( 'Popular Supplier', 'text-domain' ),
        'all_items'             => __( 'All Supplier', 'text-domain' ),
        'parent_item'           => __( 'Parent Suppliers', 'text-domain' ),
        'parent_item_colon'     => __( 'Parent Suppliers', 'text-domain' ),
        'edit_item'             => __( 'Edit Suppliers', 'text-domain' ),
        'update_item'           => __( 'Update Suppliers', 'text-domain' ),
        'add_new_item'          => __( 'Add New Suppliers', 'text-domain' ),
        'new_item_name'         => __( 'New Suppliers Name', 'text-domain' ),
        'add_or_remove_items'   => __( 'Add or remove Supplier', 'text-domain' ),
        'choose_from_most_used' => __( 'Choose from most used Supplier', 'text-domain' ),
        'menu_name'             => __( 'Supplier', 'text-domain' ),
    );

    $args = array(
        'labels'            => $labels,
        'public'            => true,
        'show_in_nav_menus' => true,
        'show_admin_column' => true,
        'hierarchical'      => true,
        'show_tagcloud'     => true,
        'show_ui'           => true,
        'query_var'         => true,
        'rewrite'           => false,
        'query_var'         => false,
        'capabilities'      => array(),
    );

    register_taxonomy( 'supplier', array( 'product' ), $args );
}

add_action( 'init', 'cs_taxonomy_brands' );

// Styling and scripts
add_action('booking_script_css', 'booking_script_css_styles');
function booking_script_css_styles(){
    echo '<link rel="stylesheet" href="'.CHI_URL.'assets/css/bootstrap.css">';
}



function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function get_product_by_sku( $sku ) {
  global $wpdb;
  $product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $sku ) );
  if ( $product_id ) 
    return $product_id;
    // return $product = get_product( $product_id );
}

add_action( 'before_delete_post', 'delete_product_images', 10, 1 );
function delete_product_images( $post_id ){
    
    $product = wc_get_product( $post_id );
    if ( !$product ) {
        return;
    }

    $featured_image_id = $product->get_image_id();
    $image_galleries_id = $product->get_gallery_image_ids();

    if( !empty( $featured_image_id ) ) {
        wp_delete_post( $featured_image_id );
    }

    if( !empty( $image_galleries_id ) ) {
        foreach( $image_galleries_id as $single_image_id ) {
            wp_delete_post( $single_image_id );
        }
    }
}