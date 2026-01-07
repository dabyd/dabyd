<?php

// 1. Registrar el Custom Post Type "Servicios"
function ikigai_register_cpt_servicios() {

    $labels = array(
        'name'                  => 'Servicios',
        'singular_name'         => 'Servicio',
        'menu_name'             => 'Servicios',
        'add_new'               => 'Añadir Nuevo',
        'add_new_item'          => 'Añadir Nuevo Servicio',
        'edit_item'             => 'Editar Servicio',
        'new_item'              => 'Nuevo Servicio',
        'view_item'             => 'Ver Servicio',
        'search_items'          => 'Buscar Servicios',
        'not_found'             => 'No se encontraron servicios',
        'not_found_in_trash'    => 'No hay servicios en la papelera',
    );

    $args = array(
        'label'                 => 'Servicio',
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt' ), // Título, contenido, imagen destacada y resumen
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-hammer', // Icono de martillo (puedes cambiarlo)
        'show_in_nav_menus'     => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true, // IMPORTANTE: Activa Gutenberg (el editor de bloques)
    );

    register_post_type( 'servicios', $args );
}
add_action( 'init', 'ikigai_register_cpt_servicios', 0 );


// 2. Registrar la Taxonomía "Tipo de Servicio" (Categoría propia)
function ikigai_register_taxonomia_servicios() {

    $labels = array(
        'name'              => 'Tipos de Servicio',
        'singular_name'     => 'Tipo de Servicio',
        'search_items'      => 'Buscar Tipos',
        'all_items'         => 'Todos los Tipos',
        'parent_item'       => 'Tipo Padre',
        'parent_item_colon' => 'Tipo Padre:', 
        'edit_item'         => 'Editar Tipo de Servicio',
        'update_item'       => 'Actualizar Tipo de Servicio',
        'add_new_item'      => 'Añadir Nuevo Tipo de Servicio',
        'new_item_name'     => 'Nombre del Nuevo Tipo',
        'menu_name'         => 'Categorías de Servicio',
    );

    $args = array(
        'hierarchical'      => true, // true para que se comporte como "Categorías", false como "Etiquetas"
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'tipo-servicio' ),
        'show_in_rest'      => true,
    );

    // La vinculamos al post type 'servicios'
    register_taxonomy( 'tipo-servicio', array( 'servicios' ), $args );
}
add_action( 'init', 'ikigai_register_taxonomia_servicios', 0 );

// 3. Registrar el Custom Post Type "Testimonios"
function ikigai_register_cpt_testimonios() {

    $labels = array(
        'name'                  => 'Testimonios',
        'singular_name'         => 'Testimonio',
        'menu_name'             => 'Testimonios',
        'add_new'               => 'Añadir Nuevo',
        'add_new_item'          => 'Añadir Nuevo Testimonio',
        'edit_item'             => 'Editar Testimonio',
        'new_item'              => 'Nuevo Testimonio',
        'view_item'             => 'Ver Testimonio',
        'search_items'          => 'Buscar Testimonios',
        'not_found'             => 'No se encontraron testimonios',
        'not_found_in_trash'    => 'No hay testimonios en la papelera',
    );

    $args = array(
        'label'                 => 'Testimonio',
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor', 'thumbnail' ), // Título (Nombre), Editor (Cita), Foto
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 6, // Justo debajo de Servicios
        'menu_icon'             => 'dashicons-testimonial', // Icono de testimonio
        'show_in_nav_menus'     => false,
        'has_archive'           => false,
        'exclude_from_search'   => true,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true, 
    );

    register_post_type( 'testimonios', $args );
}
add_action( 'init', 'ikigai_register_cpt_testimonios', 0 );

// 4. Registrar el Custom Post Type "Formularios"
function ikigai_register_cpt_formularios() {

    $labels = array(
        'name'                  => 'Formularios',
        'singular_name'         => 'Formulario',
        'menu_name'             => 'Formularios',
        'add_new'               => 'Añadir Nuevo',
        'add_new_item'          => 'Añadir Nuevo Formulario',
        'edit_item'             => 'Editar Formulario',
        'new_item'              => 'Nuevo Formulario',
        'view_item'             => 'Ver Formulario',
        'search_items'          => 'Buscar Formularios',
        'not_found'             => 'No se encontraron formularios',
        'not_found_in_trash'    => 'No hay formularios en la papelera',
    );

    $args = array(
        'label'                 => 'Formulario',
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor' ), // Título y contenido
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 7, // Justo debajo de Testimonios
        'menu_icon'             => 'dashicons-feedback', // Icono de formulario
        'show_in_nav_menus'     => false,
        'has_archive'           => false,
        'exclude_from_search'   => true,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true, 
    );

    register_post_type( 'formularios', $args );
}
add_action( 'init', 'ikigai_register_cpt_formularios', 0 );

// 5. Registrar el Custom Post Type "Submissions de Formulario" (interno)
function ikigai_register_cpt_submissions() {
    $labels = array(
        'name'                  => 'Formularios enviados',
        'singular_name'         => 'Formulario enviado',
        'menu_name'             => 'Formularios enviados',
        'add_new'               => 'Añadir Nueva',
        'add_new_item'          => 'Añadir Nueva Formulario enviado',
        'edit_item'             => 'Ver Formulario enviado',
        'view_item'             => 'Ver Formulario enviado',
        'search_items'          => 'Buscar Formularios enviados',
        'not_found'             => 'No se encontraron formularios enviados',
        'not_found_in_trash'    => 'No hay formularios enviados en la papelera',
    );

    $args = array(
        'label'                 => 'Submission',
        'labels'                => $labels,
        'supports'              => array( 'title' ),
        'hierarchical'          => false,
        'public'                => false,           // No público
        'show_ui'               => true,            // Visible en admin para debug
        'show_in_menu'          => false,           // NO en menú principal (lo mostramos como submenú de formularios)
        'menu_position'         => null,
        'menu_icon'             => 'dashicons-email-alt',
        'show_in_nav_menus'     => false,
        'has_archive'           => false,
        'exclude_from_search'   => true,
        'publicly_queryable'    => false,
        'capability_type'       => 'post',
        'show_in_rest'          => false,
    );

    register_post_type( 'ikg_submission', $args );
}
add_action( 'init', 'ikigai_register_cpt_submissions', 0 );