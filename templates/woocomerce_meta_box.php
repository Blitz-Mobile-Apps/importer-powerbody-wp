<?php do_action('booking_script_css');  
global $post; ?>
<div style="clear: both;"></div>
<div class="card mb-3">
  <h3 class="card-header">PowerBody Response</h3>
  <div class="card-body">
    <?php 
    $Response = get_post_meta($post->ID, '_powerBodyApiResponse' , true);
    $Response = json_decode($Response, true);
    echo "<pre>";
    print_r($Response);
    echo "</pre>";
    ?>
  </div>
</div>