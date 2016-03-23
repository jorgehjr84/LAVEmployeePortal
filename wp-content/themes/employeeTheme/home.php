<?php
/*
Template Name: Home
*/
?>


<?php get_header(); ?>


  <?php if($current_user->user_level < 10){ ?>

    <h1>User is an Employee</h1>

  <?php }else if($current_user->user_level >= 10){ ?>
<h1>User is an Admin</h1>
  <?php } ?>

<?php get_footer(); ?>
