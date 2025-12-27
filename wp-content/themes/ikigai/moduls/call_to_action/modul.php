<?php // ikg_show_all_metas(); ?>
    <!-- CTA Final Section -->
    <section class="cta-section <?php ikg_get_variant(); ?>">
        <div class="container">
            <div class="cta-content">
                <h2><?php echo ikg_get_acf_value( 'titol' ); ?></h2>
                <p><?php echo ikg_get_acf_value( 'texte' ); ?></p>
				<?php ikg_put_link( 'boto', 'btn btn-primary btn-large' ); ?>
                <p class="cta-note"><?php echo ikg_get_acf_value( 'texte_2' ); ?></p>
            </div>
        </div>
    </section>
