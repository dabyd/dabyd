	<?php 
        //ikg_show_all_metas(); 
        $clase = '';
        if ( ikg_get_acf_value('invertir') ) {
            $clase = 'is-reversed';
        }
    ?>
	<!-- Hero Section -->
    <section class="about-hero  <?php ikg_get_variant(); ?> <?php echo $clase; ?>">
        <div class="container">
            <div class="about-intro">
                <div class="about-text">
                    <span class="section-label"><?php ikg_value( 'titol_petit' ); ?></span>
                    <h1><?php ikg_value( 'titol_gran' ); ?></h1>
					<?php ikg_value( 'texte' ); ?>
                    <div class="about-highlight">
                        <p style="margin:0; font-weight: 500;">
                            <?php ikg_value( 'texte_resaltat' ); ?>
                        </p>
                    </div>
                </div>
                <div class="about-image">
                    <div class="about-image-placeholder">
                        <?php ikg_get_image( [ 'image_id' => 'imatge', 'class' => 'about-image', 'clase-picture' => 'about-picture' ] ); ?>
                    </div>
                </div>
            </div>
        </div>
    </section>