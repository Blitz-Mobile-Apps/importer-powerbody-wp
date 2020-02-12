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

					if ($product->get_status() == 'publish') {
						
						update_post_meta( $productID, 'cron_date_update', date("d-m-Y") );
						update_post_meta( $productID, 'api_product_ean', $productget['ean'] );

					}elseif ($product->get_status() == 'pending') {

						update_post_meta( $productID, 'cron_date_update', date("d-m-Y") );
						update_post_meta( $productID, 'api_product_ean', $productget['ean'] );

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