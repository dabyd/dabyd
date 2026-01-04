<?php 
	//ikg_show_all_metas();
    $titol_gran = ikg_get_acf_value('titol_gran');
    $titol_petit = ikg_get_acf_value('titol_petit');
    $texte = ikg_get_acf_value('texte');
    $boto = ikg_get_acf_value('boto' );
?>
    <!-- Bloc de texte -->
    <section class="bloc-de-texte <?php ikg_get_variant(); ?>">
        <div class="container">
            <?php if( '' != $titol_petit ) : ?>
                <span class="section-label"><?php echo $titol_petit; ?></span>
            <?php endif; ?>
            <?php if( '' != $titol_gran ) : ?>
                <h2 class="section-title"><?php echo $titol_gran; ?></h2>
            <?php endif; ?>
            <?php if( '' != $texte ) : ?>
                <p class="section-subtitle"><?php echo $texte; ?></p>
            <?php endif; ?>
            <?php if( is_array( $boto ) ) : ?>
                <?php ikg_put_link( 'boto', 'cta-btn' ); ?>
            <?php endif; ?>
        </div>
    </section>