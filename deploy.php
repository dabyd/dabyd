<?php
namespace Deployer;

require 'recipe/wordpress.php';

// Configuración del Repositorio
set('repository', 'https://github.com/dabyd/dabyd.git');

// Configuración del Servidor
host('omda.es')
    ->set('remote_user', 'root') 
    ->set('deploy_path', '/var/www/dabyd.com/deploy');

// Carpeta donde Nginx busca la web
set('current_path', '/var/www/dabyd.com/html');

// Archivos y carpetas compartidos
set('shared_files', ['wp-config.php', '.htaccess']);
set('shared_dirs', ['wp-content/uploads']);
set('writable_dirs', ['wp-content/uploads']);

/**
 * Tareas Personalizadas
 */

// 1. Enlace simbólico para que /html apunte a la release actual
task('deploy:link_html', function () {
    run('ln -sfn {{release_path}} {{current_path}}');
});

// 2. Limpieza de caché de Nginx
task('nginx:reload', function () {
    // Recarga la configuración y limpia caché interna si se usa fastcgi_cache
    run('systemctl reload nginx'); 
})->desc('Recargando Nginx...');

/**
 * Flujo de Despliegue Desglosado
 * Sustituimos 'deploy:publish' por sus tareas individuales
 */
desc('Desplegando WordPress...');
task('deploy', [
    'deploy:prepare',    // Crea carpetas, comprueba conexión
    'deploy:setup',      // Configura la estructura inicial
    'deploy:lock',       // Bloquea para que nadie más despliegue a la vez
    'deploy:release',    // Prepara la nueva carpeta de release
    'deploy:update_code',// Hace el git clone / pull
    'deploy:shared',     // Enlaza wp-config.php y uploads
    'deploy:writable',   // Ajusta permisos de escritura
    'deploy:symlink',    // Crea el enlace interno 'current' de Deployer
    'deploy:link_html',  // NUESTRO ENLACE: /html -> release_path
    'deploy:unlock',     // Desbloquea el deploy
    'deploy:cleanup',    // Borra releases antiguas (mantiene 5 por defecto)
    'deploy:success',    // Mensaje de éxito
]);

// Ejecutar limpieza de Nginx al finalizar todo
after('deploy:success', 'nginx:reload');

// Si algo falla, desbloquea para poder reintentar
after('deploy:failed', 'deploy:unlock');