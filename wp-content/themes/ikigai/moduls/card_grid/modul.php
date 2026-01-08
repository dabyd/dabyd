<?php
	$total = ikg_get_acf_value('caixes');
?>
	<!-- card grid -->
	<section class="card-grid <?php ikg_value('posicio_caixes') ?> <?php ikg_get_variant(); ?>">
		<h2 class="section-title"><?php ikg_value('titol'); ?></h2>
		<div class="container">
			<?php for( $n = 0; $n < $total; $n++ ) : ?>
				<?php ikg_setbase( 'caixes_' . $n . '_' ); ?>
				<div class="card">
					<h3><?php ikg_value('titol'); ?></h3>
					<p class="section-subtitle"><?php echo ikg_get_text('texte'); ?></p>
				</div>
			<?php endfor; ?>
		</div>
	</section>
