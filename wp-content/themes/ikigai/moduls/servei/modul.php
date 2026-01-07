<?php 
	//ikg_show_all_metas();
	$servei = get_post(ikg_get_acf_value('servicio'));
	$img = get_post_thumbnail_id($servei->ID);
	$orden = ikg_get_post_position($servei->ID);
	$texto = 'Servicio ' . str_pad($orden, 2, '0', STR_PAD_LEFT); // Convierte "3" en "03"
	if ( 0 == ikg_get_acf_value('auto-numerado') ) {
		$texto = '';
	}
	$txt_inversion = 'Sesión de ' . $servei->post_title . ' (' . ikg_get_acf_value( 'duracion', false, false, $servei->ID ) . ')';
	$precio = ikg_get_acf_value('precio', false, false, $servei->ID);
	if ( '' == $precio ) {
		$precio = 'Consultar';
}
?>
    <!-- Terapias Manuales Section -->
    <section class="service-section <?php ikg_get_variant(); ?>" id="<?php echo get_post_field( 'post_name', $servei->ID ); ?>">
        <div class="container">            
            <div class="service-content">
				<?php if ( 0 == ikg_get_acf_value('layout_invertit') ) : ?>
					<div class="service-sticky">
						<span class="section-label"><?php echo $texto; ?></span>
						<h2 class="section-title"><?php echo $servei->post_title; ?></h2>
						<div class="service-image">
							<div class="service-image-placeholder"><?php ikg_get_image(['image_id'=>$img]); ?></div>
						</div>
					</div>
				<?php endif; ?>
                
                <div class="service-details">
                    <p class="section-subtitle">
						<?php ikg_value('texto_1', $servei->ID ); ?>
                    </p>

                    <h3><?php ikg_value('titulo_lista_1', $servei->ID ); ?></h3>
                    <ul>
						<?php
							$loop = ikg_get_acf_value( 'lista_1', false, false, $servei->ID );
						for ($n = 0; $n < $loop; $n++):
							ikg_setbase('lista_1_' . $n . '_');
						?>
                        	<li><strong><?php ikg_value( 'titulo', $servei->ID ); ?>:</strong> <?php ikg_value( 'detalle', $servei->ID ); ?></li>
						<?php endfor; ?>
						<?php ikg_setbase(''); ?>
                    </ul>

                    <h3><?php ikg_value('titulo_lista_2', $servei->ID ); ?></h3>
                    <div class="benefits-grid">
						<?php
							$loop = ikg_get_acf_value( 'lista_2', false, false, $servei->ID );
							for ($n = 0; $n < $loop; $n++):
								ikg_setbase('lista_2_' . $n . '_');
						?>
                        <div class="benefit-card">
                            <strong><?php ikg_value( 'titulo', $servei->ID ); ?>:</strong>
                            <p><?php ikg_value( 'detalle', $servei->ID ); ?></p>
                        </div>
						<?php endfor; ?>
						<?php ikg_setbase(''); ?>
                    </div>

					<?php if ( '' != ikg_get_acf_value( 'texto_2', false, false, $servei->ID ) ) { ?>
						<p><?php ikg_value( 'texto_2', $servei->ID ); ?></p>
					<?php } ?>

                    <div class="pricing-box">
                        <h4>Inversión en tu bienestar</h4>
                        <p><?php echo $txt_inversion; ?></p>
                        <div class="price"><?php echo $precio; ?></div>
                        <p class="section-subtitle">
                            Cada sesión se adapta a tus necesidades específicas
                        </p>
                        <a href="contacto.html" class="btn">Reserva tu sesión</a>
                    </div>
                </div>

				<?php if ( 1 == ikg_get_acf_value('layout_invertit') ) : ?>
					<div class="service-sticky">
						<span class="section-label"><?php echo $texto; ?></span>
						<h2 class="section-title"><?php echo $servei->post_title; ?></h2>
						<div class="service-image">
							<div class="service-image-placeholder"><?php ikg_get_image(['image_id'=>$img]); ?></div>
						</div>
					</div>
				<?php endif; ?>

            </div>
        </div>
    </section>
