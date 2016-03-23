<?php
  function lavprint($arr){
    echo '<pre>';
      print_r($arr);
    echo '</pre>';
  }


// Block Employees from WP-Admin
  add_action( 'init', 'blockusers_init' );
  function blockusers_init() {
  if ( is_admin() && ! current_user_can( 'administrator' ) &&
  ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
  wp_redirect( home_url() );
  exit;
  }
  }


  // Add Custom User Directory
  function wpum_add_custom_directory_template( $templates ) {

  	$templates['custom'] = 'Custom Template';

  	return $templates;

  }
  add_filter( 'wpum_get_directory_templates', 'wpum_add_custom_directory_template' );



 ?>
