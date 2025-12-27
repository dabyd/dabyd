<?php
class IkigaiMediaClass {
	private $breakpoints = [];

	/**
	 * __construct Crea un objeto de media
	 * @return void
	 */
	public function __construct($values) {
		$default = $this->defaultValues();
		$obp = $default['breakpoints'];
		$cbp = array();
		if ( isset( $values['breakpoints'] ) ) {
			$cbp = $values['breakpoints'];
		}
		$values = array_merge( $this->defaultValues(), $values );
		$values['breakpoints'] = array_merge( $obp, $cbp );

		$this->breakpoints = $values['breakpoints'];
		add_filter('image_downsize', [$this, 'fix_svg_size_attributes'], 10, 2);
		add_action('after_setup_theme', [$this, 'theme_setup']);
		add_action('after_setup_theme', [$this, 'register_custom_image_sizes']);
		add_filter('jpeg_quality', [$this, 'custom_jpeg_quality'], 10, 2);
		if (!defined('ALLOW_UNFILTERED_UPLOADS')) {
			define('ALLOW_UNFILTERED_UPLOADS', true);
		}
		add_filter( 'upload_mimes', [$this, 'custom_mime_types'], 1, 1 );
	}

	public static function defaultValues() {
		return [
			'custom_jpeg_quality' 	=> '80',
			'breakpoints'			=> array(
				'thumbnail'     => array( 150, 150, true, 'core'),
				'medium'        => array( 300, 300, true, 'core'),
				'medium_large'  => array( 768, 0, true, 'core'),
				'large'         => array( 1024, 1024, true, 'core'),
				'1536x1536'     => array( 1536, 1536, false, 'core'),
				'2048x2048'     => array( 2048, 2048, false, 'core'),
			),
		];
	}

	public static function custom_jpeg_quality( $quality ) {
		$tmp = IkigaiMediaClass::defaultValues();
		return $tmp['custom_jpeg_quality'];
    }

	function register_custom_image_sizes() {
		// Obtener todos los tamaños ya registrados
		global $_wp_additional_image_sizes;

		// Ahora registramos solo los que no existan
		foreach ( $this->breakpoints as $name => $props) {
			if ( isset( $_wp_additional_image_sizes[ $name ] ) ) {
				continue;
			}

			list($width, $height, $crop, $origin ) = $props;

			// Añadir tamaño (altura 0, crop centrado si es true)
			add_image_size(
				$name,
				$width,
				$height,
				$crop ? array('center', 'center') : false
			);
		}
	}

	function showSizes() {
		echo '<table>';
		echo '<tr>';
		echo '<th>Nombre</th>';
		echo '<th>Width</th>';
		echo '<th>Height</th>';
		echo '<th>Crop?</th>';
		echo '<th>Type</th>';
		echo '</tr>';
		foreach ( $this->breakpoints as $name => $props) {
			echo '<tr>';
			echo '<td>' . $name . '</td>';
			foreach( $props as $value ) {
				echo '<td>' . $value . '</td>';
			}
			echo '</tr>';
		}
		echo '</table>';
	}

	function custom_mime_types($mime_types) {
		$mime_types['json'] = 'application/json';
		$mime_types['svg'] = 'image/svg+xml';
		$mime_types['svgz'] = 'image/svg+xml';
		return $mime_types;
	}

	/**
	 * Removes the width and height attributes of <img> tags for SVG
	 * 
	 * Without this filter, the width and height are set to "1" since
	 * WordPress core can't seem to figure out an SVG file's dimensions.
	 * 
	 * For SVG:s, returns an array with file url, width and height set 
	 * to null, and false for 'is_intermediate'.
	 * 
	 * @wp-hook image_downsize
	 * @param mixed $out Value to be filtered
	 * @param int $id Attachment ID for image.
	 * @return bool|array False if not in admin or not SVG. Array otherwise.
	 */
	public function fix_svg_size_attributes($out, $id) {
		$image_url  = wp_get_attachment_url($id);
		$file_ext   = pathinfo($image_url, PATHINFO_EXTENSION);
		if (is_admin() || 'svg' !== $file_ext) {
			return false;
		}
		return array($image_url, null, null, false, 'svg');
	}

	/**
	 * Enables post thumbnails.
	 */
	public function theme_setup() {
		add_theme_support('post-thumbnails');
	}

	function obtener_todos_los_tamanos_de_imagen($attachment_id) {
		if (!wp_attachment_is_image($attachment_id)) {
			return null;
		}

		$image_meta = wp_get_attachment_metadata($attachment_id);
		if ( is_array($image_meta) && !isset( $image_meta['file'] ) ) {
			$image_meta['file'] = get_post_meta( $attachment_id, '_wp_attached_file', true );
		}
		
		$upload_dir = wp_get_upload_dir();
		$base_url = $upload_dir['baseurl'];
		$base_dir = $upload_dir['basedir'];

		$file_base = dirname($image_meta['file']);
		$file_name = basename($image_meta['file']);

		$resultados = [];

		// Datos del post de adjunto
		$post = get_post($attachment_id);
		$alt_text = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
		$caption = $post->post_excerpt;
		$description = $post->post_content;

		// Tamaño original (full)
		$full_path = "{$base_dir}/{$file_base}/{$file_name}";
		$full_url = "{$base_url}/{$file_base}/{$file_name}";
		$mime_type = get_post_mime_type($attachment_id);
		if ( ! isset( $image_meta['width'] ) ) {
			$image_meta['width'] = 0;
		}
		if ( ! isset( $image_meta['height'] ) ) {
			$image_meta['height'] = 0;
		}

		$resultados['full'] = [
			'url' => $full_url,
			'file' => $full_path,
			'width' => $image_meta['width'],
			'height' => $image_meta['height'],
			'mime-type' => $mime_type,
			'alt' => $alt_text,
			'caption' => $caption,
			'description' => $description,
		];

		// Tamaños intermedios
		if (isset( $image_meta['sizes'] ) && !empty($image_meta['sizes'])) {
			foreach ($image_meta['sizes'] as $size => $data) {
				$path = "{$base_dir}/{$file_base}/{$data['file']}";
				$url = "{$base_url}/{$file_base}/{$data['file']}";
				$mime_type = mime_content_type($path);

				$resultados[$size] = [
					'url' => $url,
					'file' => $path,
					'width' => $data['width'],
					'height' => $data['height'],
					'mime-type' => $mime_type,
					'alt' => $alt_text,
					'caption' => $caption,
					'description' => $description,
				];
			}
		}

		// Buscar versiones con doble extensión (.jpg.webp y .jpg.avif)
		$versiones_extra = ['webp'];
		foreach ($resultados as $size => &$info) {
			foreach ($versiones_extra as $ext) {
				$extra_path = $info['file'] . '.' . $ext;
				if (file_exists($extra_path)) {
					$info[$ext] = [
						'file' => $extra_path,
						'url' => $info['url'] . '.' . $ext,
						'mime-type' => mime_content_type($extra_path),
					];
				}
			}
		}

		// Buscar versiones con doble extensión (.jpg.webp y .jpg.avif)
		$versiones_extra = ['avif'];
		foreach ($resultados as $size => &$info) {
			foreach ($versiones_extra as $ext) {
				$extra_path = preg_replace('/\.(jpg|jpeg|png)$/i', '.avif', $info['file']);
				$extra_url = preg_replace('/\.(jpg|jpeg|png)$/i', '.avif', $info['url']);
				if (file_exists($extra_path)) {
					$info[$ext] = [
						'file' => $extra_path,
						'url' => $extra_url,
						'mime-type' => mime_content_type($extra_path),
					];
				}
			}
		}
		return $resultados;
	}

	function put_svg(array $data) {
		if (!file_exists($data['file'])) {
			return ''; // SVG no encontrado
		}

		$svg = file_get_contents($data['file']);

		// Limpiar el xml del inicio si existe
		$svg = preg_replace('/<!--\?xml[^>]+-->\s*/', '', $svg);

		// Generar un ID único para accesibilidad
		$title_id = 'svg-title-' . uniqid();

		// Extraer contenido del SVG
		$dom = new DOMDocument();
		libxml_use_internal_errors(true); // suprimir warnings
		$dom->loadXML($svg);
		libxml_clear_errors();

		$svg_tag = $dom->getElementsByTagName('svg')->item(0);
		if (!$svg_tag) return ''; // No es un SVG válido

		// Set role="img" y aria-labelledby
		$svg_tag->setAttribute('role', 'img');

		// Crear o actualizar <title>
		if (!empty($data['alt'])) {
			$title_el = null;

			// Buscar si ya existe un <title>
			foreach ($svg_tag->childNodes as $child) {
				if ($child->nodeName === 'title') {
					$title_el = $child;
					break;
				}
			}

			if (!$title_el) {
				$title_el = $dom->createElement('title');
				$svg_tag->insertBefore($title_el, $svg_tag->firstChild);
			}

			$title_el->nodeValue = htmlspecialchars($data['alt']);
			$title_el->setAttribute('id', $title_id);
			$svg_tag->setAttribute('aria-labelledby', $title_id);
		}

		// Crear o actualizar <desc>
		if (!empty($data['description'])) {
			$desc_el = null;

			foreach ($svg_tag->childNodes as $child) {
				if ($child->nodeName === 'desc') {
					$desc_el = $child;
					break;
				}
			}

			if (!$desc_el) {
				$desc_el = $dom->createElement('desc');
				$svg_tag->insertBefore($desc_el, $svg_tag->getElementsByTagName('title')->item(0)->nextSibling);
			}

			$desc_el->nodeValue = htmlspecialchars($data['description']);
		}

		// Quitar espacios innecesarios y devolver como string limpio
		$dom->formatOutput = false;
		$result = $dom->saveXML($svg_tag);

		return $result;
	}

	/**
	 * Genera el contenido HTML de debug formateado
	 */
	private function generate_debug_content($valores, $imagenes = null) {
		$html = '<div class="sam-floating-content">';
		
		// Sección 1: Valores de entrada
		$html .= '<div class="meta-item">';
		$html .= '<h3 style="color: #50054c;">📥 Valores de entrada ($valores)</h3>';
		$html .= '<pre style="background: rgba(120, 12, 115, 0.05); padding: 10px; border-radius: 5px; border: 1px solid rgba(120, 12, 115, 0.2);">';
		$html .= htmlspecialchars(print_r($valores, true));
		$html .= '</pre>';
		$html .= '</div>';
		
		// Sección 2: Información de la imagen
		if ($imagenes) {
			$html .= '<div class="meta-item">';
			$html .= '<h3 style="color: #50054c;">🖼️ Información de la imagen ($imagenes)</h3>';
			
			// Estadísticas generales
			$total_sizes = count($imagenes);
			$has_webp = false;
			$has_avif = false;
			
			foreach ($imagenes as $size => $data) {
				if (isset($data['webp'])) $has_webp = true;
				if (isset($data['avif'])) $has_avif = true;
			}
			
			$html .= '<div style="background: rgba(120, 12, 115, 0.1); padding: 15px; border-radius: 5px; margin-bottom: 15px;">';
			$html .= '<strong style="color: #780c73;">📊 Estadísticas:</strong><br>';
			$html .= '• Total de tamaños generados: ' . $total_sizes . '<br>';
			$html .= '• Formato WebP disponible: ' . ($has_webp ? '✅ Sí' : '❌ No') . '<br>';
			$html .= '• Formato AVIF disponible: ' . ($has_avif ? '✅ Sí' : '❌ No') . '<br>';
			$html .= '</div>';
			
			// Detalle de cada tamaño
			foreach ($imagenes as $size_name => $data) {
				$html .= '<details style="margin-bottom: 15px;">';
				$html .= '<summary style="cursor: pointer; padding: 10px; background: rgba(120, 12, 115, 0.05); border-radius: 5px; font-weight: bold; color: #50054c;">';
				$html .= '📐 Tamaño: ' . esc_html($size_name);
				if (isset($data['width']) && isset($data['height'])) {
					$html .= ' (' . $data['width'] . 'x' . $data['height'] . ')';
				}
				$html .= '</summary>';
				
				$html .= '<div style="padding: 10px; margin-top: 10px; background: rgba(120, 12, 115, 0.02); border-radius: 5px;">';
				
				// URL
				if (isset($data['url'])) {
					$html .= '<div style="margin-bottom: 10px;">';
					$html .= '<strong style="color: #50054c;">🔗 URL:</strong><br>';
					$html .= '<code style="background: rgba(120, 12, 115, 0.1); padding: 5px; border-radius: 3px; font-size: 12px; word-break: break-all;">';
					$html .= esc_html($data['url']);
					$html .= '</code>';
					$html .= '</div>';
				}
				
				// Archivo
				if (isset($data['file'])) {
					$exists = file_exists($data['file']) ? '✅ Existe' : '❌ No existe';
					$html .= '<div style="margin-bottom: 10px;">';
					$html .= '<strong style="color: #50054c;">📁 Archivo:</strong> ' . $exists . '<br>';
					$html .= '<code style="background: rgba(120, 12, 115, 0.1); padding: 5px; border-radius: 3px; font-size: 12px; word-break: break-all;">';
					$html .= esc_html($data['file']);
					$html .= '</code>';
					$html .= '</div>';
				}
				
				// Dimensiones y mime-type
				$html .= '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">';
				if (isset($data['width'])) {
					$html .= '<div><strong style="color: #50054c;">📏 Width:</strong> ' . $data['width'] . 'px</div>';
				}
				if (isset($data['height'])) {
					$html .= '<div><strong style="color: #50054c;">📏 Height:</strong> ' . $data['height'] . 'px</div>';
				}
				$html .= '</div>';
				
				if (isset($data['mime-type'])) {
					$html .= '<div style="margin-bottom: 10px;">';
					$html .= '<strong style="color: #50054c;">📄 MIME Type:</strong> ';
					$html .= '<span style="background: rgba(120, 12, 115, 0.1); padding: 3px 8px; border-radius: 3px;">';
					$html .= esc_html($data['mime-type']);
					$html .= '</span>';
					$html .= '</div>';
				}
				
				// Versiones alternativas (WebP, AVIF)
				if (isset($data['webp']) || isset($data['avif'])) {
					$html .= '<div style="background: rgba(74, 165, 116, 0.1); padding: 10px; border-radius: 5px; margin-top: 10px;">';
					$html .= '<strong style="color: #2F5940;">🚀 Versiones optimizadas:</strong><br>';
					
					if (isset($data['webp'])) {
						$webp_exists = file_exists($data['webp']['file']) ? '✅' : '❌';
						$html .= '<div style="margin-top: 5px;">';
						$html .= $webp_exists . ' <strong>WebP:</strong><br>';
						$html .= '<code style="font-size: 11px; word-break: break-all;">' . esc_html($data['webp']['url']) . '</code>';
						$html .= '</div>';
					}
					
					if (isset($data['avif'])) {
						$avif_exists = file_exists($data['avif']['file']) ? '✅' : '❌';
						$html .= '<div style="margin-top: 5px;">';
						$html .= $avif_exists . ' <strong>AVIF:</strong><br>';
						$html .= '<code style="font-size: 11px; word-break: break-all;">' . esc_html($data['avif']['url']) . '</code>';
						$html .= '</div>';
					}
					
					$html .= '</div>';
				}
				
				$html .= '</div>'; // Fin del contenido del details
				$html .= '</details>';
			}
			
			$html .= '</div>'; // Fin meta-item imagenes
		}
		
		// Sección 3: Preview visual (solo si hay URL)
		if ($imagenes && isset($imagenes['full']['url'])) {
			$html .= '<div class="meta-item">';
			$html .= '<h3 style="color: #50054c;">👁️ Preview visual</h3>';
			$html .= '<div style="text-align: center; background: rgba(120, 12, 115, 0.05); padding: 20px; border-radius: 5px;">';
			$html .= '<img src="' . esc_url($imagenes['full']['url']) . '" style="max-width: 100%; height: auto; border-radius: 5px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);" alt="Preview">';
			$html .= '</div>';
			$html .= '</div>';
		}
		
		$html .= '</div>'; // Fin sam-floating-content
		
		return $html;
	}

	function img( $valores ) {
		$base = [
			'image_id'      => '',
			'dummy'         => false,
			'width'         => 1024,
			'height'        => 1024,
			'back-color'    => '000',
			'text-color'    => 'fff',
			'clase-en-img'  => true,
			'clase-picture' => true,
			'data-full'     => false,
			'src'           => '',
			'alt'           => '',
			'default-size'  => 'full',
			'area-hidden'   => 'true',
			'class'         => '',
			'clase'         => '',
			'force-img'     => false,
			'echo'          => true,
			'debug'         => false,
		];

		$output = '';

		if ( ! is_array( $valores ) ) {
			if ( is_numeric( $valores ) ) {
				$valores = [ 'image_id' => $valores ];
			} else {
				if ( is_string( $valores ) ) {
					if ( preg_match('#^(/|\.{1,2}/|[a-zA-Z]:\\\\)#', $valores ) ) {
						$valores = [ 'src' => $valores ];
					} else {
						$valores = [ 'image_id' => ikg_get_acf_value( $valores ) ];
					}
				}
			}
		}
		$valores = array_merge( $base, $valores );

		$valores['class'] = trim( $valores['class'] . ' ' . $valores['clase'] );
		unset( $valores['clase'] );

		// MODO DEBUG: Mostrar en ventana flotante
		if ( $valores['debug'] ) {
			// Verificar que IkigaiFloatingWindow existe
			if (!class_exists('IkigaiFloatingWindow')) {
				// Fallback al método anterior si la clase no existe
				echo '<pre>';
				echo '<h1>$valores</h1>';
				print_r( $valores );
				echo '</pre>';
			} else {
				$imagenes = null;
				if ($valores['image_id']) {
					$imagenes = $this->obtener_todos_los_tamanos_de_imagen( $valores['image_id'] );
				}
				
				$debug_content = $this->generate_debug_content($valores, $imagenes);
				
				$window = new IkigaiFloatingWindow(
					'🖼️ Debug: Imagen #' . ($valores['image_id'] ?: 'N/A'),
					$debug_content,
					[
						'width' => '900px',
						'height' => '700px',
						'icon' => '🐛',
						'position' => ['top' => '50px', 'right' => '50px'],
						'contentClass' => 'sam-content-debug',
						'headerClass' => 'sam-header-custom',
						'closable' => true,
						'minimizable' => true,
						'maximizable' => true,
						'resizable' => true
					]
				);
				
				$window->show();
			}
		}

		$clase_img = '';
		$clase_pic = '';
		if ( $valores['clase-picture'] ) {
			$clase_pic = ' class="' . $valores['clase-picture'] . '" ';
		}

		if ( $valores['class'] ) {
			if ( $valores['clase-en-img'] ) {
				$clase_img = ' class="' . $valores['class'] . '" ';
			} else {
				$clase_pic = ' class="' . $valores['class'] . '" ';
			}
		}
		$output = '';
		$alt = '';
		$area_hidden = '';
		$data_full = '';
		if ( $valores['dummy'] ) {
			$src = 'https://dummyimage.com/';
			$src .= $valores['width'] . 'x' . $valores['height'];
			$src .= '/' . $valores['back-color'] . '/' . $valores['text-color'];
			$src .= '&text=Imagen+de+relleno+(' . $valores['width'] . 'px+x' . $valores['height'] . 'px)';
			if ( '' == $valores['alt'] ) {
				$valores['alt'] = htmlspecialchars( $default['alt'] ?? '', ENT_QUOTES );				
			}
			if ( '' == $valores['alt'] ) {
				$area_hidden = ' area-hidden="true"';
				$valores['area-hidden'] = 'true';
			} else {
				$alt = ' alt="' . $valores['alt'] . '"';
			}
			if ( $valores['data-full'] ) {
				$data_full = ' data-full="' . esc_attr($default['url']) . '"';
			}
			$output .= '<img src="' . esc_url( $src ) . '"' . $clase_img . $alt . $area_hidden . $data_full . ' loading="lazy">';
			$valores['image_id'] = '';
			$valores['src'] = '';
		}

		if ( '' != $valores['image_id'] && 0 != $valores['image_id'] ) {
			$valores['src'] = '';

			if ( is_string( $valores['image_id'] ) ) {
				if ( preg_match('#^(/|\.{1,2}/|[a-zA-Z]:\\\\)#', $valores['image_id'] ) ) {
					$valores['src'] = $valores['image_id'];
					$valores['image_id'] = '';
				} else {
					$valores['image_id'] = ikg_get_acf_value( $valores['image_id'] );
				}
			}

			if ('' != $valores['image_id'] && 0 != $valores['image_id'] ) {
				$imagenes = $this->obtener_todos_los_tamanos_de_imagen( $valores['image_id'] );

				$default = $imagenes[ $valores['default-size'] ];
				unset( $imagenes[ $valores['default-size'] ] );

				if ( 'image/svg+xml' == $default['mime-type'] ) {
					// Es SVG
					$output = $this->put_svg( $default );
				} else {
					// Es una imagen normal
					$alt = '';
					$area_hidden = '';
					if ( '' == $valores['alt'] ) {
						$valores['alt'] = htmlspecialchars( $default['alt'] ?? '', ENT_QUOTES );				
					}
					if ( '' == $valores['alt'] ) {
						$area_hidden = ' area-hidden="true"';
						$valores['area-hidden'] = 'true';
					} else {
						$alt = ' alt="' . $valores['alt'] . '"';
					}

					$data_full = '';
					if ( $valores['data-full'] ) {
						$data_full = ' data-full="' . esc_attr($default['url']) . '"';
					}

					if ( ! $valores['force-img'] ) {
						$output .= '<picture' . $clase_pic . '>';

						foreach( $imagenes as $name => $imagen ) {
							// Media query automática en función del ancho
							$media = '(max-width: ' . $imagen['width'] . 'px)';

							// avif
							if (isset($imagen['avif'])) {
								$output .= '<source srcset="' . esc_url($imagen['avif']['url']) . '" type="image/avif" media="' . $media . '">';
							}

							// webp
							if (isset($imagen['webp'])) {
								$output .= '<source srcset="' . esc_url($imagen['webp']['url']) . '" type="image/webp" media="' . $media . '">';
							}

							// jpg (fallback por tamaño)
							$output .= '<source srcset="' . esc_url($imagen['url']) . '" type="' . esc_attr($imagen['mime-type']) . '" media="' . $media . '">';
						}
					}

					// Fallback: full
					$output .= '<img src="' . esc_url($default['url']) . '"' . $clase_img . $alt . $area_hidden . $data_full . ' width="' . esc_attr($default['width']) . '" loading="lazy">';

					if ( ! $valores['force-img'] ) {
						$output .= "</picture>\n";
					}
				}
			} else {

				$valores['src'] = get_template_directory_uri() . '/imatges/sense-imatge.jpg';
			}
		} else {
			if ( 0 == $valores['image_id'] ) {
				$valores['src'] = get_template_directory_uri() . '/imatges/sense-imatge.jpg';
			}
		}

		if ( '' != $valores['src'] ) {
			$src = $valores['src'];			
			$pattern = '#^/?static/elements#';
			if (preg_match($pattern, $src)) {
				$src = preg_replace($pattern, '/wp-content/themes/petals/front/dist/static/elements', $src, 1);
			} else {
				$pattern = '#^/static/shared/images#';
				if (preg_match($pattern, $src)) {
					$src = preg_replace($pattern, '/wp-content/themes/petals/front/dist/static/shared/images', $src, 1);
				}
			}				
			$output .= '<img src="' . esc_url($src) . '"' . $clase_img . $alt . $area_hidden . $data_full . ' loading="lazy">';
		}

		if ( $valores['echo'] ) {
			echo $output;
			$output = '';
		}

		return $output;
	}
}