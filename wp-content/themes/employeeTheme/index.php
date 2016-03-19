
<?php get_header(); ?>
  <div class="content">
      This is my Content
  </div>

  <a href="<?php echo get_permalink( get_page_by_path( 'login' )->ID ); ?>">Login</a>
  <a href="<?php echo get_permalink( get_page_by_path( 'about' )->ID ); ?>">About</a>

  <?php get_footer(); ?>
