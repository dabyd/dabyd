<?php
//	ikg_show_all_metas();

	$titol_1 = ikg_get_acf_value('titol_esquerra');
	$texte_1 = ikg_get_acf_value('texte_esquerra');
	$titol_2 = ikg_get_acf_value('titol_dreta');
	$texte_2 = ikg_get_acf_value('texte_dreta');
	if ( ikg_get_acf_value( 'invertir_columnas' ) ) {
		$tmp = $titol_1;
		$titol_1 = $titol_2;
		$titol_2 = $tmp;
		$tmp = $texte_1;
		$texte_1 = $texte_2;
		$texte_2 = $tmp;
	}
?>
	<!-- evolution box -->
	<section class="evolution-box <?php ikg_get_variant(); ?>">
		<div class="container">
			<div class="evolution-grid">
				<div>
					<h2><?php echo $titol_1; ?></h2>
					<p class="section-subtitle"><?php echo nl2br( $texte_1 ); ?></p>
				</div>
				<div class="evolution-card">
					<h3><?php echo $titol_2; ?></h3>
					<p class="section-subtitle"><?php echo nl2br( $texte_2 ); ?></p>
				</div>
			</div>
		</div>
	</section>

	