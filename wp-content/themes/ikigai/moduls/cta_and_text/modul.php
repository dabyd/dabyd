<?php 
	// ikg_show_all_metas();
    $elements = ikg_get_acf_value('elements');
?>
    <!-- CTA and Text -->
    <section class="cta-and-text <?php ikg_get_variant(); ?>">
        <div class="container">
            <?php 
                for( $n = 0; $n < $elements; $n++ ) {
                    ikg_setbase( 'elements_' . $n . '_' );
                    $amplada = ikg_get_acf_value('amplada');
                    $tamany = ikg_get_acf_value('tamany');
                    $clase = trim( $amplada . ' ' . $tamany );

                    $tipus = ikg_get_acf_value( 'element' );
                    switch ( $tipus ) {
                        case '0':
                            // Capçalera
                            $element = ikg_get_acf_value('tipus_de_capcalera');
                            $alineacio = ikg_get_acf_value('alineacio');
                            echo '<' . $element . ' class="section-title ' . $alineacio . '">';
                            ikg_value( 'capcalera' );
                            echo '</' . $element . '>';
                            break;
                        case '1':
                            // Texte sense format
                            $alineacio = ikg_get_acf_value('alineacio');
                            echo '<p class="section-subtitle ' . $alineacio . '">';
                            echo ikg_get_text( 'texte_sense_format' );
                            echo '</p>';
                            break;
                        case '2':
                            // Texte amb format
                            $alineacio = ikg_get_acf_value('alineacio');
                            echo '<p class="section-subtitle ' . $alineacio . '">';
                            echo ikg_get_text( 'texte_amb_format' );
                            echo '</p>';
                            break;
                        case '3':
                            // Botons
                            ikg_setbase( 'elements_' . $n . '_botons_' );
                            echo '<div class="cta-and-text-buttons">';
                            ikg_put_buttons();
                            echo '</div>';
                            break;
                        case '4':
                            // Imatge                            
                            echo '<div class="cta-and-text-image ' . $clase . '">';
                            ikg_get_image( 'imatge' );
                            echo '</div>';
                            break;
                        case '5':
                            // Video
                            echo '<div class="cta-and-text-video ' . $clase . '">';
                            ikg_get_video( 'video' );
                            echo '</div>';
                            break;
                    }
                }
            ?>
        </div>
    </section>