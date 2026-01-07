    <?php 
		// ikg_show_all_metas(); 
		$blocs = ikg_get_acf_value('blocs');
	?>
    <div class="legal-page-container  <?php ikg_get_variant(); ?>">
        <div class="legal-page-layout container">
            
            <!-- Sidebar - Table of Contents -->
            <aside class="legal-page-sidebar">
                <nav class="legal-page-toc">
                    <h2>Índice</h2>
                    <ul>
						<?php for( $n = 0; $n < $blocs; $n++ ): ?>
							<?php ikg_setbase('blocs_' . $n . '_' ); ?>
							<li><a href="#<?php echo sanitize_title( ikg_get_acf_value('titol') ); ?>"><?php echo ikg_get_acf_value('titol'); ?></a></li>
						<?php endfor; ?>
                    </ul>
                </nav>
            </aside>

            <!-- Main Content -->
            <main class="legal-page-main">
                
                <!-- Owner Section -->
                <section class="legal-page-section" id="owner">
                    <h2>Datos identificativos</h2>
                    <p><?php echo ikg_get_option('cabecera_legal' ); ?></p>
                    <div class="legal-page-contact-box">
                        <p><strong>Titular:</strong> <?php echo ikg_get_option( 'titular' ); ?></p>
                        <p><strong>NIF / DNI:</strong> <?php echo ikg_get_option( 'nifdni' ); ?></p>
                        <p><strong>Domicilio:</strong><br>
						<?php echo nl2br( ikg_get_option( 'direccio' ) ); ?>
                        <p><strong>Correo electrónico:</strong> <a href="mailto:<?php echo ikg_get_option( 'e-mail' ); ?>"><?php echo ikg_get_option( 'e-mail' ); ?></a></p>
                        <p><strong>Actividad:</strong> <?php echo ikg_get_option( 'actividad' ); ?></p>
                    </div>
                </section>

				<?php for( $n = 0; $n < $blocs; $n++ ): ?>
					<?php ikg_setbase('blocs_' . $n . '_' ); ?>
	                <section class="legal-page-section" id="<?php echo sanitize_title( ikg_get_acf_value('titol') ); ?>">
						<?php ikg_value('texte'); ?>
					</section>
				<?php endfor; ?>

                <!-- Action Section -->
                <div class="legal-page-action-section" id="actions">
                    <h2>¿Cómo te puedo ayudar?</h2>
                    <p>Puedes ejercer sus derechos contactándonos a través de los siguientes medios:</p>
                    
                    <div class="legal-page-action-grid">
                        <div class="legal-page-action-card">
                            <h3>📋<br>Acceder a sus datos</h3>
                            <p>Solicite conocer qué información tenemos sobre usted</p>
							<a href="mailto:<?php echo ikg_get_option( 'e-mail' ); ?>" class="legal-page-btn">Solicitar acceso</a>
                        </div>
                        
                        <div class="legal-page-action-card">
                            <h3>✏️<br>Corregir datos</h3>
                            <p>Rectifique información incorrecta que tengamos</p>
							<a href="mailto:<?php echo ikg_get_option( 'e-mail' ); ?>" class="legal-page-btn">Solicitar corrección</a>
                        </div>
                        
                        <div class="legal-page-action-card">
                            <h3>🗑️<br>Eliminar datos</h3>
                            <p>Ejercite su derecho al olvido</p>
                            <a href="mailto:<?php echo ikg_get_option( 'e-mail' ); ?>" class="legal-page-btn">Solicitar eliminación</a>
                        </div>
                        
                        <div class="legal-page-action-card">
                            <h3>📦<br>Portar datos</h3>
                            <p>Reciba sus datos en formato portátil</p>
                            <a href="mailto:<?php echo ikg_get_option( 'e-mail' ); ?>" class="legal-page-btn">Solicitar portabilidad</a>
                        </div>
                    </div>
                </div>

            </main>
            
        </div>
    </div>
