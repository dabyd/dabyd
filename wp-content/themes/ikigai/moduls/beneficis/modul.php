<?php // ikg_show_all_metas(); ?>
    <!-- Proceso Section -->
    <section class="process-sectio <?php ikg_get_variant(); ?>">
        <div class="container">
            <span class="section-label"><?php ikg_value('titol_petit'); ?></span>
            <h2 class="section-title"><?php ikg_value('titol_gran'); ?></h2>
            <p class="section-subtitle">
                <?php ikg_value('subtitol'); ?>
            </p>

            <div class="process-steps">
				<?php
					$loop = ikg_get_acf_value( 'caixes' );
				for ($n = 0; $n < $loop; $n++):
					ikg_setbase('caixes_' . $n . '_');
				?>
					<div class="process-step">
						<div class="step-number"><?php echo $n+1; ?></div>
						<h3><?php ikg_value( 'titol' ); ?></h3>
						<p>
							<?php ikg_value( 'texte' ); ?>
						</p>
					</div>
                <?php endfor; ?>
            </div>
        </div>
    </section>