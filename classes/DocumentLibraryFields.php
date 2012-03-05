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
class DocumentLibraryFields {

    public static function getFieldCodes() {
        return array('dauthor', 'session', 'keyword', 'country', 'language', 'type');
    }

    public static function addSetings() {

        add_option('document-field-dauthor-name', 'Document Author');
        add_option('document-field-dauthor-singular-name', 'Document Author');
        add_option('document-field-dauthor-slug', 'document_author');
        add_option('document-field-dauthor-hierarchical', '1');
        add_option('document-field-dauthor-active', '1');

        add_option('document-field-session-name', 'Document Session');
        add_option('document-field-session-singular-name', 'Document Session');
        add_option('document-field-session-slug', 'document_session');
        add_option('document-field-session-hierarchical', '1');
        add_option('document-field-session-active', '1');

        add_option('document-field-keyword-name', 'Document Keyword');
        add_option('document-field-keyword-singular-name', 'Document Keyword');
        add_option('document-field-keyword-slug', 'document_keyword');
        add_option('document-field-keyword-hierarchical', '1');
        add_option('document-field-keyword-active', '1');

        add_option('document-field-country-name', 'Document Country');
        add_option('document-field-country-singular-name', 'Document Country');
        add_option('document-field-country-slug', 'document_country');
        add_option('document-field-country-hierarchical', '1');
        add_option('document-field-country-active', '1');

        add_option('document-field-language-name', 'Document Languages');
        add_option('document-field-language-singular-name', 'Document Language');
        add_option('document-field-language-slug', 'document_langauge');
        add_option('document-field-language-hierarchical', '1');
        add_option('document-field-language-active', '1');

        add_option('document-field-type-name', 'Document Type');
        add_option('document-field-type-singular-name', 'Document Type');
        add_option('document-field-type-slug', 'document_type');
        add_option('document-field-type-hierarchical', '1');
        add_option('document-field-type-active', '1');

        add_option('document-field-search-result-unit', '%TITLE% - %SIZE% - %FILEEXT%');
        add_option('document-field-perpeage', 10);

    }

    public static function manageFields() {
        $fields = self::getFieldCodes();
        if ($_POST) {
            $fields = DocumentLibraryFields::getFieldCodes();
            foreach ($fields as $field) {
                if (!empty($_POST['document-field-' . $field . '-name'])) {
                    update_option('document-field-' . $field . '-name', $_POST['document-field-' . $field . '-name']);
                }

                if (!empty($_POST['document-field-' . $field . '-singular-name'])) {
                    update_option('document-field-' . $field . '-singular-name', $_POST['document-field-' . $field . '-singular-name']);
                }

                if (!empty($_POST['document-field-' . $field . '-slug'])) {
                    update_option('document-field-' . $field . '-slug', $_POST['document-field-' . $field . '-slug']);
                }
                update_option('document-field-' . $field . '-hierarchical', (int) $_POST['document-field-' . $field . '-hierarchical']);

                update_option('document-field-' . $field . '-active', (int) $_POST['document-field-' . $field . '-active']);
            }
            update_option('document-field-search-result-unit', $_POST['document-field-search-result-unit']);
            if((int)$_POST['document-field-perpeage']) {
                update_option('document-field-perpeage', (int)$_POST['document-field-perpeage']);
            }else {
                update_option('document-field-perpeage', 10);
            }


        }
        ?>
        <div class="wrap">
            <h2>Fields</h2>

            <form method="post" action="">

                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Search result unit</th>
                        <td><input type="text" name="document-field-search-result-unit" value="<?php echo get_option('document-field-search-result-unit'); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Search results per page</th>
                        <td><input type="text" name="document-field-perpeage" value="<?php echo get_option('document-field-perpeage'); ?>" /></td>
                    </tr>
                            <?php
                            foreach ($fields as $key => $field) {
                                ?>

                    <tr valign="top">
                        <th scope="row">Field <?php echo $key + 1 ?> Name</th>
                        <td><input type="text" name="document-field-<?php echo $field ?>-name" value="<?php echo get_option('document-field-' . $field . '-name'); ?>" /></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Field <?php echo $key + 1 ?> Singular Name</th>
                        <td><input type="text" name="document-field-<?php echo $field ?>-singular-name" value="<?php echo get_option('document-field-' . $field . '-singular-name'); ?>" /></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Field <?php echo $key + 1 ?> Slug</th>
                        <td><input type="text" name="document-field-<?php echo $field ?>-slug" value="<?php echo get_option('document-field-' . $field . '-slug'); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Field <?php echo $key + 1 ?> Hierarchical</th>
                        <td><input type="checkbox" name="document-field-<?php echo $field ?>-hierarchical" value="1" <?php if (get_option('document-field-' . $field . '-hierarchical')
                                               )echo "checked='checked'" ?> /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Field <?php echo $key + 1 ?> Active</th>
                        <td><input type="checkbox" name="document-field-<?php echo $field ?>-active" value="1" <?php if (get_option('document-field-' . $field . '-active')
                                               )echo "checked='checked'" ?> /></td>
                    </tr>

                                <?php
                            }
                            ?>

                </table>

                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php _e('Save Changes','document-library') ?>" />
                </p>

            </form>
        </div>
        <?php
    }

}
?>
