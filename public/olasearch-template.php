<?php
/*
 * Template Name: Ola Search Template
 */

get_header(); ?>

    <div class="wrap">
        <div id="primary" class="content-area" style="width: 100%;">
            <main id="main" class="site-main" role="main">

				<?php
				while ( have_posts() ) : the_post();

					the_content();

				endwhile; // End of the loop.
				?>

            </main><!-- #main -->
        </div><!-- #primary -->
    </div><!-- .wrap -->

<?php get_footer();
