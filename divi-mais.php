<?php
/**
 * Plugin Name: Divi Mais
 * Description: Voltado para melhorias nas funcionalidades do divi.
 * Version:     1.0
 * Author:      Matheus Mathias
 * Text Domain: divi-mais
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 1) Enfileira o JS e passa a árvore de pastas para ele
add_action( 'print_media_templates', 'divi_mais_filebird_enqueue_media' );
function divi_mais_filebird_enqueue_media() {
    if ( ! class_exists( '\FileBird\Classes\Tree' ) ) {
        return;
    }

    // Se não estiver na tela do Divi Builder, sai
    if ( ! isset( $_GET['et_fb'] ) || $_GET['et_fb'] !== '1' ) {
        return;
    }
    
    $folders = \FileBird\Classes\Tree::getFolders( null );
    
    wp_enqueue_script(
        'divi-mais-folder-filter',
        plugin_dir_url( __FILE__ ) . '/assets/js/filebird-folder-filter.js',
        [ 'media-views' ],
        null,
        true
    );
    
    wp_localize_script( 'divi-mais-folder-filter', 'FilebirdFolders', [
        'folders' => $folders,
    ] );
}


// 2) Filtra a query de attachments via AJAX, usando os IDs que o FileBird Lite armazena
add_filter( 'ajax_query_attachments_args', 'divi_mais_filebird_filter_attachments', 10, 1 );
function divi_mais_filebird_filter_attachments( $args ) {
    // se não veio folder ou se o helper não existe, retorna sem mexer
    if ( empty( $_POST['query']['filebird_folder'] )
      || ! class_exists( '\FileBird\Classes\Helpers' ) ) {
        return $args;
    }

    $folder_id = intval( $_POST['query']['filebird_folder'] );

    // pega todos os attachment IDs daquela pasta
    $attachment_ids = \FileBird\Classes\Helpers::getAttachmentIdsByFolderId( $folder_id );

    if ( ! empty( $attachment_ids ) ) {
        // restringe a query só àqueles IDs
        $args['post__in'] = $attachment_ids;
    } else {
        // sem attachments: força retorno vazio
        $args['post__in'] = [ 0 ];
    }

    return $args;
}