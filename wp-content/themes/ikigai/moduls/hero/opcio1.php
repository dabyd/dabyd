<?php 
$parallax = ikg_get_acf_value('parallax');
$tipo     = ikg_get_acf_value('tipo_parallax');
$capa     = ikg_get_acf_value('capa_ofuscacion'); // Añade este campo en ACF

$clase_parallax = $parallax ? ' has-parallax has-media' : '';
?>

<section class="services-hero <?php ikg_get_variant(); ?> <?php echo $clase_parallax; ?>">
    <?php if ( $parallax ) : ?>
        <div class="hero-bg-wrapper">
            <?php 
                if ( '1' == $tipo ) {
                    ikg_get_image([ 
                        'image_id' => ikg_get_acf_value('imatge_parallax'), 
                        'class' => 'hero-bg-media hero-parallax', 
                        'force-img' => true 
                    ]);
                } else {
                    ikg_get_video([ 
                        'video_id' => ikg_get_acf_value('video_parallax'), 
                        'class' => 'hero-bg-media hero-parallax', 
                        'autoplay' => true, 
                        'loop' => true, 
                        'muted' => true, 
                        'controls' => false 
                    ]);
                }
            ?>
            <div class="hero-overlay <?php echo esc_attr($capa); ?>"></div>
        </div>
    <?php endif; ?>

    <div class="container">
        <div class="services-hero-content"> <span class="section-label"><?php ikg_value('titol_petit'); ?></span>
            <h1><?php ikg_value('titol_gran'); ?></h1>
            <p class="section-subtitle"><?php echo ikg_get_text('texte'); ?></p>
            <div class="hero-buttons">
                <?php ikg_put_buttons(); ?>
            </div>
        </div>
    </div>
</section>