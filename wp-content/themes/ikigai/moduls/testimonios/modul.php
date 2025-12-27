<section class="testimonials <?php ikg_get_variant(); ?>">
    <div class="container">
        <span class="section-label"><?php echo ikg_get_acf_value('titol_petit'); ?></span>
        <h2 class="section-title"><?php echo ikg_get_acf_value('titol_gran'); ?></h2>

        <?php
        $args = array(
            'post_type'      => 'testimonios',
            'posts_per_page' => -1, 
            'orderby'        => 'date',
            'order'          => 'DESC',
        );

        $testimonios_query = new WP_Query($args);

        if ($testimonios_query->have_posts()) :
            $total_testimonios = $testimonios_query->post_count;
        ?>

        <div class="testimonials-carousel" data-carousel="testimonials">
            <!-- Botón anterior -->
            <button class="carousel-nav prev" aria-label="Anterior">‹</button>

            <!-- Grid de testimonios -->
            <div class="testimonials-grid">
                <?php
                while ($testimonios_query->have_posts()) : $testimonios_query->the_post();

                    $puntuacion = get_post_meta(get_the_ID(), 'puntuacion', true);
                    $nombre     = get_post_meta(get_the_ID(), 'nombre', true);
                    $localidad  = get_post_meta(get_the_ID(), 'localidad', true);

                    $estrellas = is_numeric($puntuacion) ? str_repeat('★', intval($puntuacion)) : $puntuacion;
                ?>

                <div class="testimonial-card">
                    <div class="testimonial-stars"><?php echo esc_html($estrellas); ?></div>

                    <h3><?php the_title(); ?></h3>

                    <div class="testimonial-text testimonial-quote">
                        <?php 
                        $content = get_the_content();
                        $content = apply_filters('the_content', $content);
                        $content = trim( str_replace(['<p>', '</p>'], '', $content) ); 
                        echo $content;
                        ?>
                    </div>

                    <div class="testimonial-author">
                        <strong><?php echo esc_html($nombre); ?></strong>
                        <span><?php echo esc_html($localidad); ?></span>
                    </div>
                </div>

                <?php
                endwhile;
                wp_reset_postdata();
                ?>
            </div>

            <!-- Botón siguiente -->
            <button class="carousel-nav next" aria-label="Siguiente">›</button>
        </div>

        <!-- Indicadores (dots) -->
        <div class="carousel-dots" data-dots="testimonials"></div>

        <?php
        else :
            echo '<p>Aún no hay testimonios disponibles.</p>';
        endif;
        ?>
    </div>
</section>
