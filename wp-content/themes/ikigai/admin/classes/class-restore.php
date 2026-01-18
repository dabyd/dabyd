<?php
/**
 * Clase para gestión de Copias de Seguridad y Restauración.
 * Integrado como funcionalidad del tema Ikigai.
 */

class IkigaiRestore {
    private $action = 'ikg_restore';
    private $backup_dir;

    public function __construct() {
        // Establecer directorio de backups: themes/ikigai/admin/backups
        $this->backup_dir = get_template_directory() . '/admin/backups';
        
        // Hooks
        add_action('admin_init', [$this, 'init_page']);
    }

    /**
     * Inicializa la página si se cumplen las condiciones
     */
    public function init_page() {
        if (!isset($_GET[$this->action]) || $_GET[$this->action] != '1') {
            return;
        }

        // Seguridad
        if (!is_user_logged_in() || !current_user_can('administrator')) {
            wp_die('Acceso denegado. Se requieren permisos de administrador.', 'Error 403', ['response' => 403]);
        }
        
        // Manejar envío de formularios
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['create_backup'])) {
                $this->handle_backup_creation($_POST['backup_type']);
            } elseif (isset($_POST['restore_confirm'])) {
                $this->handle_restoration();
            }
        }

        // Renderizar interfaz
        $this->render_interface();
        exit;
    }

    /**
     * Crea un backup según el tipo solicitado
     */
    private function handle_backup_creation($type) {
        // Asegurar que el directorio existe
        if (!file_exists($this->backup_dir)) {
            mkdir($this->backup_dir, 0755, true);
        }

        $filename = ($type === 'full') ? 'backup_full.zip' : 'dump.zip';
        $filepath = $this->backup_dir . '/' . $filename;
        $sql_file = $this->backup_dir . '/dump.sql';

        // 1. Generar SQL Dump
        $this->generate_sql_dump($sql_file);

        // 2. Crear ZIP
        $zip = new ZipArchive();
        if ($zip->open($filepath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            
            // Agregar SQL
            $zip->addFile($sql_file, 'dump.sql');
            
            // Agregar Uploads si es Full
            if ($type === 'full') {
                $upload_dir = wp_upload_dir();
                $this->add_folder_to_zip($upload_dir['basedir'], $zip, 'wp-content/uploads');
            }
            
            $zip->close();
            
            // Limpieza
            @unlink($sql_file);
            
            // Mensaje éxito (guardado en sesión o simplemente renderizamos con éxito)
             $this->render_notification("Copia de seguridad ({$type}) generada correctamente.", 'success');
        } else {
             $this->render_notification("Error al crear el archivo ZIP.", 'error');
        }
    }

    /**
     * Genera el volcado de la base de datos
     */
    private function generate_sql_dump($output_file) {
        $db_host = DB_HOST;
        $db_name = DB_NAME;
        $db_user = DB_USER;
        $db_pass = DB_PASSWORD;
        
        // Commando mysqldump
        // Nota: Ajustar path de mysqldump si es necesario en el servidor
        $cmd = "mysqldump -h " . escapeshellarg($db_host) . " -u " . escapeshellarg($db_user);
        if ($db_pass) {
            $cmd .= " -p" . escapeshellarg($db_pass);
        }
        $cmd .= " " . escapeshellarg($db_name) . " > " . escapeshellarg($output_file);
        
        exec($cmd, $output, $return_var);
        
        return $return_var === 0;
    }

    /**
     * Añade carpeta recursivamente al ZIP
     */
    private function add_folder_to_zip($dir, $zipArchive, $zipPath) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                // path relativo dentro del zip
                $relativePath = substr($filePath, strlen($dir) + 1);
                $zipArchive->addFile($filePath, $zipPath . '/' . $relativePath);
            }
        }
    }

    /**
     * Proceso de restauración
     */
    private function handle_restoration() {
        // Buffer de salida para el log en tiempo real
        $logs = [];
        
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/ikg_restore_temp';
        
        if (!file_exists($temp_dir)) {
            mkdir($temp_dir, 0755, true);
        }

        $zip_target = $temp_dir . '/restore_package.zip';
        $target_url = isset($_POST['target_url']) ? esc_url_raw($_POST['target_url']) : home_url();

        // 1. Subida
        if (isset($_FILES['backup_file']) && $_FILES['backup_file']['error'] === UPLOAD_ERR_OK) {
            move_uploaded_file($_FILES['backup_file']['tmp_name'], $zip_target);
            $logs[] = ['msg' => 'Archivo subido correctamente.', 'type' => 'success'];
        } else {
            $logs[] = ['msg' => 'Error en la subida del archivo.', 'type' => 'error'];
            $this->render_interface($logs);
            return;
        }

        // 2. Descomprimir
        $zip = new ZipArchive();
        if ($zip->open($zip_target) === TRUE) {
            $zip->extractTo(ABSPATH);
            $zip->close();
            $logs[] = ['msg' => 'Archivos extraídos correctamente en ABSPATH.', 'type' => 'success'];
        } else {
            $logs[] = ['msg' => 'Error al abrir el archivo ZIP.', 'type' => 'error'];
            $this->cleanup($temp_dir);
            $this->render_interface($logs);
            return;
        }

        // 3. Importar SQL
        $sql_file = ABSPATH . 'dump.sql';
        if (file_exists($sql_file)) {
            $db_host = DB_HOST;
            $db_name = DB_NAME;
            $db_user = DB_USER;
            $db_pass = DB_PASSWORD;
            
            $cmd = "mysql -h " . escapeshellarg($db_host) . " -u " . escapeshellarg($db_user);
            if ($db_pass) {
                $cmd .= " -p" . escapeshellarg($db_pass);
            }
            $cmd .= " " . escapeshellarg($db_name) . " < " . escapeshellarg($sql_file);
            
            exec($cmd, $output, $return_code);
            
            if ($return_code === 0) {
                $logs[] = ['msg' => 'Base de datos importada correctamente.', 'type' => 'success'];
                @unlink($sql_file);
            } else {
                $logs[] = ['msg' => 'Error al importar la base de datos.', 'type' => 'error'];
            }
        } else {
            $logs[] = ['msg' => 'No se encontró archivo dump.sql (Posiblemente restauración solo de archivos).', 'type' => 'warning'];
        }

        // 4. Reemplazo de URLs
        if ($target_url) {
            $this->replace_urls($target_url);
            $logs[] = ['msg' => "URLs actualizadas a: $target_url", 'type' => 'success'];
        }

        $this->cleanup($temp_dir);
        $logs[] = ['msg' => 'Restauración completada.', 'type' => 'success'];
        
        $this->render_interface($logs);
        exit; // Detener ejecución para mostrar logs limpio
    }

    /**
     * Reemplazar siteurl y home en la DB
     */
    private function replace_urls($new_url) {
        global $wpdb;
        // Re-inicializamos conexión por si acaso el import la cerró o confundió (raro en CLI pero posible)
        // Pero como estamos en el mismo script PHP, la conexión $wpdb sigue activa.
        // Lo que pasa es que los DATOS cambiaron por fuera.
        
        // Opción segura: UPDATE directo a opciones críticas
        $wpdb->query( $wpdb->prepare("UPDATE {$wpdb->options} SET option_value = %s WHERE option_name = 'siteurl'", $new_url) );
        $wpdb->query( $wpdb->prepare("UPDATE {$wpdb->options} SET option_value = %s WHERE option_name = 'home'", $new_url) );
    }

    private function cleanup($dir) {
        if (!is_dir($dir)) return;
        $files = glob($dir . '/*');
        foreach ($files as $file) {
            is_file($file) ? unlink($file) : false;
        }
        rmdir($dir);
    }
    
    private function render_notification($msg, $type) {
        echo "<script>alert('$msg');</script>"; // Simple fallback
    }

    /**
     * Obtener listado de backups existentes
     */
    private function get_existing_backups() {
        $backups = [];
        if (file_exists($this->backup_dir . '/dump.zip')) {
            $path = $this->backup_dir . '/dump.zip';
            $backups[] = [
                'name' => 'dump.zip',
                'type' => 'Base de Datos',
                'date' => date('d/m/Y H:i:s', filemtime($path)),
                'size' => $this->format_size(filesize($path)),
                'url'  => get_template_directory_uri() . '/admin/backups/dump.zip'
            ];
        }
        if (file_exists($this->backup_dir . '/backup_full.zip')) {
            $path = $this->backup_dir . '/backup_full.zip';
            $backups[] = [
                'name' => 'backup_full.zip',
                'type' => 'Completo (DB + Uploads)',
                'date' => date('d/m/Y H:i:s', filemtime($path)),
                'size' => $this->format_size(filesize($path)),
                'url'  => get_template_directory_uri() . '/admin/backups/backup_full.zip'
            ];
        }
        return $backups;
    }

    /**
     * Formatear tamaño de archivo
     */
    private function format_size($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } else {
            return number_format($bytes / 1024, 2) . ' KB';
        }
    }

    /**
     * Renderiza la interfaz completa
     */
    private function render_interface($logs = []) {
        $assets_uri = get_template_directory_uri() . '/admin';
        $backups = $this->get_existing_backups();
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Centro de Copias de Seguridad Ikigai</title>
            <link rel="stylesheet" href="<?php echo $assets_uri; ?>/css/backup-restore.css?v=<?php echo time(); ?>">
        </head>
        <body>
            <div class="ikg-backup-container">
                <header class="ikg-backup-header">
                    <?php if (!isset($_GET['iframe'])): ?>
                        <a href="<?php echo admin_url(); ?>" class="ikg-backup-btn-back">← Volver al WP Admin</a>
                    <?php endif; ?>
                    <h1>🛡️ Centro de Recuperación Ikigai</h1>
                    <p>Gestiona tus copias de seguridad y restaura el sistema</p>
                </header>

                <div class="ikg-backup-tabs">
                    <button class="ikg-backup-tab-btn ikg-backup-active" data-tab="tab-backup">
                        <span class="ikg-backup-icon">💾</span> Copias de Seguridad
                    </button>
                    <button class="ikg-backup-tab-btn" data-tab="tab-restore">
                        <span class="ikg-backup-icon">🔄</span> Restauración
                    </button>
                </div>

                <!-- TAB BACKUPS -->
                <div id="tab-backup" class="ikg-backup-tab-content ikg-backup-active">
                    <div class="ikg-backup-section-title">Generar nueva copia</div>
                    
                    <div class="ikg-backup-actions">
                        <form method="POST" class="ikg-backup-action-card">
                            <h3>Solo Base de Datos</h3>
                            <p>Genera un archivo SQL comprimido. Rápido y ligero.</p>
                            <input type="hidden" name="backup_type" value="db">
                            <button type="submit" name="create_backup" class="ikg-backup-btn ikg-backup-btn-primary">
                                Generar Dump (DB)
                            </button>
                        </form>

                        <form method="POST" class="ikg-backup-action-card">
                            <h3>Copia Completa</h3>
                            <p>Incluye Base de Datos y la carpeta de medios (uploads).</p>
                            <input type="hidden" name="backup_type" value="full">
                            <button type="submit" name="create_backup" class="ikg-backup-btn ikg-backup-btn-primary">
                                Generar Full Backup
                            </button>
                        </form>
                    </div>

                    <div class="ikg-backup-section-title">Copias Disponibles</div>
                    <?php if (empty($backups)): ?>
                        <div class="ikg-backup-empty-state">No hay copias de seguridad generadas aún.</div>
                    <?php else: ?>
                        <div class="ikg-backup-list">
                            <?php foreach ($backups as $backup): ?>
                                <div class="ikg-backup-item">
                                    <div class="ikg-backup-info">
                                        <span class="ikg-backup-name"><?php echo $backup['type']; ?> (<?php echo $backup['name']; ?>)</span>
                                        <span class="ikg-backup-date">📅 <?php echo $backup['date']; ?> | 📦 <?php echo $backup['size']; ?></span>
                                    </div>
                                    <a href="<?php echo $backup['url']; ?>" class="ikg-backup-btn ikg-backup-btn-download" download>
                                        ⬇ Descargar
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- TAB RESTORE -->
                <div id="tab-restore" class="ikg-backup-tab-content">
                    
                    <?php if (!empty($logs)): ?>
                        <div class="ikg-backup-log-container">
                            <?php foreach ($logs as $log): ?>
                                <div class="ikg-backup-log-entry ikg-backup-log-<?php echo $log['type']; ?>">
                                    > <?php echo $log['msg']; ?>
                                </div>
                            <?php endforeach; ?>
                            <div class="ikg-backup-log-entry">> Proceso finalizado.</div>
                        </div>
                        <br>
                    <?php endif; ?>

                    <div class="ikg-backup-warning-box">
                        <strong>⚠️ ZONA DE PELIGRO:</strong> Restaurar una copia sobrescribirá la base de datos actual. 
                        Asegúrate de tener un backup reciente antes de continuar.
                    </div>

                    <form method="POST" enctype="multipart/form-data" class="ikg-backup-restore-form">
                        <div class="ikg-backup-form-group">
                            <label>1. Archivo de respaldo (.zip)</label>
                            <input type="file" name="backup_file" required accept=".zip" class="ikg-backup-form-control">
                        </div>

                        <div class="ikg-backup-form-group">
                            <label>2. URL del Sistema (Opcional)</label>
                            <input type="url" name="target_url" class="ikg-backup-form-control" 
                                   value="<?php echo home_url(); ?>" 
                                   placeholder="https://ejemplo.com">
                            <small style="color: #666;">Se usarán para reemplazar las URLs en la base de datos tras la importación.</small>
                        </div>

                        <div style="text-align: right; margin-top: 20px;">
                            <button type="submit" name="restore_confirm" value="1" class="ikg-backup-btn ikg-backup-btn-primary" style="background-color: #EF4444;">
                                🚨 Iniciar Restauración
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <script src="<?php echo $assets_uri; ?>/js/backup-restore.js?v=<?php echo time(); ?>"></script>
        </body>
        </html>
        <?php
    }
}
