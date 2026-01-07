	<?php 
        //ikg_show_all_metas(); 
        $clase = '';
        if ( ikg_get_acf_value('invertir') ) {
            $clase = 'is-reversed';
        }
        $titol_petit = ikg_get_acf_value('titol_petit');
        $titol_gran = ikg_get_acf_value('titol_gran');
        $texte = ikg_get_acf_value('texte');
        $texte_resaltat = ikg_get_acf_value('texte_resaltat');
    ?>
	<!-- Hero Section -->
    <section class="about-hero  <?php ikg_get_variant(); ?> <?php echo $clase; ?>">
        <div class="container">
            <div class="about-intro">
                <div class="about-text">
                    <?php if ( '' != $titol_petit ): ?>
                        <span class="section-label"><?php echo $titol_petit; ?></span>
                    <?php endif; ?>
                    <?php if ( '' != $titol_gran ): ?>
                        <h1><?php echo $titol_gran; ?></h1>
                    <?php endif; ?>
                    <?php if ( '' != $texte ): ?>
                        <?php echo $texte; ?>
                    <?php endif; ?>
                    <?php if ( '' != $texte_resaltat ): ?>
                        <div class="about-highlight">
                            <p style="margin:0; font-weight: 500;">
                                <?php echo $texte_resaltat; ?>
                            </p>
                        </div>
                    <?php endif; ?>
                    <div class="hero-buttons">
                        <?php ikg_put_buttons(); ?>
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