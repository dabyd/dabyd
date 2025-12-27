<?php // ikg_show_all_metas(); ?>
    <!-- Casos que acompaño Section -->
    <section class="conditions <?php ikg_get_variant(); ?>">
        <div class="container">
            <h2 class="section-title"><?php echo ikg_get_acf_value('titol'); ?></h2>
            <div class="conditions-grid">
                <?php
                	$total = ikg_get_acf_value('caixes');
                	for ($n = 0; $n < $total; $n++) : ?>
						<div class="condition-item">
							<span class="condition-icon"><?php echo ikg_get_acf_value('caixes_' . $n . '_icona'); ?></span>
							<p><?php echo ikg_get_acf_value('caixes_' . $n . '_texte'); ?></p>
						</div>
				<?php endfor; ?>
            </div>
        </div>
    </section>
