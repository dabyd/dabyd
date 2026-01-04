<?php
	$total = ikg_get_acf_value('caixes');
?>
	<!-- card grid -->
	<section class="container card-grid <?php ikg_value('posicio_caixes') ?> <?php ikg_get_variant(); ?>">
		<?php for( $n = 0; $n < $total; $n++ ) : ?>
			<?php ikg_setbase( 'caixes_' . $n . '_' ); ?>
			<div class="card">
				<h3><?php ikg_value('titol'); ?></h3>
				<p class="section-subtitle"><?php echo ikg_get_text('texte'); ?></p>
			</div>
		<?php endfor; ?>
	</section>
