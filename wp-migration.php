<?php
/**
 * WordPress Posts Export/Import Script
 * 
 * Este script permite exportar e importar posts de WordPress junto con sus metadatos,
 * renumerando automáticamente los IDs para evitar conflictos.
 * 
 * USO:
 * - Exportar: php wp_migration.php --mode=export --types=page,portfolio --output=dump.json
 * - Importar: php wp_migration.php --mode=import --input=dump.json
 * 
 * @author David Herrero Migration Tool
 * @version 1.0
 */

class WP_Posts_Migrator {
    
    private $db_host = 'localhost';
    private $db_name = 'nombre_base_datos';
    private $db_user = 'usuario';
    private $db_pass = 'contraseña';
    private $table_prefix = 'wp_';
    
    private $pdo;
    private $mode;
    private $post_types = [];
    private $output_file = 'wp_export.json';
    private $input_file = 'wp_export.json';
    
    /**
     * Constructor
     */
    public function __construct($config = []) {
        // Permitir configuración personalizada
        if (isset($config['db_host'])) $this->db_host = $config['db_host'];
        if (isset($config['db_name'])) $this->db_name = $config['db_name'];
        if (isset($config['db_user'])) $this->db_user = $config['db_user'];
        if (isset($config['db_pass'])) $this->db_pass = $config['db_pass'];
        if (isset($config['table_prefix'])) $this->table_prefix = $config['table_prefix'];
    }
    
    /**
     * Conectar a la base de datos
     */
    private function connect() {
        try {
            $dsn = "mysql:host={$this->db_host};dbname={$this->db_name};charset=utf8mb4";
            $this->pdo = new PDO($dsn, $this->db_user, $this->db_pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->log("✓ Conexión establecida con la base de datos", 'success');
        } catch (PDOException $e) {
            $this->log("✗ Error de conexión: " . $e->getMessage(), 'error');
            die();
        }
    }
    
    /**
     * Procesar argumentos de línea de comandos
     */
    public function parseArgs($args) {
        $options = [];
        
        foreach ($args as $arg) {
            if (strpos($arg, '--') === 0) {
                $arg = substr($arg, 2);
                $parts = explode('=', $arg, 2);
                $key = $parts[0];
                $value = isset($parts[1]) ? $parts[1] : true;
                $options[$key] = $value;
            }
        }
        
        return $options;
    }
    
    /**
     * Ejecutar el script
     */
    public function run($args) {
        $this->showHeader();
        
        $options = $this->parseArgs($args);
        
        // Validar modo
        if (!isset($options['mode'])) {
            $this->showUsage();
            die();
        }
        
        $this->mode = $options['mode'];
        $this->connect();
        
        if ($this->mode === 'export') {
            $this->runExport($options);
        } elseif ($this->mode === 'import') {
            $this->runImport($options);
        } else {
            $this->log("Modo no válido. Usa 'export' o 'import'", 'error');
            $this->showUsage();
        }
    }
    
    /**
     * Ejecutar exportación
     */
    private function runExport($options) {
        // Validar post types
        if (!isset($options['types'])) {
            $this->log("Debes especificar los post types con --types=type1,type2", 'error');
            die();
        }
        
        $this->post_types = explode(',', $options['types']);
        $this->post_types = array_map('trim', $this->post_types);
        
        if (isset($options['output'])) {
            $this->output_file = $options['output'];
        }
        
        $this->log("Iniciando exportación...", 'info');
        $this->log("Post types: " . implode(', ', $this->post_types), 'info');
        $this->log("Archivo de salida: {$this->output_file}", 'info');
        echo "\n";
        
        // Exportar posts
        $posts = $this->exportPosts();
        
        // Exportar postmeta
        $postmeta = $this->exportPostMeta($posts);
        
        // Guardar en archivo
        $this->saveExport($posts, $postmeta);
        
        $this->log("✓ Exportación completada con éxito", 'success');
        $this->log("Posts exportados: " . count($posts), 'info');
        $this->log("Metadatos exportados: " . count($postmeta), 'info');
    }
    
    /**
     * Ejecutar importación
     */
    private function runImport($options) {
        if (isset($options['input'])) {
            $this->input_file = $options['input'];
        }
        
        if (!file_exists($this->input_file)) {
            $this->log("El archivo {$this->input_file} no existe", 'error');
            die();
        }
        
        $this->log("Iniciando importación...", 'info');
        $this->log("Archivo de entrada: {$this->input_file}", 'info');
        echo "\n";
        
        // Leer datos
        $data = $this->loadImport();
        
        // Confirmar antes de importar
        $this->log("Se importarán " . count($data['posts']) . " posts y " . count($data['postmeta']) . " metadatos", 'warning');
        $this->log("¿Deseas continuar? (y/n): ", 'warning', false);
        
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        if (trim($line) != 'y') {
            $this->log("Importación cancelada", 'info');
            die();
        }
        
        // Importar
        $this->importPosts($data['posts'], $data['postmeta']);
        
        $this->log("✓ Importación completada con éxito", 'success');
    }
    
    /**
     * Exportar posts
     */
    private function exportPosts() {
        $placeholders = implode(',', array_fill(0, count($this->post_types), '?'));
        $sql = "SELECT * FROM {$this->table_prefix}posts 
                WHERE post_type IN ($placeholders)
                ORDER BY ID";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->post_types);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->log("→ Exportando posts...", 'info');
        $this->log("  Encontrados: " . count($posts) . " posts", 'info');
        
        return $posts;
    }
    
    /**
     * Exportar postmeta
     */
    private function exportPostMeta($posts) {
        if (empty($posts)) {
            return [];
        }
        
        $post_ids = array_column($posts, 'ID');
        $placeholders = implode(',', array_fill(0, count($post_ids), '?'));
        
        $sql = "SELECT * FROM {$this->table_prefix}postmeta 
                WHERE post_id IN ($placeholders)
                ORDER BY post_id, meta_id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($post_ids);
        $postmeta = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->log("→ Exportando postmeta...", 'info');
        $this->log("  Encontrados: " . count($postmeta) . " registros", 'info');
        
        return $postmeta;
    }
    
    /**
     * Guardar exportación en archivo
     */
    private function saveExport($posts, $postmeta) {
        $data = [
            'export_date' => date('Y-m-d H:i:s'),
            'post_types' => $this->post_types,
            'posts_count' => count($posts),
            'postmeta_count' => count($postmeta),
            'posts' => $posts,
            'postmeta' => $postmeta
        ];
        
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        if (file_put_contents($this->output_file, $json)) {
            $this->log("→ Datos guardados en {$this->output_file}", 'info');
            $size = filesize($this->output_file);
            $this->log("  Tamaño del archivo: " . $this->formatBytes($size), 'info');
        } else {
            $this->log("Error al guardar el archivo", 'error');
        }
    }
    
    /**
     * Cargar datos de importación
     */
    private function loadImport() {
        $json = file_get_contents($this->input_file);
        $data = json_decode($json, true);
        
        if (!$data) {
            $this->log("Error al leer el archivo JSON", 'error');
            die();
        }
        
        $this->log("→ Archivo cargado correctamente", 'info');
        $this->log("  Fecha de exportación: " . $data['export_date'], 'info');
        $this->log("  Post types: " . implode(', ', $data['post_types']), 'info');
        
        return $data;
    }
    
    /**
     * Importar posts con renumeración de IDs
     */
    private function importPosts($posts, $postmeta) {
        $id_mapping = []; // Mapeo de IDs antiguos a nuevos
        
        $this->pdo->beginTransaction();
        
        try {
            // 1. Importar posts
            $this->log("→ Importando posts...", 'info');
            $progress = 0;
            
            foreach ($posts as $post) {
                $old_id = $post['ID'];
                unset($post['ID']); // Dejar que MySQL asigne el nuevo ID
                
                // Preparar columnas y valores
                $columns = array_keys($post);
                $placeholders = array_fill(0, count($columns), '?');
                
                $sql = "INSERT INTO {$this->table_prefix}posts 
                        (" . implode(', ', $columns) . ") 
                        VALUES (" . implode(', ', $placeholders) . ")";
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(array_values($post));
                
                $new_id = $this->pdo->lastInsertId();
                $id_mapping[$old_id] = $new_id;
                
                $progress++;
                if ($progress % 10 == 0 || $progress == count($posts)) {
                    $this->log("  Progreso: $progress/" . count($posts) . " posts", 'info', true);
                }
            }
            
            echo "\n";
            
            // 2. Importar postmeta con IDs renumerados
            $this->log("→ Importando postmeta...", 'info');
            $progress = 0;
            
            foreach ($postmeta as $meta) {
                $old_post_id = $meta['post_id'];
                
                // Verificar que el post_id existe en el mapeo
                if (!isset($id_mapping[$old_post_id])) {
                    continue;
                }
                
                unset($meta['meta_id']); // Dejar que MySQL asigne el nuevo meta_id
                $meta['post_id'] = $id_mapping[$old_post_id]; // Usar el nuevo ID
                
                // Preparar columnas y valores
                $columns = array_keys($meta);
                $placeholders = array_fill(0, count($columns), '?');
                
                $sql = "INSERT INTO {$this->table_prefix}postmeta 
                        (" . implode(', ', $columns) . ") 
                        VALUES (" . implode(', ', $placeholders) . ")";
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(array_values($meta));
                
                $progress++;
                if ($progress % 50 == 0 || $progress == count($postmeta)) {
                    $this->log("  Progreso: $progress/" . count($postmeta) . " metadatos", 'info', true);
                }
            }
            
            echo "\n";
            
            // 3. Actualizar referencias entre posts (post_parent)
            $this->log("→ Actualizando referencias post_parent...", 'info');
            foreach ($id_mapping as $old_id => $new_id) {
                // Buscar si algún post importado tiene como parent este old_id
                foreach ($posts as $post) {
                    if ($post['post_parent'] == $old_id) {
                        $current_new_id = $id_mapping[$post['ID']];
                        $parent_new_id = $id_mapping[$old_id];
                        
                        $sql = "UPDATE {$this->table_prefix}posts 
                                SET post_parent = ? 
                                WHERE ID = ?";
                        $stmt = $this->pdo->prepare($sql);
                        $stmt->execute([$parent_new_id, $current_new_id]);
                    }
                }
            }
            
            $this->pdo->commit();
            
            // Mostrar mapeo de IDs
            $this->log("\n→ Mapeo de IDs (primeros 10):", 'info');
            $count = 0;
            foreach ($id_mapping as $old => $new) {
                $this->log("  ID antiguo: $old → ID nuevo: $new", 'info');
                $count++;
                if ($count >= 10) {
                    $this->log("  ... (y " . (count($id_mapping) - 10) . " más)", 'info');
                    break;
                }
            }
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->log("Error durante la importación: " . $e->getMessage(), 'error');
            throw $e;
        }
    }
    
    /**
     * Mostrar cabecera
     */
    private function showHeader() {
        echo "\n";
        echo "╔════════════════════════════════════════════════════════════╗\n";
        echo "║         WordPress Posts Export/Import Tool v1.0           ║\n";
        echo "║              by David Herrero Migration                    ║\n";
        echo "╚════════════════════════════════════════════════════════════╝\n";
        echo "\n";
    }
    
    /**
     * Mostrar uso
     */
    private function showUsage() {
        echo "USO:\n";
        echo "  Exportar:\n";
        echo "    php wp_migration.php --mode=export --types=page,portfolio --output=dump.json\n\n";
        echo "  Importar:\n";
        echo "    php wp_migration.php --mode=import --input=dump.json\n\n";
        echo "PARÁMETROS:\n";
        echo "  --mode=export|import    Modo de operación (obligatorio)\n";
        echo "  --types=type1,type2     Post types a exportar (obligatorio en export)\n";
        echo "  --output=archivo.json   Archivo de salida (por defecto: wp_export.json)\n";
        echo "  --input=archivo.json    Archivo de entrada (por defecto: wp_export.json)\n";
        echo "\n";
    }
    
    /**
     * Mostrar mensaje con color
     */
    private function log($message, $type = 'info', $sameLine = false) {
        $colors = [
            'success' => "\033[0;32m",
            'error' => "\033[0;31m",
            'warning' => "\033[0;33m",
            'info' => "\033[0;36m",
            'reset' => "\033[0m"
        ];
        
        $color = isset($colors[$type]) ? $colors[$type] : '';
        $reset = $colors['reset'];
        
        if ($sameLine) {
            echo "\r" . $color . $message . $reset;
        } else {
            echo $color . $message . $reset . "\n";
        }
    }
    
    /**
     * Formatear bytes
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

// ============================================================================
// CONFIGURACIÓN - EDITA ESTOS VALORES
// ============================================================================

$config = [
    'db_host' => 'localhost',
    'db_name' => 'wordpress_db',      // Nombre de tu base de datos
    'db_user' => 'root',               // Usuario de MySQL
    'db_pass' => '',                   // Contraseña de MySQL
    'table_prefix' => 'wp_'            // Prefijo de tablas de WordPress
];

// ============================================================================
// EJECUCIÓN DEL SCRIPT
// ============================================================================

if (php_sapi_name() !== 'cli') {
    die("Este script debe ejecutarse desde la línea de comandos.\n");
}

try {
    $migrator = new WP_Posts_Migrator($config);
    $migrator->run($argv);
} catch (Exception $e) {
    echo "\n\033[0;31m✗ Error fatal: " . $e->getMessage() . "\033[0m\n\n";
    exit(1);
}

echo "\n";
?>
