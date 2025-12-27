<?php
/**
 * The main template file
 * * Este es el archivo más genérico en la jerarquía de plantillas de WordPress.
 */

get_header(); 

require_once( __DIR__ . '/header.php' );

$moduls = ikg_get_acf_modules();
if (count($moduls) > 0) {
    ikg_pinta_pagina_acf();
} else {
?>
    <main id="site-content">
        <div class="container">

            <?php if ( have_posts() ) : ?>

                <?php while ( have_posts() ) : the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                        <header class="entry-header">
                            <?php the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' ); ?>
                        </header>

                        <div class="entry-content">
                            <?php the_excerpt(); ?>
                        </div>
                    </article>
                <?php endwhile; ?>

                <?php the_posts_pagination(); ?>

            <?php else : ?>
                <p>No se encontraron contenidos.</p>
            <?php endif; ?>

        </div>
    </main>
<?php } ?>
<?php get_footer(); ?>