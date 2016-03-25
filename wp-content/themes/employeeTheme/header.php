<html>
  <head>
    <link href="<?php bloginfo('stylesheet_url'); ?>" rel="stylesheet" type="text/css" />
    <link href="<?php bloginfo('template_url'); ?>/bootstrap/css/bootstrap.min.css" rel="stylesheet" >
</head>

    <?php
          $activePage = 'Home';
        switch(get_post_type($post)){
          case 'page':
              if($post->post_name == 'home'){
                  $activePage = 'Home';
              }
              else if($post->post_name == 'today'){
                  $activePage = 'Today';
              }
              else if($post->post_name == 'extra-work'){
                  $activePage = 'Extra';
              }
              else if($post->post_name == 'unassigned-shifts'){
                  $activePage = 'Unassigned';
              }
              else if($post->post_name == 'directory'){
                  $activePage = 'Directory';
              }
              break;
        }

        global $current_user;
        get_currentuserinfo();

     ?>
    <body>

      <!-- <div class="row">
        <div class="header col-md-12"> -->


      <?php if(is_user_logged_in()){ ?>
      <nav class="navbar navbar-default">
        <div class="container-fluid">

          <!-- Collect the nav links, forms, and other content for toggling -->
          <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav">
              <li <?php if($activePage == 'Home'){echo 'class="active"';} ?>><a href="<?php echo get_permalink( get_page_by_path( 'Home' )->ID ); ?>">Home <span class="sr-only">(current)</span></a></li>
              <?php if($current_user->user_level < 10){ ?>
              <li <?php if($post->post_name == 'today'){echo 'class="active"';} ?>><a href="<?php echo get_permalink( get_page_by_path( 'Today' )->ID ); ?>">Today</a></li>
              <li <?php if($activePage == 'Extra'){echo 'class="active"';} ?>><a href="<?php echo get_permalink( get_page_by_path( 'Extra Work' )->ID ); ?>">Extra Work</a></li>
              <li <?php if($activePage == 'Unassigned'){echo 'class="active"';} ?>><a href="<?php echo get_permalink( get_page_by_path( 'Unassigned Shifts' )->ID ); ?>">Unassigned Shifts</a></li>
              <?php }else if($current_user->user_level >= 10){ ?>

                  <li <?php if($activePage == 'Directory'){echo 'class="active"';} ?>><a href="<?php echo get_permalink( get_page_by_path( 'Directory' )->ID ); ?>">Directory</a></li>
                  <li ><a href="<?php echo get_admin_url(); ?>">Admin</a></li>
                <?php } ?>
            </ul>

            <div class="welcome_message">
              <?php
                  echo 'Hello,' . $current_user->data->display_name;
                    // lavprint($current_user);
                  echo '<br>';
              ?>
                <div class="logout_link">
                  <?php echo do_shortcode('[wpum_logout redirect="Login" label="Logout"]'); ?>
                </div>
            </div> <!-- End of welcome Message -->

          </div><!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
      </nav>




      <?php }else{ ?>
            <nav class="navbar navbar-default">
              <a class="register_link col-md-1 col-md-offset-11" href="<?php echo get_permalink( get_page_by_path( 'register' )->ID ); ?>">Register</a>
            </nav>
        <?php } ?>
        <!-- </div>
            </div> -->


        <!-- <?php
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

        ?> -->
