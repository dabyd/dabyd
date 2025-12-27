    <?php 
        // ikg_show_all_metas(); 
        $link = ikg_get_acf_value( 'pagina_de_serveis' );
        if ( is_array( $link ) ) {
            if ( isset( $link['url'] ) ) {
                $link = $link['url'] . '#';
            } else {
                $link = '#';
            }
        } else {
            $link = '#';
        }
    ?>
    <!-- Servicios Section -->
    <section class="services-preview <?php ikg_get_variant(); ?>">
        <div class="container">
            <span class="section-label"><?php ikg_value('titol_petit'); ?></span>
            <h2 class="section-title"><?php ikg_value('titol_gran'); ?></h2>
            <p class="section-subtitle">
                <?php ikg_value('subtitol'); ?>
            </p>
            
            <div class="services-grid">
                <?php
                // Argumentos para traer todos los servicios
                $args = array(
                    'post_type'      => 'servicios',
                    'posts_per_page' => -1, // Traer todos
                    'orderby'        => 'date',
                    'order'          => 'ASC',
                );

                $servicios_query = new WP_Query($args);

                if ($servicios_query->have_posts()) :
                    $counter = 1;
                    while ($servicios_query->have_posts()) : $servicios_query->the_post();
                        // Obtener el meta 'extracte'
                        $extracte = get_post_meta(get_the_ID(), 'extracte', true);
                        
                        // Formatear el número con cero a la izquierda (01, 02...)
                        $number = str_pad($counter, 2, '0', STR_PAD_LEFT);
                        ?>
                        
                        <div class="service-card">
                            <div class="service-number"><?php echo $number; ?></div>
                            <h3><?php the_title(); ?></h3>
                            <p><?php echo esc_html($extracte); ?></p>
                            <a href="<?php echo $link . get_post_field( 'post_name', get_the_ID() ); ?>" class="service-link">Más información →</a>
                        </div>

                        <?php
                        $counter++;
                    endwhile;
                    wp_reset_postdata(); // Restaurar datos originales del post global
                else :
                    echo '<p>No se encontraron servicios.</p>';
                endif;
                ?>
            </div>
        </div>
    </section>