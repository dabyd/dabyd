<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<?php wp_head(); ?>
	</head>
	<body <?php body_class(); ?>>
		<!-- Header/Navigation -->
		<header class="header" id="header">
		<nav class="nav container">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="nav-logo">
				<?php
					$logo = ikg_get_option('logo');
					if ( '' != $logo ) {
						$logo = wp_get_attachment_image( $logo, 'full' );
					}
					else {
						$logo = bloginfo('name');
					}
					echo $logo;
				?>
			</a>
			<?php
				$menu_id = ikg_get_option('menu_header');
				$items = wp_get_nav_menu_items($menu_id);
				$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			?>
			<div class="nav-menu" id="nav-menu">
				<ul class="nav-list">
					<?php 
						if ($items) :
							foreach ($items as $item) :
								// Obtener el metadato "resaltado"
								$resaltado = get_post_meta($item->ID, 'resaltado', true);
								// Preparar las clases
								$classes = ['nav-link'];
								// Verificar si es la página actual
								if (rtrim($item->url, '/') === rtrim($current_url, '/') || 
									($item->url === home_url('/') && is_front_page())) {
									$classes[] = 'active';
								}
								// Verificar si tiene resaltado
								if ($resaltado == 1) {
									$classes[] = 'nav-cta';
								}
								$class_string = implode(' ', $classes);
								// Verificar si se abre en nueva ventana
								$target = $item->target ? ' target="' . esc_attr($item->target) . '"' : '';

								$class_string = implode(' ', $classes);

								// Verificar si se abre en nueva ventana
								$target = $item->target ? ' target="' . esc_attr($item->target) . '"' : '';

								echo '<li class="nav-item">';
								echo '<a href="' . esc_url($item->url) . '" class="' . esc_attr($class_string) . '"' . $target . '>';
								echo esc_html($item->title);
								echo '</a>';
								echo '</li>';
							endforeach;
						endif;
					?>
				</ul>
			</div>
			<div class="nav-toggle" id="nav-toggle">
				<span></span>
				<span></span>
				<span></span>
			</div>
		</nav>
	</header>