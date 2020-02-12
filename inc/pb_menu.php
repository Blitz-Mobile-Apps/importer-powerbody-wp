<?php 
function pb_main_menu(){require CHI_PATH.'/templates/powerbody_credentials.php';}
function pb_donwload_list(){require CHI_PATH.'/templates/download_list.php';}

add_action('admin_menu', 'wpse149688');
function wpse149688(){
	add_menu_page( 'Powerbody Importer', 'Powerbody Importer', 'read', 'pb_main_menu', 'pb_main_menu');
	add_submenu_page( 'pb_main_menu', 'Import Products', 'Import Products', 'read', 'pb_donwload_list', 'pb_donwload_list');
}

// Styling and scripts
add_action('lms_scripts', 'lms_scripts_styles');
function lms_scripts_styles(){
	echo '<link rel="stylesheet" href="'.CHI_URL.'assets/css/bootstrap.css">';
}
