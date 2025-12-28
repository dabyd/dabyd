<?php
/**
 * Script de restauración de base de datos WordPress
 * Uso: Acceder desde el navegador a http://tudominio.com/__restore.php
 */

// Configuración de seguridad básica (opcional pero recomendado)
$SECRET_KEY = 'W8YqazV9ACzTqu6QRZYz9TcpJvRqXbDZ'; // Cambia esto por algo único

// Verificar clave de seguridad si está configurada
if ($SECRET_KEY !== 'W8YqazV9ACzTqu6QRZYz9TcpJvRqXbDZ') {
    if (!isset($_GET['key']) || $_GET['key'] !== $SECRET_KEY) {
        die('Acceso denegado');
    }
}

// Función para mostrar mensajes en HTML
function log_message($message, $type = 'info') {
    $colors = [
        'info' => '#2196F3',
        'success' => '#4CAF50',
        'warning' => '#FF9800',
        'error' => '#F44336'
    ];
    $color = $colors[$type] ?? $colors['info'];
    echo "<div style='padding: 10px; margin: 5px 0; background: {$color}; color: white; border-radius: 4px;'>{$message}</div>";
    flush();
    ob_flush();
}

// Función para encontrar wp-config.php
function findWpConfig() {
    $configPath = __DIR__ . '/wp-config.php';
    
    if (file_exists($configPath)) {
        log_message('✓ wp-config.php encontrado en carpeta actual', 'success');
        return $configPath;
    }
    
    $configPath = dirname(__DIR__) . '/wp-config.php';
    if (file_exists($configPath)) {
        log_message('✓ wp-config.php encontrado en carpeta superior', 'success');
        return $configPath;
    }
    
    log_message('✗ Error: No se encontró wp-config.php', 'error');
    return false;
}

// Función para extraer credenciales de wp-config.php
function getDbCredentials($configPath) {
    $config = file_get_contents($configPath);
    
    preg_match("/define\s*\(\s*['\"]DB_NAME['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/", $config, $dbName);
    preg_match("/define\s*\(\s*['\"]DB_USER['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/", $config, $dbUser);
    preg_match("/define\s*\(\s*['\"]DB_PASSWORD['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/", $config, $dbPass);
    preg_match("/define\s*\(\s*['\"]DB_HOST['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/", $config, $dbHost);
    preg_match("/\\\$table_prefix\s*=\s*['\"]([^'\"]+)['\"]/", $config, $tablePrefix);
    
    if (empty($dbName[1]) || empty($dbUser[1])) {
        log_message('✗ Error: No se pudieron extraer las credenciales de la base de datos', 'error');
        return false;
    }
    
    return [
        'name' => $dbName[1],
        'user' => $dbUser[1],
        'pass' => $dbPass[1] ?? '',
        'host' => $dbHost[1] ?? 'localhost',
        'prefix' => $tablePrefix[1] ?? 'wp_'
    ];
}

// HTML Header
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restauración de Base de Datos</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 30px;
        }
        .log-container {
            background: #1e1e1e;
            padding: 20px;
            border-radius: 4px;
            margin: 20px 0;
            max-height: 500px;
            overflow-y: auto;
        }
        button {
            background: #2196F3;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 20px;
        }
        button:hover {
            background: #1976D2;
        }
        button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 Restauración de Base de Datos</h1>
        
        <div class="warning-box">
            <strong>⚠️ Advertencia:</strong> Este proceso restaurará completamente tu base de datos. 
            Asegúrate de que el archivo dump.zip está en la misma carpeta que este script.
        </div>

<?php
// Si no se ha enviado la confirmación, mostrar botón
if (!isset($_POST['confirm'])) {
    ?>
    <form method="POST">
        <p>¿Estás seguro de que deseas restaurar la base de datos?</p>
        <button type="submit" name="confirm" value="1">Confirmar y Restaurar</button>
    </form>
    <?php
} else {
    // Iniciar proceso de restauración
    ?>
    <div class="log-container">
    <?php
    
    ob_start();
    
    log_message('🔄 Iniciando proceso de restauración...', 'info');
    
    // Verificar que existe dump.zip
    if (!file_exists(__DIR__ . '/dump.zip')) {
        log_message('✗ Error: No se encontró el archivo dump.zip', 'error');
        echo '</div></div></body></html>';
        exit;
    }
    
    log_message('✓ dump.zip encontrado', 'success');
    
    // Descomprimir dump.zip
    log_message('📦 Descomprimiendo dump.zip...', 'info');
    $zip = new ZipArchive();
    if ($zip->open(__DIR__ . '/dump.zip') === TRUE) {
        $zip->extractTo(__DIR__);
        $zip->close();
        log_message('✓ dump.zip descomprimido correctamente', 'success');
    } else {
        log_message('✗ Error: No se pudo descomprimir dump.zip', 'error');
        echo '</div></div></body></html>';
        exit;
    }
    
    // Verificar que existe dump.sql
    if (!file_exists(__DIR__ . '/dump.sql')) {
        log_message('✗ Error: No se encontró dump.sql después de descomprimir', 'error');
        echo '</div></div></body></html>';
        exit;
    }
    
    log_message('✓ dump.sql encontrado', 'success');
    
    // Obtener configuración de WordPress
    $configPath = findWpConfig();
    if (!$configPath) {
        echo '</div></div></body></html>';
        exit;
    }
    
    $db = getDbCredentials($configPath);
    if (!$db) {
        echo '</div></div></body></html>';
        exit;
    }
    
    log_message("📊 Base de datos: {$db['name']}", 'info');
    log_message("📊 Usuario: {$db['user']}", 'info');
    log_message("📊 Host: {$db['host']}", 'info');
    log_message("📊 Prefijo: {$db['prefix']}", 'info');
    
    // Conectar a la base de datos
    try {
        $pdo = new PDO(
            "mysql:host={$db['host']};dbname={$db['name']};charset=utf8mb4",
            $db['user'],
            $db['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        log_message('✓ Conectado a la base de datos', 'success');
    } catch (PDOException $e) {
        log_message('✗ Error de conexión: ' . $e->getMessage(), 'error');
        echo '</div></div></body></html>';
        exit;
    }
    
    // Guardar valores actuales de siteurl y home
    log_message('💾 Guardando valores actuales de siteurl y home...', 'info');
    $optionsTable = $db['prefix'] . 'options';
    
    try {
        $stmt = $pdo->prepare("SELECT option_value FROM {$optionsTable} WHERE option_name = 'siteurl'");
        $stmt->execute();
        $siteurl = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT option_value FROM {$optionsTable} WHERE option_name = 'home'");
        $stmt->execute();
        $home = $stmt->fetchColumn();
        
        if ($siteurl && $home) {
            log_message("✓ siteurl: {$siteurl}", 'success');
            log_message("✓ home: {$home}", 'success');
        } else {
            log_message('⚠️ No se encontraron valores previos (puede ser normal)', 'warning');
        }
    } catch (PDOException $e) {
        log_message('⚠️ Advertencia: ' . $e->getMessage(), 'warning');
        $siteurl = null;
        $home = null;
    }
    
    // Cerrar conexión PDO
    $pdo = null;
    
    // Restaurar dump.sql usando comando mysql
    log_message('🔄 Restaurando base de datos desde dump.sql...', 'info');
    
    $password = !empty($db['pass']) ? "-p{$db['pass']}" : "";
    $command = "mysql -u {$db['user']} {$password} -h {$db['host']} {$db['name']} < " . __DIR__ . "/dump.sql 2>&1";
    
    exec($command, $output, $returnCode);
    
    if ($returnCode === 0) {
        log_message('✓ Base de datos restaurada correctamente', 'success');
    } else {
        log_message('✗ Error al restaurar la base de datos', 'error');
        if (!empty($output)) {
            log_message(implode("\n", $output), 'error');
        }
        // Limpiar archivos
        @unlink(__DIR__ . '/dump.sql');
        @unlink(__DIR__ . '/dump.zip');
        log_message('❌ Proceso abortado', 'error');
        echo '</div></div></body></html>';
        exit;
    }
    
    // Reconectar para restaurar URLs
    if ($siteurl && $home) {
        try {
            $pdo = new PDO(
                "mysql:host={$db['host']};dbname={$db['name']};charset=utf8mb4",
                $db['user'],
                $db['pass'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            log_message('♻️ Restaurando URLs originales...', 'info');
            
            $stmt = $pdo->prepare("UPDATE {$optionsTable} SET option_value = :value WHERE option_name = 'siteurl'");
            $stmt->execute(['value' => $siteurl]);
            
            $stmt = $pdo->prepare("UPDATE {$optionsTable} SET option_value = :value WHERE option_name = 'home'");
            $stmt->execute(['value' => $home]);
            
            log_message('✓ URLs restauradas correctamente', 'success');
            log_message("   siteurl: {$siteurl}", 'success');
            log_message("   home: {$home}", 'success');
            
        } catch (PDOException $e) {
            log_message('⚠️ Advertencia: No se pudieron restaurar las URLs: ' . $e->getMessage(), 'warning');
        }
    }
    
    // Limpiar archivos
    log_message('🗑️ Limpiando archivos temporales...', 'info');
    
    if (unlink(__DIR__ . '/dump.sql')) {
        log_message('✓ dump.sql eliminado', 'success');
    } else {
        log_message('⚠️ No se pudo eliminar dump.sql', 'warning');
    }
    
    if (unlink(__DIR__ . '/dump.zip')) {
        log_message('✓ dump.zip eliminado', 'success');
    } else {
        log_message('⚠️ No se pudo eliminar dump.zip', 'warning');
    }
    
    log_message('✅ Proceso de restauración completado exitosamente', 'success');
    log_message('🎉 Tu base de datos ha sido restaurada como Gandalf rescatando a Helm del abismo.', 'success');
    
    ?>
    </div>
    
    <button onclick="location.reload()">Restaurar otra vez</button>
    <button onclick="window.close()">Cerrar</button>
    
    <?php
}
?>

    </div>
</body>
</html>