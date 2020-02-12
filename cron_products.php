<?php 
define('ABSPATH', dirname(dirname(dirname(dirname(__FILE__)))).'/');

require_once (ABSPATH.'wp-load.php');


$login = 'sales@mega-nutrition.co.uk';
$password = 'nuttrisal21@';
$wsdl = 'http://www.powerbody.co.uk/index.php/api/soap/?wsdl';
$client = new SoapClient($wsdl, array('cache_wsdl' => WSDL_CACHE_NONE));
$session = $client->login($login, $password);




global $wpdb, $obj;
$products = $wpdb->get_results('
	SELECT 
	a.meta_id,
	a.post_id,
	a.meta_key,
	a.meta_value
	FROM wp_postmeta as a
	WHERE a.meta_key = "product_api_id" AND NOT EXISTS(
		SELECT 
		b.meta_value
		FROM wp_postmeta as b 
		WHERE a.post_id = b.post_id AND b.meta_key = "cron_date_update" AND b.meta_value = "'.date("d-m-Y").'"
	)
	LIMIT 20
	');


if ($products) {
	foreach ($products as $key) {
				$productID = $key->post_id;
				$post_id = $key->meta_value;
				$product = wc_get_product( $productID);

				try {
					$productget = $client->call($session, 'dropshipping.getProductInfo', $post_id);
					$productget = json_decode($productget, true);


					pr($productget);

					$category = $productget['category'];
					$brand = $productget['manufacturer'];


					if ($product->get_status() == 'publish') {

						$my_post = array(
							'ID'           => $productID,
							'post_content' => $productget['description_en'],
						);
						wp_update_post( $my_post );

						update_post_meta( $productID , '_regular_price', $productget['price_tax']);
						update_post_meta( $productID , '_price', $productget['price_tax']);
						update_post_meta( $productID , '_manage_stock', 'yes' );
						update_post_meta( $productID, '_stock', $productget['qty']);
						update_post_meta( $productID, '_weight', $productget['weight']);
						update_post_meta( $productID, 'cron_date_update', date("d-m-Y") );
						update_post_meta( $productID, 'api_product_ean', $productget['ean'] );

						


					}elseif ($product->get_status() == 'pending') {

						$my_post = array(
							'ID'           => $productID,
							'post_title'   => $productget['name'],
							'post_content' => $productget['description_en'],
							'post_status' => 'publish',
						);
						wp_update_post( $my_post );
						update_post_meta( $productID , '_regular_price', $productget['price_tax']);
						update_post_meta( $productID , '_price', $productget['price_tax']);
						update_post_meta( $productID , '_manage_stock', 'yes' );
						update_post_meta( $productID, '_stock', $productget['qty']);
						update_post_meta( $productID, '_weight', $productget['weight']);
						update_post_meta( $productID, 'cron_date_update', date("d-m-Y") );
						update_post_meta( $productID, 'api_product_ean', $productget['ean'] );

						$firstimg  = $productget['image'];					
						$attachmentid =  Generate_Featured_Image2($firstimg, $productID);
						update_post_meta( $productID, '_thumbnail_id', $attachmentid);

						$term1 = term_exists(trim($brand), 'brand_category');
						if ($term1 !== 0 && $term1 !== null) {
							$term_id2 = $term1['term_id'];
						}else{
							$term_d = wp_insert_term(
								trim($brand),
								'brand_category',
								array(
									'description'=> '',
									'parent'=> 0
								)
							);
							$term_id2 = $term_d['term_id'];
						}
						wp_set_object_terms( $productID, $brand, 'brand_category', true);

						$term = term_exists(trim($category), 'product_cat');
						if ($term !== 0 && $term !== null) {
							$term_id = $term['term_id'];
						}else{
							$term_d = wp_insert_term(
								trim($category),
								'product_cat',
								array(
									'description'=> '',
									'parent'=> 0
								)
							);
							$term_id = $term_d['term_id'];
						}
						wp_set_object_terms( $productID, $category, 'product_cat', true);
						wp_set_object_terms( $productID, 'Powerbody', 'supplier', true);


					}else{


					}

					// wp_mail('asad.ali@salsoft.net', 'CRon Hit', 'Cron Job Done');

				} catch (Exception $e) {
					if ($e->faultcode == 5) {
						$this->re_authentication_check();
					}
					var_dump($e);
				}




	}
}