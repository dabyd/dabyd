    <!-- Mi Enfoque Section -->
    <section class="approach <?php ikg_get_variant(); ?>">
        <div class="container">
            <div class="approach-content">
                <div class="approach-text">
                    <span class="section-label"><?php echo ikg_get_acf_value('titol_petit'); ?></span>
                    <h2 class="section-title"><?php echo ikg_get_acf_value('titol_gran'); ?></h2>
                    <p class="approach-description">
						<?php echo ikg_get_text('texte'); ?>
                    </p>
					<?php ikg_put_link('boto','btn btn-outline'); ?>
                </div>
                <div class="approach-features">
					<?php $loop = ikg_get_acf_value('caixes'); ?>
					<?php for( $n=0; $n<$loop; $n++) : ?>
	                    <div class="feature-card">
    	                    <h4><?php echo ikg_get_acf_value('caixes_' . $n . '_icona') . ' ' . ikg_get_acf_value('caixes_' . $n . '_titol'); ?></h4>
        	                <p><?php echo ikg_get_acf_value('caixes_' . $n . '_texte'); ?></p>
            	        </div>
					<?php endfor; ?>
                </div>
            </div>
        </div>
    </section>