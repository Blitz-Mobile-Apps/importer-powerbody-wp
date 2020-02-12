<?php
/**
* China Brands
*/
class ChinaBrand{
	public $token;
	function __construct(){
		$this->$token = get_option('chinaBrand_token_generate');
	}
	public function generate_token(){
		$client_secret = '0b43b5959cbae304e8f793b1b850951a';
		$data = array(
			'email' => 'igorek112019@gmail.com',
			'password' => 'walmart1.',
			'client_id' => '1206980927'
		);
		$json_data = json_encode($data);
$signature_string = md5($json_data.$client_secret); //签名数据
$post_data = 'signature='.$signature_string.'&data='.urlencode($json_data);
$curl = curl_init('https://cnapi.chinabrands.com/v2/user/login');
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
$result = curl_exec($curl); //返回结果
$result = json_decode( $result, $assoc_array = false );
curl_close($curl);
update_option('chinaBrand_token_generate', $result->msg->token);
}
public function show_all_download_product_list(){
	$token = $this->$token;
	$post_data = array(
		'token' => $token,
		'type' => 0,
		'per_page' => 50,
		'page_number' => 1,
	);
	$curl = curl_init('https://cnapi.chinabrands.com/v2/user/inventory');
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
$result = curl_exec($curl); //返回结果
$result = json_decode( $result, $assoc_array = false );
if ($result->status == 1) {
	return $result;
}elseif ($result->status == 0 AND $result->errcode == 10014) {
	$this->generate_token();
	echo '<div class="notice notice-success is-dismissible"><p>Refresh again!</p></div>';
	die();
}elseif ($result->status == 0) {
	$this->generate_token();
	echo '<div class="notice notice-success is-dismissible"><p>'.$result->msg.'</p></div>';
	die();
}else{
	$this->generate_token();
	echo '<div class="notice notice-success is-dismissible"><p>Load again</p></div>';
	die();
}
}
public function show_product_by_id($goods_sn){
	$token = $this->$token;
	$goods_sn = $goods_sn;
	$post_data = array(
		'token' => $token,
		'goods_sn' => json_encode($goods_sn)
	);
	$curl = curl_init('https://cnapi.chinabrands.com/v2/product/index');
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
$result = curl_exec($curl); //返回结果
$result = json_decode( $result, $assoc_array = false );
curl_close($curl);
return $result;
}
}
$ChinaBrand = new ChinaBrand();