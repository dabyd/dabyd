<?php 
    // ikg_show_all_metas();
    $slides = ikg_get_acf_value('slides'); // Repeater
    $overlay = ikg_get_acf_value('overlay');
    $alineacio = ikg_get_acf_value('alineacio');
    $botons_per_fila = ikg_get_acf_value('quants_botons_per_fila');
    $botons_class = $botons_per_fila ? 'cols-' . $botons_per_fila : '';
    
    // Intervalo (default 5s)
    $interval = ikg_get_acf_value('tiempo_de_transicion_segundos');
    if (!$interval) $interval = 5;
?>

<!-- Hero Slider Section -->
<section class="hero-slider <?php ikg_get_variant(); ?> <?php echo esc_attr($alineacio); ?>" data-interval="<?php echo esc_attr($interval); ?>">    
    <!-- Background Slider Wrapper -->
    <div class="hero-slider__bg-wrapper">
        <?php if ($slides>0): ?>
            <?php for ($n=0; $n < $slides; $n++): 
				ikg_setbase('slides_' . $n . '_');

                $active_class = ($n === 0) ? 'active' : '';
                $type = ikg_get_acf_value('tipus_fons' ); // 'imagen' or 'video'
            ?>
                <div class="hero-slider__slide <?php echo $active_class; ?>">
                    <?php if ($type == 'imagen' || $type == 'image') : ?>
                        <?php 
                            ikg_get_image([
                                'image_id' => 'imagen',
                                'class' => 'hero-slider__bg-media',
                                'force-img' => true
                            ]); 
                        ?>
                    <?php else : ?>
                        <?php 
                            ikg_get_video([
                                'video_id' => 'video',
                                'class' => 'hero-slider__bg-media',
                                'autoplay' => true,
                                'loop' => true,
                                'muted' => true,
                                'controls' => false
                            ]); 
                        ?>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
        <?php endif; ?>

		<?php ikg_reset(); ?>
        
    </div>
    
    <!-- Overlay -->
    <div class="hero-slider__overlay <?php echo esc_attr($overlay); ?>"></div>

    <!-- Content -->
    <div class="container">
        <div class="hero-slider__content">
            <?php if ( ikg_has_value('titol_petit') ) : ?>
                <span class="hero-slider__label"><?php ikg_value('titol_petit'); ?></span>
            <?php endif; ?>
            
            <h1 class="hero-slider__title"><?php ikg_value('titol_gran'); ?></h1>
            
            <?php if ( ikg_has_value('descripcio') ) : ?>
                <p class="hero-slider__description"><?php echo ikg_get_text('descripcio'); ?></p>
            <?php endif; ?>
            
            <?php if ( ikg_get_acf_value('botons') > 0 ) : ?>
                <div class="hero-slider__buttons <?php echo esc_attr($botons_class); ?>">
                    <?php ikg_put_buttons(); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
