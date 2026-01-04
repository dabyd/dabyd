    <!-- Mi Filosofía Section (tres caixes) -->
    <?php // ikg_show_all_metas(); ?>
    <section class="philosophy <?php ikg_get_variant(); ?>">
        <div class="container">
            <span class="section-label"><?php ikg_value( 'titol_petit' ); ?></span>
            <h2 class="section-title"><?php ikg_value( 'titol_gran' ); ?></h2>
            <p class="section-subtitle"><?php ikg_value( 'texte' ); ?></p>
            <div class="philosophy-grid">
                <?php
                    $total = ikg_get_acf_value( 'blocs' );
					for( $n = 0; $n < $total; $n++ ):
                        ikg_setbase('blocs_' . $n . '_');
				?>
                    <div class="philosophy-card">
                        <div class="philosophy-icon"><?php ikg_value('icona') ; ?></div>
                        <h3><?php ikg_value('titol') ; ?></h3>
                        <p>
                            <?php ikg_value('texte') ; ?>
                        </p>
                    </div>
                <?php 
                    endfor;
                    ikg_setbase('');
                ?>    
            </div>
        </div>
    </section>