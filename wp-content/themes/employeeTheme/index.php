
<?php get_header(); ?>
  <div class="content">
      This is my Content
  </div>

  <div class="login_field col-md-12">
      <a class="login_link " href="<?php echo get_permalink( get_page_by_path( 'login' )->ID ); ?>">Login</a>
  </div>
  <?php get_footer(); ?>
