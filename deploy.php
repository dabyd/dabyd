<?php
namespace Deployer;

require 'recipe/wordpress.php';

// Configuración del Repositorio
set('repository', 'https://github.com/dabyd/dabyd.git');

// Configuración del Servidor
host('omda.es')
    ->hostname('omda.es')
    ->user('root') 
    ->set('deploy_path', '/var/www/dabyd.com/deploy');

// Carpeta donde Nginx busca la web
set('current_path', '/var/www/dabyd.com/html');

// Archivos y carpetas compartidos
set('shared_files', ['wp-config.php', '.htaccess']);
set('shared_dirs', ['wp-content/uploads']);
set('writable_dirs', ['wp-content/uploads']);
// Configura el modo de escritura a 'chmod' o 'chown'
set('writable_mode', 'chmod');

/**
 * Tareas Personalizadas
 */

// Enlace simbólico para que /html apunte a la release actual
task('deploy:link_html', function () {
    run('ln -sfn {{release_path}} {{current_path}}');
});

// Limpieza de caché de Nginx
task('nginx:reload', function () {
    run('systemctl reload nginx'); 
})->desc('Recargando Nginx...');

// Tarea para corregir el dueño de los archivos
task('deploy:fix_permissions', function () {
    // Permisos en la release actual
    run('chown -R www-data:www-data {{release_path}}');
    
    // Permisos en la carpeta shared (donde está uploads realmente)
    run('chown -R www-data:www-data {{deploy_path}}/shared');
    
    // Asegurar que uploads sea escribible (755 para dirs, 644 para archivos)
    run('find {{deploy_path}}/shared/wp-content/uploads -type d -exec chmod 755 {} \;');
    run('find {{deploy_path}}/shared/wp-content/uploads -type f -exec chmod 644 {} \;');
});

// Añádela a tu lista de tareas justo antes del unlock
task('deploy', [
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:writable',
    'deploy:fix_permissions', // <--- Asegura que Nginx pueda leer
    'deploy:symlink',
    'deploy:link_html',
    'deploy:unlock',
    'cleanup',
]);

// Ejecutar recarga de Nginx después de desbloquear
after('deploy:unlock', 'nginx:reload');

// Si algo falla, desbloquea
after('deploy:failed', 'deploy:unlock');