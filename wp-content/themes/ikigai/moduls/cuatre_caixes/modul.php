    <!-- Cuatre caixes -->
    <section class="pain-points <?php ikg_get_variant(); ?>">
        <div class="container">
            <h2 class="section-title"><?php echo ikg_get_acf_value( 'titol' ); ?></h2>
            <?php ikg_value( 'texte' ); ?>

            <div class="pain-grid">
				<?php
					$total = ikg_get_acf_value( 'blocs' );
					for( $n = 0; $n < $total; $n++ ):
				?>
                <div class="pain-card">
                    <div class="pain-icon"><?php echo ikg_get_acf_value( 'blocs_' . $n . '_icona' ); ?></div>
                    <h3><?php echo ikg_get_acf_value( 'blocs_' . $n . '_titol' ); ?></h3>
                    <p><?php echo ikg_get_acf_value( 'blocs_' . $n . '_texte' ); ?></p>
                </div>
                <?php endfor; ?>
            </div>
            <p class="pain-conclusion">
				<?php echo ikg_get_text( 'texte_de_peu' ); ?>
            </p>
        </div>
    </section>