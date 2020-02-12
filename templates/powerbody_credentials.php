<?php
do_action( 'lms_scripts'); 
if (!empty($_POST)) {
	update_option('api_login', $_POST['login']);
	update_option('api_pwd', $_POST['password']);
	echo '<div class="notice notice-success is-dismissible"><p>Record updated!</p></div>';
}
?>




<div class="card">
  <h3 class="card-header">Settings</h3>
  <div class="card-body">




<form accept="" method="POST">
  <div class="form-group row">
    <label for="staticEmail" class="col-sm-2 col-form-label">Username:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control-plaintext" id="staticEmail"  name="login" placeholder="example@gmail.com" value="<?php echo get_option('api_login'); ?>">
    </div>
  </div>

  <div class="form-group row">
    <label for="inputPassword" class="col-sm-2 col-form-label">Password</label>
    <div class="col-sm-10">
      <input type="password" class="form-control" id="inputPassword" placeholder="********" name="password" value="<?php echo get_option('api_pwd'); ?>">
    </div>
  </div>

  <div class="form-group row">
    <label for="inputPassword" class="col-sm-2 col-form-label"></label>
    <div class="col-sm-10">
    	<button type="submit" class="btn btn-primary">Update</button>
    </div>
  </div>
</form>






  </div>
</div>
