    <?php 
		// ikg_show_all_metas(); 
		$tarjetes = ikg_get_acf_value('tarjetes');
	?>

    <!-- Contact Content -->
    <section class="contact-content <?php ikg_get_variant(); ?>">
        <div class="container">
            <div class="contact-grid">
                <!-- Contact Info -->
                <div class="contact-info">
					<?php
						for( $n=0; $n < $tarjetes; $n++ ) :
							ikg_setbase( 'tarjetes_' . $n . '_' );
					?>
						<div class="info-card">
							<h2>
								<span class="info-icon"><?php ikg_value('icona'); ?></span>
								<?php ikg_value('titol'); ?>
							</h2>
							<?php
								$elements = ikg_get_acf_value('elements' );
								for ($i = 0; $i < $elements; $i++):
									ikg_setbase( 'tarjetes_' . $n . '_elements_' . $i . '_' );
									switch ( ikg_get_acf_value('tipus')) {
										case '0':
											// Texte
											ikg_value('texte');
											break;

										case '1':
											// Mail
											echo '<p><a href="mailto:';
											ikg_value('e-mail');
											echo '">';
											ikg_value('e-mail');
											echo '</a></p>';
											break;

										case '2':
											// Teléfon
											echo '<p><a href="https://wa.me/';
											ikg_value('telefon');
											echo '?text=Me%20gustar%C3%ADa%20reservar%20una%20cita" target="_blank">';
											ikg_value('telefon');
											echo '</a></p>';
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
                    <h2>Agenda tu sesión y empieza a sentir el cambio</h2>
					<p>Cada persona tiene un camino único hacia el bienestar. Si sientes que tu cuerpo, tus emociones o tu energía necesitan equilibrio, estoy aquí para acompañarte.</p>
					<p>Completa el formulario y cuéntame brevemente qué te gustaría trabajar o mejorar. Juntos encontraremos la terapia o combinación de técnicas que mejor se adapte a ti.</p>
					<p>Da el primer paso hacia una vida más consciente, equilibrada y plena.</p>
                    <form id="contact-form">
                        <div class="form-group">
                            <label for="name">Nombre completo *</label>
                            <input type="text" id="name" name="name" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">Teléfono</label>
                            <input type="tel" id="phone" name="phone">
                            <small>Opcional, pero ayuda para coordinar mejor la cita</small>
                        </div>

                        <div class="form-group">
                            <label for="service">¿De qué forma sientes que puedo acompañarte hoy? *</label>
                            <select id="service" name="service" required>
								<option value="">Selecciona una opción...</option>
								<option value="astrologia">Quiero conocerme mejor y entender mis ciclos (Astrología)</option>
								<option value="integral">Busco recuperar mi equilibrio y bienestar integral (Pack Integral)</option>
								<option value="coaching">Tengo un objetivo claro y necesito enfoque para lograrlo (Coaching)</option>
                            </select>
                        </div>

                        <div class="form-group">
							<label for="agenda">Preferencia de agenda (Sesiones de 2h aprox.)</label>
                            <select id="agenda" name="agenda" required>
								<option value="">¿Cuándo te vendría mejor?</option>
								<option value="manana">Mañanas (09:00 a 13:00)</option>
								<option value="tarde">Tardes (15:00 a 19:00)</option>
								<option value="indiferente">Me adapto a tu primer hueco libre</option>
							</select>
                        </div>

                        <div class="form-group">
                            <label for="message">Cuéntame brevemente qué te trae por aquí... *</label>
                            <textarea id="message" name="message" required placeholder="Describe brevemente tu situación, síntomas o lo que te gustaría trabajar..."></textarea>
                            <small>Esta información me ayudará a preparar mejor tu consulta</small>
                        </div>

                        <div class="form-checkbox">
                            <input type="checkbox" id="privacy" name="privacy" required>
                            <label for="privacy">
                                Acepto la política de privacidad y el tratamiento de mis datos 
                                personales para que David Herrero pueda contactarme.
                            </label>
                        </div>

                        <button type="submit" class="btn-submit">Enviar solicitud</button>

                        <p style="text-align: center; margin-top: var(--spacing-md); font-size: 0.9rem; color: var(--text-light);">
                            Mis servicios no sustituyen en ningún caso el consejo, diagnóstico o tratamiento médico o psicológico profesional. Al enviar este formulario, aceptas que este es un proceso de desarrollo personal y bienestar.
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </section>