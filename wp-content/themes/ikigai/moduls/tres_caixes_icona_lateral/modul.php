    <!-- Formación y Certificaciones Section -->
    <section class="certifications <?php ikg_get_variant(); ?>">
        <div class="container">
            <span class="section-label"><?php ikg_value( 'titol_petit' ); ?></span>
            <h2 class="section-title"><?php ikg_value( 'titol_gran' ); ?></h2>
            <p class="section-subtitle">
                <?php ikg_value( 'texte' ); ?>
            </p>

            <div class="cert-list">
                <?php
                    $total = ikg_get_acf_value( 'blocs' );
					for( $n = 0; $n < $total; $n++ ):
                        ikg_setbase('blocs_' . $n . '_');
				?>
                    <div class="cert-item">
                        <div class="cert-icon"><?php ikg_value( 'icona' ); ?></div>
                        <div>
                            <strong><?php ikg_value( 'titol' ); ?></strong>
                            <p style="margin:0; color: var(--text-light); font-size: 0.9rem;"><?php ikg_value( 'texte' ); ?></p>
                        </div>
                    </div>
                <?php 
                    endfor;
                    ikg_setbase('');
                ?>
            </div>
        </div>
    </section>