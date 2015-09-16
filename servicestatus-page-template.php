<?php define( 'DONOTCACHEPAGE', True ); ?>
<?php require_once('status-functions.php');
	get_header(); ?>
    <div id="wrap" class="row">

<div id="main_content" role="main">


			<div id="content" class="it_container">
				<div id="secondary" class="col-lg-3 col-md-3 hidden-sm hidden-xs" role="complementary">
					<div class="stripe-top"></div><div class="stripe-bottom"></div>
                      <div class="" id="sidebar" role="navigation" aria-label="Sidebar Menu">
                      <?php dynamic_sidebar('servicenow-sidebar'); ?>
                      </div>
				</div>
        <div id="primary" class="col-lg-9 col-md-9 col-sm-12 col-xs-12">
			<?php while ( have_posts() ) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<header class="entry-header">
					<h1 class="entry-title hidden-phone"><?php apply_filters('italics', get_the_title()); ?></h1>
				</header><!-- .entry-header -->

				<div class="entry-content">
					<p>This page shows active eOutages and High priority incidents for UW Seattle, Bothell and Tacoma network and computing services managed by UW Information Technology.</p>
<?php //if (check_e_outage()) { echo "<p><div class='alert alert-warning' style='margin-top:2em;'>There have been <a href='https://www.washington.edu/cac/outages' target='_blank'>eOutages</a> reported</div>"; } ?>
					<?php the_content(); ?>
					<?php wp_link_pages( array( 'before' => '<div class="page-link"><span>' . __( 'Pages:', 'twentyeleven' ) . '</span>', 'after' => '</div>' ) );
                    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
                    // prompt the user to log in and leave feedback if appropriate
                    if (is_plugin_active('document-feedback/document-feedback.php') && !is_user_logged_in()): ?>
                    <p id='feedback_prompt'><?php printf(__('<a href="%s">Log in</a> to leave feedback.'), wp_login_url( get_permalink() . '#document-feedback' ) ); ?></p>
                    <?php endif;?>
				</div><!-- .entry-content -->
                <div id="noscript" class="alert">
        JavaScript is required to view this content. Please enable JavaScript and try again.
        </div>
        <div id="spinner" style="width:150px; margin:auto; display: none; text-align:center; line-height:100px;">
            <img src="<?php echo site_url('/wp-admin/images/loading.gif'); ?>" alt="Loading..." />
        </div>
        <div id="services"></div>
                </div>
            </article><!-- #post-<?php the_ID(); ?> -->

			<?php
          endwhile; // end of the loop.
      ?>
 			 </div>
			</div>
      </div>
		</div>
        <div class="push"></div>
   </div>
   <script>
       servicestatus();
   </script>

<!-- #wrap -->
<?php get_footer(); ?>
