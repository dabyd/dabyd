<?php 
	// ikg_show_all_metas();

	$args = array(
		'post_type'      => 'servicios',
		'posts_per_page' => -1, // Traer todos
		'orderby'        => 'date',
		'order'          => 'ASC',
	);

	$servicios_query = new WP_Query($args);

	$params = [
		'aspecte' => '-',
		'auto-numerado' => true,
		'layout_invertit' => true,
		'servicio' => '1',
	];
	while ($servicios_query->have_posts()) {
		$servicios_query->the_post();
		$params['layout_invertit'] = !$params['layout_invertit'];
		$params['servicio'] = get_the_ID();
		ikg_invoque_module( 'servei', $params );
	}

	wp_reset_postdata();

?>
