<?php
namespace Deployer;

require 'recipe/wordpress.php'; // Esto carga automáticamente las tareas de WP

// Configuración del Repositorio
set('repository', 'https://github.com/dabyd/dabyd.git');

// Configuración del Servidor
host('omda.es')
    ->set('remote_user', 'root') // Cambia por tu usuario de Ubuntu
    ->set('deploy_path', '/var/www/dabyd.com/deploy'); // La raíz del sitio

// [IMPORTANTE] Carpeta donde Nginx busca la web
// Deployer creará un enlace simbólico de /html hacia la última versión
set('current_path', '/var/www/dabyd.com/html');

// Archivos que NO se borran ni se sobrescriben al desplegar
set('shared_files', ['wp-config.php', '.htaccess']);
set('shared_dirs', ['wp-content/uploads']);

// Permisos de escritura
set('writable_dirs', ['wp-content/uploads']);

// Tarea para enlazar la carpeta /html con la versión actual de Deployer
task('deploy:link_html', function () {
    run('ln -sfn {{release_path}} {{current_path}}');
});

// Orden de ejecución
desc('Desplegando WordPress...');
task('deploy', [
    'deploy:prepare',
    'deploy:publish',
]);

// Después de crear el enlace simbólico interno, actualizamos el de /html
after('deploy:symlink', 'deploy:link_html');

// Si algo falla, desbloquea para poder reintentar
after('deploy:failed', 'deploy:unlock');