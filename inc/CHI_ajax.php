<?php 

// print branches againt companies
add_action( 'wp_ajax_get_branches', 'get_branches' );
add_action( 'wp_ajax_nopriv_get_branches', 'get_branches' );
function get_branches(){
	global $wpdb,$obj;
	$branchestab= $wpdb->prefix.'ego_branch';
	$data  = $obj->getRows($branchestab, ['where' =>['compid' => $_POST['id'] ] ]);
	print(json_encode($data));
	exit();
}



// get_items
add_action( 'wp_ajax_get_items', 'get_items' );
add_action( 'wp_ajax_nopriv_get_items', 'get_items' );
function get_items(){
	global $wpdb,$obj;
	$branchestab = $wpdb->prefix.'ego_items';
	$data1 =  $wpdb->get_results( 'SELECT * FROM '.$branchestab .' WHERE itemName LIKE "%'.$_GET['term'].'%" ');
	foreach ($data1 as $key) {
		$new[] = ['id'=> $key->id, 'name' => $key->itemName];
	}
	$data['items']  = $new;
	print(json_encode($data));
	exit();
}

//get items types
add_action( 'wp_ajax_get_items_type', 'get_items_type' );
add_action( 'wp_ajax_nopriv_get_items_type', 'get_items_type' );
function get_items_type(){
	global $wpdb,$obj;
	$branchestab = $wpdb->prefix.'ego_items_type';
	$data1 =  $wpdb->get_results( 'SELECT * FROM '.$branchestab .' WHERE name LIKE "%'.$_GET['term'].'%" ');
	foreach ($data1 as $key) {
		$new[] = ['id'=> $key->id, 'name' => $key->name];
	}
	$data['items']  = $new;
	print(json_encode($data));
	exit();
}


// Popupfunctin
add_action( 'wp_ajax_booking_billing_detail', 'booking_billing_detail' );
add_action( 'wp_ajax_nopriv_booking_billing_detail', 'booking_billing_detail' );
function booking_billing_detail(){
	global $wpdb;
	$id = $_GET['id'];
	$results = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'dynamic_form_inquiries WHERE id='.$id);
	$data = json_decode($results->billingdata);
	$message .= '<h2>Billing Details</h2>';
	$message .= '<table rules="all" style="border-color: #666;" cellpadding="10">';
	
	foreach ($data as $key => $value) {
		$message .= "<tr style='background: #eee;'><td><strong>".ucwords(str_replace('_', ' ', $key)).":</strong> </td><td>".$value."</td></tr>";
	}
	
	$message .= "</table>";
	echo $message;
	exit();
}

add_action( 'wp_ajax_booking_order_details', 'booking_order_details' );
add_action( 'wp_ajax_nopriv_booking_order_details', 'booking_order_details' );
function booking_order_details(){
	global $wpdb;
	$id = $_GET['id'];
	$results = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'dynamic_form_inquiries WHERE id='.$id);
	$data = json_decode($results->ingredients);
	$message .= '<h2>Ingredients Details</h2>';
	$message .= '<table rules="all" style="border-color: #666;" cellpadding="10">';
	
	foreach ($data as $key => $value) {
		$message .= "<tr style='background: #eee;'><td><strong>".ucwords(str_replace('_', ' ', $key)).":</strong> </td><td>".$value."</td></tr>";
	}
	
	$message .= "<tr style='background: #eee;'><td><strong>Amount:</strong> </td><td>".$results->amount."</td></tr>";
	$message .= "</table>";
	echo $message;
	exit();
}