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
class DocumentLibraryWidget extends WP_Widget {

    function DocumentLibraryWidget() {
        parent::WP_Widget('documentlibrarywidget', $name = 'Document Library Widget');
    }

    function widget($args, $instance) {
        extract($args);
        $title = apply_filters('widget_title', $instance['title']);
        ?>
        <?php echo $before_widget; ?>
        <?php
        if ($title){
            echo $before_title . $title . $after_title;
        }
        ?>
        <div id="documents-page">
            <form action="<?php home_url( '/' ) ?>" method="get">
                <label for="dlsearch"> <?php _e('Search', 'document-library') ?>:</label>
                <input type="text" name="dlsearch"  value="<?php echo $_GET['dlsearch'] ?>" />
                <br/>
                <?php
                $fields = DocumentLibraryFields::getFieldCodes();
                foreach ($fields as $field) {
                    if (get_option('document-field-' . $field . '-active')) {
                        $columns[get_option('document-field-' . $field . '-slug')] = get_option('document-field-' . $field . '-name');
                        ?>

                        <label><?php echo get_option('document-field-' . $field . '-name') ?></label>
                        <?php
                        $args = array('orderby' => 'count', 'hide_empty' => false);
                        echo DocumentLibrary::getTermsDropdown(get_option('document-field-' . $field . '-slug'), $args);
                        ?>
                        <br/>
                        <?php
                    }
                }
                ?>
                <p><input class="search_submit" type="submit" value="<?php _e('Search', 'document-library') ?>" /></p>
            </form>
        </div>
        <?php echo $after_widget; ?>
        <?php
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        return $instance;
    }

    function form($instance) {
        $title = esc_attr($instance['title']);
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'document-library'); ?>:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <?php
    }

    public static function  titleWhere($where) {
        global $wp_query;

        if ($_GET['dlsearch']) {
            $where .= ' AND post_title LIKE \'%' . esc_sql(like_escape($_GET['dlsearch'])) . '%\'';
        }

        return $where;
    }
    public static function getTitle() {
        if (isset($_GET['dlsearch']) && $_GET['dlsearch']) {
            @header("HTTP/1.1 200 OK",1);
            @header("Status: 200 OK", 1);
            return $_GET['dlsearch'].' - ';
        }
        return __('Document Search','document-library').' - ';
    }
    public static function searchResult($atts) {

        if (stripos($_SERVER['REQUEST_URI'], '/?dlsearch=') === FALSE ) {
            return;
        }
        
        add_action('wp_title', array('DocumentLibraryWidget', 'getTitle'));
        get_header();
        ?>
        <div id="container">
            <div id="content" role="main">
                <?php

                add_filter('posts_where',array('DocumentLibraryWidget', 'titleWhere'));          

                /* extract(shortcode_atts(array(
                    'title' => '',
                    'perpage' => '1',
                    'order' => 'rand',
                        ), $atts));*/

                $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
                $paged = (int)$paged;
                $args = array(
                        'post_status' => 'publish',
                        'posts_per_page' => get_option('document-field-perpeage'),
                        'post_type' => 'document',
                        'paged'=>$paged
                );

                $fields = DocumentLibraryFields::getFieldCodes();
                foreach ($fields as $field) {
                    if (get_option('document-field-' . $field . '-active') && $_GET[get_option('document-field-' . $field . '-slug')]) {
                        $args[get_option('document-field-' . $field . '-slug')] = $_GET[get_option('document-field-' . $field . '-slug')];
                    }
                }

                $my_query = null;
                $my_query = new WP_Query($args);

                if(!$my_query->have_posts()) {
                    $my_query = null;
                    $paged = 1;
                    $args['paged'] = 1;
                    $my_query = new WP_Query($args);
                }
                function format_bytes($size) {
                    $units = array(' B', ' KB', ' MB', ' GB', ' TB');
                    for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
                    return intval($size).$units[$i];
                }

                if ($my_query->have_posts()) {
                    echo __("Results:", "document-library");
                    echo "<br>";
                    $searchUnit = get_option('document-field-search-result-unit');
                    while ($my_query->have_posts()) {
                        global $post;
                        $my_query->the_post();
                        $documentUrl = wp_get_attachment_url(get_post_meta($post->ID, 'document', true));

                        $size = filesize( get_attached_file( get_post_meta($post->ID, 'document', true) ) );
                        $size = format_bytes($size);

                        $fileExtArr = pathinfo(get_attached_file( get_post_meta($post->ID, 'document', true) ));
                        $fileExt = strtoupper($fileExtArr['extension']);

                        $keys = array('%TITLE%', '%SIZE%','%FILEEXT%');
                        $values = array(get_the_title(), $size,$fileExt);


                        /*foreach ($fields as $field) {
                         $a = get_term_by('id',(int)$post->ID,get_option('document-field-' . $field . '-slug'));

                         var_dump($a);
                         continue;;
                        if (get_option('document-field-' . $field . '-active')) {
                            $keys[] = strtoupper("%".get_option('document-field-' . $field . '-slug')."%");
                            $term = get_the_terms($post->ID, get_option('document-field-' . $field . '-slug'));
                            $values[] = $term;
                           }
                        }
                      exit;*/
                        $text = $searchUnit;


                        $text = str_replace($keys, $values, $text);

                        ?>
                        <a href="<?php echo $documentUrl; ?>" target="_blank" title="<?php the_title_attribute(); ?>"><?php echo  $text; ?></a><br/>
                        <?php
                    }
                    if($my_query->max_num_pages>1) {?>
                        <p class="navrechts">
                                            <?php
                                            if ($paged > 1) { ?>
                            <a href="<?php echo $_SERVER['REQUEST_URI'].'&paged=' . ($paged -1); //prev link ?>"><</a>
                                                <?php }
                                            for($i=1;$i<=$my_query->max_num_pages;$i++) {?>
                            <a href="<?php echo $_SERVER['REQUEST_URI'].'&paged=' . $i; ?>" <?php echo ($paged==$i)? 'class="selected"':'';?>><?php echo $i;?></a>
                                                <?php
                                            }
                                            if($paged < $my_query->max_num_pages) {?>
                            <a href="<?php echo $_SERVER['REQUEST_URI'].'&paged=' . ($paged + 1); //next link ?>">></a>
                                                <?php } ?>
                        </p>
                        <?php
                    }
                } else {
                    echo __("No Documents found", "document-library");
                }
                wp_reset_query();
                ?>
            </div>
        </div>
        <?php
        echo get_sidebar();

        get_footer();
        exit;

    }


}

?>
