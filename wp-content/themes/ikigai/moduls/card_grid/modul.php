<?php
	$total = ikg_get_acf_value('caixes');
?>
	<!-- card grid -->
	<section class="card-grid <?php ikg_value('posicio_caixes') ?> <?php ikg_get_variant(); ?>">
		<?php if ( ikg_has_value('titol') ) : ?>
			<h2 class="section-title"><?php ikg_value('titol'); ?></h2>
		<?php endif; ?>
		<div class="container">
			<?php for( $n = 0; $n < $total; $n++ ) : ?>
				<?php ikg_setbase( 'caixes_' . $n . '_' ); ?>
				<div class="card">
					<?php if ( ikg_has_value('titol') ) : ?>						
						<h3><?php ikg_value('titol'); ?></h3>
					<?php endif; ?>
					<?php if ( ikg_has_value('texte') ) : ?>
						<p class="section-subtitle"><?php ikg_value('texte'); ?></p>
					<?php endif; ?>
				</div>
			<?php endfor; ?>
		</div>
	</section>
