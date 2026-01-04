    <?php // ikg_show_all_metas(); ?>

    <!-- Sección FAQ -->
    <section class="faq <?php ikg_get_variant(); ?>">
        <div class="container">
            <div class="faq-container">
                <span class="section-label"><?php echo ikg_get_acf_value( 'titol_petit' ); ?></span>
                <h2 class="section-title"><?php echo ikg_get_acf_value( 'titol_gran' ); ?></h2>
                <p class="section-subtitle"><?php echo ikg_get_acf_value( 'subtitol' ); ?></p>
                
                <div class="faq-list">
                    <?php $loop = ikg_get_acf_value('blocs'); ?>
                    <?php for( $n = 0; $n < $loop; $n++ ): ?>
                        <div class="faq-item">
                            <button class="faq-question" aria-expanded="false">
                                <span><?php echo ikg_get_acf_value('blocs_' . $n . '_icona' ); ?> <?php echo ikg_get_acf_value('blocs_' . $n . '_titol' ); ?></span>
                                <span class="faq-icon">+</span>
                            </button>
                            <div class="faq-answer">
                                <div class="faq-answer-content">
                                    <?php echo ikg_get_text( 'blocs_' . $n . '_texte' ); ?>
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </section>