    <?php 
		// ikg_show_all_metas(); 
		$form_id   = ikg_get_acf_value('formulario');
		$formulari = get_post($form_id );
		// ikg_show_all_metas($form_id);
		$tarjetes  = ikg_get_acf_value('tarjetas' , false, false, $form_id );
		$camps  = ikg_get_acf_value('campos_del_formulario' , false, false, $form_id );
		$texto_boton = ikg_get_acf_value('icono_del_boton', false, false, $form_id );
		$texto_boton .= ' ' . ikg_get_acf_value('titulo_del_boton', false, false, $form_id );
		$texto_boton = trim($texto_boton);
		if ( '' == $texto_boton ) {
			$texto_boton = 'Enviar solicitud';
		}
	?>

    <!-- Contact Content -->
    <section class="contact-content <?php ikg_get_variant(); ?>">
        <div class="container">
            <div class="contact-grid">
                <!-- Contact Info -->
                <div class="contact-info">
					<?php
						for( $n=0; $n < $tarjetes; $n++ ) :
							ikg_setbase( 'tarjetas_' . $n . '_' );
					?>
						<div class="info-card">
							<h2>
								<span class="info-icon"><?php ikg_value('icono', $form_id ); ?></span>
								<?php ikg_value('titulo', $form_id ); ?>
							</h2>
							<?php
								$elements = ikg_get_acf_value('elementos', false, false, $form_id );
								for ($i = 0; $i < $elements; $i++):
									ikg_setbase( 'tarjetas_' . $n . '_elementos_' . $i . '_' );
									switch ( ikg_get_acf_value('tipo', false, false, $form_id ) ) {
										case '0':
											// Texte
											ikg_value('texto', $form_id );
											break;

										case '1':
											// Mail
											echo '<p><a href="mailto:';
											ikg_value('e-mail', $form_id );
											echo '">';
											ikg_value('e-mail', $form_id );
											echo '</a></p>';
											break;

										case '2':
											// Teléfon
											echo '<p><a href="https://wa.me/';
											ikg_value('telefono', $form_id );
											echo '?text=Me%20gustar%C3%ADa%20reservar%20una%20cita" target="_blank">';
											echo ikg_format_phone_number( ikg_get_acf_value('telefono', false, false, $form_id ) );
											echo '</a></p>';
											break;

										case '3':
											// Ubicación
											echo '<p>';
											ikg_value('ubicacion', $form_id );
											echo '</p>';
											break;

										default:
											# code...
											break;
									}									
								endfor;
							?>
						</div>
					<?php endfor; ?>
                </div>

                <!-- Contact Form -->
                <div class="form-container">
                    <form id="contact-form" data-form-id="<?php echo esc_attr($form_id); ?>">
                        <input type="hidden" name="form_id" value="<?php echo esc_attr($form_id); ?>">
                        <?php wp_nonce_field('ikg_form_submit', 'ikg_form_nonce'); ?>
                        <div id="form-messages" class="form-messages"></div>
						<?php
							for( $n=0; $n < $camps; $n++ ) :
								ikg_setbase( 'campos_del_formulario_' . $n . '_' );
								$req = ikg_get_acf_value('obligatorio', false, false, $form_id );
								$tipo = ikg_get_acf_value('tipo_de_campo', false, false, $form_id);
								$label = ikg_get_acf_value('etiqueta_del_campo', false, false, $form_id);
								$placeholder = ikg_get_acf_value('placeholder', false, false, $form_id);
								$instrucciones = ikg_get_acf_value('instrucciones', false, false, $form_id);
								$opcion_defecto = ikg_get_acf_value('opcion_por_defecto', false, false, $form_id);
								if ( '' != $placeholder ) {
									$placeholder = ' placeholder="' . $placeholder . '"';
								}
								if ( '' != $instrucciones ) {
									$instrucciones = '<small>' . $instrucciones . '</small>';
								}
								$id = sanitize_title( $label );
								switch ( $tipo ) {
									case '0':
										// Cabecera (h2)
										echo '<h2>';
										ikg_value('cabecera', $form_id);
										echo '</h2>';
										break;

									case '1':
										// Texto informativo (wysiwyg - sólo mostrar texto)
										ikg_value('texto_informativo', $form_id);
										break;

									case '2':
										// Campo de Texto
										ikg_put_input_with_label('text', $id, $label, $req, $placeholder, $instrucciones);
										break;

									case '3':
										// Campo de E-mail
										ikg_put_input_with_label('email', $id, $label, $req, $placeholder, $instrucciones);
										break;

									case '4':
										// Campo de Teléfono
										ikg_put_input_with_label('tel', $id, $label, $req, $placeholder, $instrucciones);
										break;

									case '5':
										// Campo de Selector
										echo '<div class="form-group">';
										echo '<label for="' . $id . '">' . $label . ($req ? ' *' : '') . '</label>';
										echo '<select id="' . $id . '" name="' . $id . '" ' . ($req ? 'required' : '') . $placeholder .'>';
										if ( '' != $opcion_defecto ) {
											ikg_put_option($opcion_defecto);
										}
										$opciones = explode( PHP_EOL, ikg_get_acf_value('opciones', false, false, $form_id ) );
										foreach ( $opciones as $opcion ) {
											ikg_put_option($opcion);
										}
										echo '</select>';
										echo $instrucciones;
										echo '</div>';
										break;

									case '6':
										// Campo oculto
										ikg_put_input_with_label('hidden', $id, $label, $req, $placeholder, $instrucciones);
										break;

									case '7':
										// Campo de Textarea
										ikg_put_input_with_label('textarea', $id, $label, $req, $placeholder, $instrucciones);
										break;

								}
							endfor;
							ikg_reset();
						?>

                        <div class="form-checkbox">
                            <input type="checkbox" id="privacy" name="privacy" required>
                            <label for="privacy">
								<?php echo ikg_get_acf_value('frase_de_politica_de_privacidad_del_formulario', true ); ?>
                            </label>
                        </div>

                        <button type="submit" class="btn-submit"><?php echo $texto_boton; ?></button>

						<?php if ( '' != ikg_get_acf_value('texto_del_final_del_formulario', false, false, $form_id ) ) : ?>
							<p class="form-under-button">
								<?php echo ikg_get_acf_value('texto_del_final_del_formulario', false, false, $form_id ); ?>
							</p>
						<?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </section>