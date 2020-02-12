<?php

/**

* China Brands

*/

ini_set('max_execution_time', 0);



class Powerbody{



    protected $login;

    protected $password;

    protected $sesssion;

    protected $productid;

    protected $wsdl = 'http://www.powerbody.co.uk/index.php/api/soap/?wsdl';





    function __construct(){

      $this->login = get_option('api_login');

      $this->password = get_option('api_pwd');

      //$this->sesssion = get_option('api_session');

      session_start();

      $this->authentication_check();

        // after checkout

      add_action( 'woocommerce_thankyou', array( $this, 'create_order'), 10, 1);



        // before checkout

      add_action('woocommerce_after_checkout_validation', array($this , 'after_checkout_validation') );



  }





  public function authentication_check(){



    if (empty($this->sesssion)) {

        try {

            $client = new SoapClient($this->wsdl, array('cache_wsdl' => WSDL_CACHE_NONE));

            $session = $client->login($this->login, $this->password);

            update_option('api_session', $session);

            $this->sesssion = $session;

        }catch (Exception $e){

            var_dump($e);

        }

    } else {



    }

}



public function re_authentication_check(){

    try {

        $client = new SoapClient($this->wsdl, array('cache_wsdl' => WSDL_CACHE_NONE));

        $session = $client->login($this->login, $this->password);

        update_option('api_session', $session);

        $this->sesssion = $session;

    }catch (Exception $e){

        var_dump($e);

    }

}





public function Product_list(){


    $client = new SoapClient($this->wsdl, array('cache_wsdl' => WSDL_CACHE_NONE));

    

    $status = true;

    $i = 1;

    while ($status) {



        try {

            $pageno = array('page' => $i);

            $result = $client->call($this->sesssion, 'dropshipping.getProductList', json_encode($pageno));

            $result = json_decode($result, true);

            echo "<pre>";
            print_r($result);

            if (!empty($result)) {


                foreach ($result as $key) {


                   $title =  $key['sku'];

                   $sku = $key['sku'];

                   $price = $key['price_tax'];

                   $inventory = $key['qty'];

                   $weight = $key['weight'];

                   $proid = $key['product_id'];


                   $checksku = get_product_by_sku($sku);



                         if (!$checksku):



                            $product['post_title']    = $title;

                            $product['post_author']   = '1';

                            $product['post_type']     = 'product';

                            $product['post_status']   = "pending";

                            $post_id = wp_insert_post( $product);

                            update_post_meta( $post_id , '_regular_price', $price);

                            update_post_meta( $post_id , '_price', $price);

                            update_post_meta( $post_id , '_sku', $sku); // outofstock

                            update_post_meta( $post_id , '_stock_status', 'instock'); // outofstock

                            update_post_meta( $post_id , '_weight', $weight);

                            update_post_meta( $post_id , '_visibility', 'visible' );

                            update_post_meta( $post_id , '_backorders', 'no' );

                            update_post_meta( $post_id , '_sold_individually', '' );

                            update_post_meta( $post_id , '_manage_stock', 'yes' );

                            update_post_meta( $post_id, '_stock', $inventory);

                            update_post_meta( $post_id , '_product_version', '3.7.0');

                            update_post_meta( $post_id , '_product_image_gallery', '' );

                            update_post_meta( $post_id , '_wc_review_count', '0' );

                            update_post_meta( $post_id , '_wc_average_rating', '0' );

                            wp_set_object_terms( $post_id, 'Powerbody', 'supplier', true);

                            echo '

                            <div class="alert alert-dismissible alert-success">

                            <button type="button" class="close" data-dismiss="alert">&times;</button>

                            This Simple product has been uploaded successfully! <a href="'.get_the_permalink($post_id).'" class="alert-link">'.get_the_permalink($post_id).'</a>.

                            </div>

                            ';



                        else:

                            $post_id = $checksku;

                            update_post_meta( $post_id , '_regular_price', $price);

                            update_post_meta( $post_id , '_price', $price);

                            update_post_meta( $post_id , '_sku', $sku); // outofstock

                            update_post_meta( $post_id , '_weight', $weight);

                            update_post_meta( $post_id, '_stock', $inventory);

                            echo '

                            <div class="alert alert-dismissible alert-success">

                            <button type="button" class="close" data-dismiss="alert">&times;</button>

                            Product already exist! This is only updated. <a href="'.get_the_permalink($post_id).'" class="alert-link">'.get_the_title($post_id).'</a>.

                            </div>

                            ';

                        endif;

                }

                $i++;

                echo $i;
                $status = false;

                echo "<br>";

            }else{

                $status = false;

                echo $i;

                echo "<br>";

            }



        }catch (Exception $e){

            if ($e->faultcode == 5) {

                $this->re_authentication_check();

            }

            $status = false;

            echo $i;

            echo "<br>";

            var_dump($e);

        }





    }







}



public function create_order($order_id){

    if ( ! $order_id )

        return;



        // Allow code execution only once

    if( ! get_post_meta( $order_id, '_thankyou_action_done', true ) ) {



            // Get an instance of the WC_Order object

        $order = wc_get_order( $order_id );

        $order_data = $order->get_data();



        if($order->is_paid())

            $paid = __('yes');

        else

            $paid = __('no');



            // Loop through order items

        $i = 0;

        foreach ( $order->get_items() as $item_id => $item ) {

                // Get the product object

            $product = $item->get_product();

                // The product ID

            $product_id = $item->get_product_id();

            $product = $item->get_product();

                // The quantity

            $product_qty = $item->get_quantity();

                // The order ID

            $order_id = $item->get_order_id();

                // The WC_Order object

            $order = $item->get_order();

                // The item ID

                $item_id = $item->get_id(); // which is your $order_item_id

                // The product name

                $product_name = $item->get_name(); // … OR: $product->get_name();

                //Get the product SKU (using WC_Product method)

                $sku = $product->get_sku();

                //Price

                $price = $product->get_price();



                $storrproduct[$i]['sku'] = $sku;

                $storrproduct[$i]['name'] = $product_name;

                $storrproduct[$i]['qty'] = $product_qty;

                $storrproduct[$i]['price'] = '';

                $storrproduct[$i]['currency'] = get_option('woocommerce_currency');

                $storrproduct[$i]['tax'] = '';

                $i++;

            }



        }



        $storeOrderData['id']               = $_SESSION['proid'];

        $storeOrderData['status']           = 'Success';

        $storeOrderData['date_add']         = date("Y-m-d");

        $storeOrderData['comment']          = $order_data['customer_note'];

        $storeOrderData['shipping_price']   = '';

        $storeOrderData['address']          = array(

            'name' => $order_data['shipping']['first_name'].' '.$order_data['shipping']['last_name'],

            'surname' => $order_data['shipping']['first_name'].' '.$order_data['shipping']['last_name'],

            'address1' => $order_data['shipping']['address_1'],

            'address2' => $order_data['shipping']['address_2'],

            'address3' => '',

            'postcode' => $order_data['shipping']['postcode'],

            'city' =>  $order_data['shipping']['city'],

            'county' => $order_data['shipping']['country'],

            'country_name' => $order_data['shipping']['country'],

            'country_code' => $order_data['shipping']['country'],

            'phone' => $order_data['billing']['email'],

            'email' => $order_data['billing']['phone'],

        );

        $storeOrderData['products'] = $storrproduct;

        try {

            $client = new SoapClient($this->wsdl, array('cache_wsdl' => WSDL_CACHE_NONE));

            $result = $client->call($this->sesssion, 'dropshipping.updateOrder' , json_encode($storeOrderData) );



            $order->update_meta_data( '_powerBodyApiResponse', $result);

            $order->save();

            $client->endSession($session);

            unset($_SESSION['proid']);



        } catch (Exception $e) {

            if ($e->faultcode == 5) {

                $this->re_authentication_check();

            }

            $order->update_meta_data( '_powerBodyApiResponse', 'Failed Submitting order');

            $order->save();

        }

    }





    public function after_checkout_validation($posted, $errors){





        global $woocommerce;

        $items = $woocommerce->cart->get_cart();

        $i = 0;

        foreach($items as $item => $values) { 

            $product =  wc_get_product( $values['data']->get_id()); 

            $storrproduct[$i]['sku'] = $product->get_sku();

            $storrproduct[$i]['name'] = $product->get_title();

            $storrproduct[$i]['qty'] = $values['quantity'];

            $storrproduct[$i]['price'] = '';

            $storrproduct[$i]['currency'] = get_option('woocommerce_currency');

            $storrproduct[$i]['tax'] = '';

            $i++;

        }

        





        if (empty($_SESSION['proid'])) {

            $_SESSION['proid'] = generateRandomString();

        }





        $storeOrderData['id']               = $_SESSION['proid'];

        $storeOrderData['status']           = 'Pending';

        $storeOrderData['date_add']         = date("Y-m-d");

        $storeOrderData['comment']          = $posted['order_comments'];

        $storeOrderData['shipping_price']   = '';

        $storeOrderData['address']          = array(

            'name' => $posted['shipping_first_name'].' '.$posted['shipping_last_name'],

            'surname' => $posted['shipping_first_name'].' '.$posted['shipping_last_name'],

            'address1' => $posted['shipping_address_1'],

            'address2' => $posted['shipping_address_2'],

            'address3' => '',

            'postcode' => $posted['shipping_postcode'],

            'city' =>  $posted['shipping_city'],

            'county' => $posted['shipping_country'],

            'country_name' => $posted['shipping_country'],

            'country_code' => $posted['shipping_country'],

            'phone' => $posted['billing_email'],

            'email' => $posted['billing_phone'],

        );

        $storeOrderData['products'] = $storrproduct;



        try {

            $client = new SoapClient($this->wsdl, array('cache_wsdl' => WSDL_CACHE_NONE));

            $result = $client->call($this->sesssion, 'dropshipping.createOrder' , json_encode($storeOrderData) );

            $result = json_decode($result, true);

            if ($result['api_response'] == 'FAIL') {

                wc_add_notice( __( $result['api_response_error'], 'woocommerce' ), 'error' );

            }

        } catch (Exception $e) {

            if ($e->faultcode == 5) {

                $this->re_authentication_check();

                wc_add_notice( __( 'Session expired - Please Try to submit again!', 'woocommerce' ), 'error' );

            }else{

                wc_add_notice( __( $e->faultstring, 'woocommerce' ), 'error' );

            }

        }



    }





}

$Powerbody = new Powerbody();