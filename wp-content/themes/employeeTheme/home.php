<?php
/*
Template Name: Home
*/
?>


<?php get_header(); ?>


  <?php if($current_user->user_level < 10){ ?>
    <!-- <h1>User is an Employee</h1> -->
      <div class="col-md-offset-2">
        <?php echo do_shortcode('[your_schedule]'); ?>
      </div>
  <?php }else if($current_user->user_level >= 10){ ?>
    <!-- <h1>User is an Admin</h1> -->
      <div class="col-md-offset-1">
        <?php echo do_shortcode('[master_schedule]'); ?>
      </div>
  <?php } ?>

<?php get_footer(); ?>
