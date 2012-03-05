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
class DocumentLibrary {
    
    public static function install() {

        if(!post_type_exists('document')) {
            $labels = array(
                    'name' => _x('Documents', 'post type general name'),
                    'singular_name' => _x('Document', 'post type singular name'),
                    'add_new' => _x('Add New', 'document'),
                    'add_new_item' => __('Add New Document'),
                    'edit_item' => __('Edit Document'),
                    'new_item' => __('New Document'),
                    'view_item' => __('View Document'),
                    'search_items' => __('Search Documents'),
                    'not_found' => __('No documents found'),
                    'not_found_in_trash' => __('No documents found in Trash'),
                    'parent_item_colon' => '',
                    'menu_name' => 'Documents'
            );
            $args = array(
                    'labels' => $labels,
                    'public' => true,
                    'publicly_queryable' => true,
                    'show_ui' => true,
                    'show_in_menu' => true,
                    'query_var' => true,
                    'rewrite' => true,
                    'capability_type' => 'post',
                    'has_archive' => true,
                    'hierarchical' => false,
                    'menu_position' => null,
                    'supports' => array('title', 'author')
            );
            register_post_type('document', $args);
        }
    }

    public static function uninstall() {

    }
    
    public static function createDocumentTaxonomies() {

        $fields = DocumentLibraryFields::getFieldCodes();
        foreach($fields as $field) {
            if(get_option('document-field-'.$field.'-active')) {
                $labels = array(
                        'name' => get_option('document-field-'.$field.'-name'),
                        'singular_name' => get_option('document-field-'.$field.'-singular-name'),
                        'search_items' => __('Search '.get_option('document-field-'.$field.'-name')),
                        'all_items' => __('All'),
                        'parent_item' => null,
                        'parent_item_colon' => null,
                        'edit_item' => __('Edit '.get_option('document-field-'.$field.'-name')),
                        'update_item' => __('Update '.get_option('document-field-'.$field.'-name')),
                        'add_new_item' => __('Add New '.get_option('document-field-'.$field.'-name')),
                        'new_item_name' => __('New '.get_option('document-field-'.$field.'-name').' Name'),
                        'menu_name' => get_option('document-field-'.$field.'-name'),
                );

                register_taxonomy(get_option('document-field-'.$field.'-slug'), array('document'), array(
                        'hierarchical' => get_option('document-field-'.$field.'-hierarchical'),
                        'labels' => $labels,
                        'show_ui' => true,
                        'query_var' => true,
                        'rewrite' => array('slug' => get_option('document-field-'.$field.'-slug')),
                ));
            }

        }


    }

    public static function addColumns($columns) {
        $columns = array(
                "cb" => "<input type=\"checkbox\" />",
                "title" => "Title",
        );

        $fields = DocumentLibraryFields::getFieldCodes();
        foreach($fields as $field) {
            if(get_option('document-field-'.$field.'-active')) {
                $columns[$field] = get_option('document-field-'.$field.'-name');
            }
        }

        return $columns;


    }

    public static function columnsValues($column) {
        global $post;
        echo get_the_term_list( $post->ID , get_option('document-field-'.$column.'-slug') , '' , ',' , '' );
    }

    public static function addQueryVars($qvars) {
        $fields = DocumentLibraryFields::getFieldCodes();
        $qvars[] = ' dlsearch';
        foreach($fields as $field) {
            if(get_option('document-field-'.$field.'-active')) {
                $qvars[] = get_option('document-field-'.$field.'-slug') ;
            }
        }

        return $qvars;
    }

    public static function adminMenu() {
        add_submenu_page('edit.php?post_type=document', __('Manage Fields', 'document-library'), __('Manage Fields', 'document-library'), 'manage_options', 'document-fields', array('DocumentLibraryFields', 'manageFields') );
    }

    public static function getTermsDropdown($taxonomy, $args) {
        $taxonomies = array($taxonomy);
        $myterms = get_terms($taxonomies, $args);
        $output = "<select name='" . $taxonomy . "'>";
        $output .="<option value=''>"._x('Search', 'document-library')."</option>";
        foreach ($myterms as $term) {

            $term_slug = $term->slug;
            $term_name = $term->name;
            $selected = "";
            if ($term->slug == $_GET[$taxonomy]) {
                $selected = "selected='selected'";
            }
            $output .="<option value='" . $term->slug . "' " . $selected . ">" . $term->name . "</option>";
        }
        $output .="</select>";
        return $output;
    }

    public static function addPostEnctype() {
        echo ' enctype="multipart/form-data"';
    }

    public static function addUplaodBox() {
        add_meta_box("documentInput", "Attach Document",  array('DocumentLibrary',"meta_options"), "document", "normal", "high");
    }

    public static function meta_options() {
        global $post;
        wp_nonce_field( plugin_basename( __FILE__ ), 'documentlibrary_noncename' );

        $document = wp_get_attachment_url(get_post_meta($post->ID,'document',true));
        if($document){
            ?>
            <label> <?php _e("Current Document", 'document-library' );?>:</label>
            <a href="<?php echo $document;?>" target="_blank"><?php echo $document;?></a>
            <br/><br/>

            <?php
        }
        ?>

        <label>
        <?php _e("Document", 'myplugin_textdomain' );?>:
        </label>
        <input type="file" name="document" value="" />

        <?php
    }


    public static function saveDocument() {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
          return;
        if ( !wp_verify_nonce( $_POST['documentlibrary_noncename'], plugin_basename( __FILE__ ) ) )
          return;

        if($_FILES["document"]) {
            global $post;

            $overrides = array( 'test_form' => false);
            $file = wp_handle_upload($_FILES["document"],$overrides);
            if(isset($file['file'])) {

                $title = basename($_FILES["document"]['name']);

                $title = explode('.', $title);
                array_pop($title);
                $title = implode(".", $title);
                $attachment = array(
                    'post_mime_type' => $_FILES['document']['type'],
                    'post_title' => addslashes($title),
                    'post_content' => '',
                    'post_status' => 'inherit',
                    'post_parent' => $post->ID
                );

                $attacId = wp_insert_attachment( $attachment, $file['file'] );

                $existingDocument = (int) get_post_meta($post->ID,'document', true);
                if(is_numeric($existingDocument)) {
                    wp_delete_attachment($existingDocument);
                }

                update_post_meta($post->ID, "document", $attacId);
            }
        }
    }

}

?>
