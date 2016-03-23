<html>
  <head>
    <link href="<?php bloginfo('stylesheet_url'); ?>" rel="stylesheet" type="text/css" />
    <link href="<?php bloginfo('template_url'); ?>/bootstrap/css/bootstrap.min.css" rel="stylesheet" >

</head>
    <body>
        <div class="row">


      <div class="header col-md-12">
        <?php
          if (is_user_logged_in()) {
            global $current_user;
            get_currentuserinfo();
            echo 'Hello,' . $current_user->data->display_name;
              // lavprint($current_user);
            echo '<br>';
            echo 'Current User Level ' . $current_user->user_level;
          }else{
            ?>
              <a class="register_link col-md-1 col-md-offset-11" href="<?php echo get_permalink( get_page_by_path( 'register' )->ID ); ?>">Register</a>

            <?php

          }

        ?>
        </div>
      </div>
