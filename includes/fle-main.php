<?php

if(!class_exists('FrontendLinkEditor')) { 

class FrontendLinkEditor {
    function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'fle_enqueue_plugin_scripts']);
        add_action( 'wp_head', [$this, 'fle_render_link_edit_form']);
        add_filter('the_content', [$this, 'fle_edit_link_attributes']);
        add_action('wp_ajax_fle_update_database_link', [$this, 'fle_update_database_link']);
    }

    /**
     * Change all links from post content, adding some atributtes to indetify the link
     */
    public function fle_edit_link_attributes($content) {
            global $post;
            $fle_id = $post->ID;

            $elementor_data = get_post_meta($fle_id, '_elementor_data', true);

            if (is_user_logged_in() && current_user_can('publish_posts') && !$elementor_data) {
                return preg_replace_callback('/href=".+?"/', function($matches) use ( $fle_id ) {
                    static $counter = 0;
                    $string = $matches[0] . "fle-post-id='$fle_id' fle-index='$counter' ";
                    $counter++;
                    return $string;
                }, $content);
            } 
            return $content;
        }

    /**
     * Update the link in the database
     * @return void
     */
    public function fle_update_database_link(): void {
        
        if (is_user_logged_in() && current_user_can('publish_posts') &&  check_ajax_referer( "fle_ajax_nonce", "nonce", false) && wp_verify_nonce( $_POST['fle_form_nonce'], 'validateForm' ) ) {
            $post = get_post((int)$_POST['post_ID']);
            $is_target_true = sanitize_text_field($_POST['target'] == 'true');
            $link_index = (int)$_POST['index'];
            $new_atts = [
                'href' => esc_url($_POST['fle_link_url']),
            ];

            if($is_target_true)
                $new_atts['target'] = '_blank';

            if($post) {

                $content =  wp_kses_post($post->post_content);
                $new_content = preg_replace_callback('/(<a.+?>)(.+?)<\/a>/', function($matches) use ( $link_index, $new_atts, $is_target_true) {
                    static $counter = 0;
                    $old_content = $matches[2];

                    if($counter == $link_index) {
                        $attr = [];
                        preg_match_all('/([a-z-]+)=\"([^"]*)\"/', $matches[1], $attr);

                        $attr_names = $attr[1];
                        $attr_values = $attr[2];

                        if($is_target_true)
                            $attr_names[] = 'target';
                        elseif($key = array_search('target', $attr_names)){
                            unset($attr_names[$key]);
                            unset($attr_values[$key]);
                        }

                        // Rebase array keys after unset
                        $attr_values = array_values($attr_values);
                        $attr_names = array_values($attr_names);

                        // Generate new link
                        $matches[0] = '<a';
                        $count = count($attr_names);
                    
                        for($i = 0; $i < $count; $i++){
                            $attr_value = $new_atts[ $attr_names[ $i ] ] ?? $attr_values[ $i ];
                            $matches[0] .= ' ' . $attr_names[$i] . '="' . $attr_value . '"';
                        }

                        if(empty(sanitize_text_field($_POST['fle_link_text']))) {
                            $matches[0] .= '>' . $old_content;
                        } else {
                            $matches[0] .= '>' . sanitize_text_field($_POST['fle_link_text']);
                        }
                        $matches[0] .= "</a>";
                    }
                    $counter++;
                    return $matches[0];
                }, $content);

                // Update the post with new content
                $update = wp_update_post([
                    'ID' => $post->ID,
                    'post_content' => wp_kses_post($new_content),
                ]);

                if(is_wp_error($update)){
                    wp_send_json_error('The link has not been updated.');
                }else{
                    wp_send_json_success('The link has been updated');
                }
            }else{
                wp_send_json_error('The link has been updated');
            }
        } else {
                    wp_redirect( home_url( '/404/' ) );
                    exit;
            }
    }

    /**
     * Render Popup
     * @return void
     */
    public function fle_render_link_edit_form($content) {
        $post_id = get_the_ID();
        $post_content = get_post_field('post_content', $post_id);

        $elementor_data = get_post_meta($post_id, '_elementor_data', true);

        if (is_user_logged_in() && current_user_can('publish_posts') && !$elementor_data) {
            include( FLE_PATH . 'includes/fle-form-modify.php');
        }
    }

    /**
     * Enqueue Plugin assets
     */
    public function fle_enqueue_plugin_scripts() {
        //Enqueue styles

        if (is_user_logged_in() && current_user_can('publish_posts') ) {

            $ajax_nonce = wp_create_nonce( 'fle_ajax_nonce' );

            wp_enqueue_style('fle-style', FLE_URL . 'public/css/style.css');
            wp_enqueue_script('fle-script', FLE_URL . 'public/js/script.js', ['jquery'], true);
            wp_enqueue_script( 'fle-jquery-validator', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.min.js', ['jquery'], true);


            $arr = array(
                'fleAjaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => $ajax_nonce,
                'fleIsSingular' => is_singular(),
                'fleIsUserLoggedIn'  => is_user_logged_in(),
                'fleUserCanPost' => current_user_can('publish_posts')
            );
            wp_localize_script( 'fle-script', 'phpVariables', $arr);
        }
    }

    /**
     * Activate method
     */
    public static function fle_activate() {
         flush_rewrite_rules();
    }

    /**
     * Deactivate method
     */
    public static function fle_deactivate() {
        flush_rewrite_rules();
    }
}

$frontend_link_editor = new FrontendLinkEditor();
register_activation_hook( __FILE__, array($frontend_link_editor, 'fle_activate' ) );
register_deactivation_hook( __FILE__, array($frontend_link_editor, 'fle_deactivate' ) );
}
