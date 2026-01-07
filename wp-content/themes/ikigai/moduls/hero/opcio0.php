	<?php 
        // ikg_show_all_metas(); 
        $clase = '';
        if ( ikg_get_acf_value('invertir') ) {
            $clase = 'is-reversed';
        }
    ?>
	<!-- Hero Section -->
    <section class="hero <?php ikg_get_variant(); ?> <?php echo $clase; ?>">
        <div class="hero-content container">
            <div class="hero-text">
                <h1 class="hero-title"><?php ikg_value( 'titol_gran' ); ?></h1>
                <p class="hero-description">
					<?php ikg_value( 'texte' ); ?>
                </p>
                <div class="hero-buttons">
                    <?php ikg_put_buttons(); ?>
                </div>
            </div>
            <div class="hero-image">
                <div class="hero-image-placeholder">
                    <?php ikg_get_image( ['image_id' => 'imatge' ] ); ?>
                </div>
            </div>
        </div>
    </section>
