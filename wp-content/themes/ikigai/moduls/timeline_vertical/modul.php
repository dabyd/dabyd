<?php 
	//ikg_show_all_metas();
?>
    <!-- Mi Camino Section -->
    <section class="journey <?php ikg_get_variant(); ?>">
        <div class="container">
            <span class="section-label"><?php ikg_value('titol_petit' ); ?></span>
            <h2 class="section-title"><?php ikg_value('titol_gran' ); ?></h2>
            <p class="section-subtitle"><?php ikg_value('texte' ); ?></p>
            <div class="journey-timeline">
				<?php 
					$items = ikg_get_acf_value('hites');
					for ($n = 0; $n < $items; $n++):
						ikg_setbase('hites_' . $n . '_');
				?>
						<div class="journey-item">
							<h3><?php ikg_value('titol' ); ?></h3>
							<p><?php ikg_value('texte' ); ?></p>
						</div>
				<?php 
					endfor;
					ikg_setbase('');
				?>
            </div>
        </div>
    </section>