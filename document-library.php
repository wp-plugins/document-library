<?php
/**
 * @package Document Library
 * @version 0.1
 */
/*
Plugin Name: Document Library
Plugin URI: http://wordpress.org/extend/plugins/document-library/
Description: 
Author: Hmayak Tigranyan
Version: 0.1
Author URI: http://hmayaktigranyan.com/
*/
require_once("classes/DocumentLibrary.php");
require_once("classes/DocumentLibraryFields.php");
require_once("classes/DocumentLibraryWidget.php");

register_activation_hook( __FILE__, array('DocumentLibrary', 'install') );
register_uninstall_hook( __FILE__, array('DocumentLibrary', 'uninstall') );
add_action( 'init', array('DocumentLibrary', 'install') );
add_action( 'init', array('DocumentLibraryFields', 'addSetings') );
add_action( 'init',  array('DocumentLibrary','createDocumentTaxonomies'), 0 );

add_action('admin_menu', array('DocumentLibrary', 'adminMenu'));

add_action("manage_posts_custom_column",array('DocumentLibrary', 'columnsValues'));
add_filter('manage_edit-document_columns', array('DocumentLibrary', 'addColumns'));

add_filter('query_vars',array('DocumentLibrary', 'addQueryVars') );

add_action('post_edit_form_tag', array('DocumentLibrary','addPostEnctype'));
add_action( 'add_meta_boxes', array('DocumentLibrary','addUplaodBox' ));
add_action( 'save_post',  array('DocumentLibrary','saveDocument' ));

add_action('widgets_init', create_function('', 'return register_widget("DocumentLibraryWidget");'));
add_action('template_redirect', array('DocumentLibraryWidget', 'searchResult'));

?>
