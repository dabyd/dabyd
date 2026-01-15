<?php 
    // ikg_show_all_metas();
    $tipo_fondo = ikg_get_acf_value('tipus_fons');
    $overlay = ikg_get_acf_value('overlay');
    $alineacio = ikg_get_acf_value('alineacio');
    $botons_per_fila = ikg_get_acf_value('quants_botons_per_fila');
    $botons_class = $botons_per_fila ? 'cols-' . $botons_per_fila : '';
?>

<!-- Big Hero Section -->
<section class="big-hero <?php ikg_get_variant(); ?> <?php echo esc_attr($alineacio); ?>">
    
    <!-- Background Wrapper -->
    <div class="big-hero__bg-wrapper">
        <?php if ($tipo_fondo == '0') : ?>
            <?php 
                ikg_get_image([
                    'image_id' => ikg_get_acf_value('imatge_fons'),
                    'class' => 'big-hero__bg-media',
                    'force-img' => true
                ]); 
            ?>
        <?php else : ?>
            <?php 
                ikg_get_video([
                    'video_id' => ikg_get_acf_value('video_fons'),
                    'class' => 'big-hero__bg-media',
                    'autoplay' => true,
                    'loop' => true,
                    'muted' => true,
                    'controls' => false
                ]); 
            ?>
        <?php endif; ?>
        
        <!-- Overlay -->
        <div class="big-hero__overlay <?php echo esc_attr($overlay); ?>"></div>
    </div>

    <!-- Content -->
    <div class="container">
        <div class="big-hero__content">
            <?php if ( ikg_has_value('titol_petit') ) : ?>
                <span class="big-hero__label"><?php ikg_value('titol_petit'); ?></span>
            <?php endif; ?>
            
            <h1 class="big-hero__title"><?php ikg_value('titol_gran'); ?></h1>
            
            <?php if ( ikg_has_value('descripcio') ) : ?>
                <p class="big-hero__description"><?php echo ikg_get_text('descripcio'); ?></p>
            <?php endif; ?>
            
            <?php if ( ikg_get_acf_value('botons') > 0 ) : ?>
                <div class="big-hero__buttons <?php echo esc_attr($botons_class); ?>">
                    <?php ikg_put_buttons(); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
