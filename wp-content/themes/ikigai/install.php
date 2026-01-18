<?php
/**
 * Script de Instalación de Ikigai
 * Acceder vía: /wp-content/themes/ikigai/install.php
 */

require_once 'admin/classes/class-installer.php';

$installer = new IkigaiInstaller();
$installer->run();
