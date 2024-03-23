<?php
/**
 * Plugin Name:     Countify
 * Plugin URI:      https://countity.com
 * Description:     Records the number of views a post has received and displays the count in the admin post list.
 * Author:          Iftakharul Islam
 * Author URI:      ifatwp.wordpress.com
 * Text Domain:     countify
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         countify
 */

 if ( ! defined("ABSPATH") ) exit;
 final class Countify {
     private $post_meta_key = 'countify_post_view_count';
 
     public function __construct() {
         add_action('wp', array($this, 'handle_view_count_increment'));
         add_action('manage_posts_custom_column', array($this, 'display_view_count_column'), 10, 2);
         add_filter('manage_posts_columns', array($this, 'add_view_count_column'));
         add_filter('manage_edit-post_sortable_columns', array($this, 'make_view_count_column_sortable'));
         add_shortcode('countify_post_view_count', array($this, 'shortcode_view_count'));
         add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
     }
 
     private function get_post_view_count($post_id) {

         return get_post_meta( sanitize_text_field( absint($post_id) ), sanitize_key( $this->post_meta_key ), true) ?: 0;
     }
 
     private function update_post_view_count( $post_id ) {
         
        $view_count = $this->get_post_view_count($post_id);

         update_post_meta(sanitize_text_field( absint($post_id) ), sanitize_key($this->post_meta_key), sanitize_text_field(absint(++$view_count)));
     }
 
     public function handle_view_count_increment() {
         
        if (is_single()) { // is_single means whether I'm in the single blog post page then increment view
             $this->update_post_view_count(get_the_ID());
         }
     }
 
     public function add_view_count_column($columns) {
         $columns['view_count'] = __('View Count &nbsp;', 'countify') . '<i class="dashicons dashicons-visibility"></i>';
         return $columns;
     }
 
     public function display_view_count_column($column, $post_id) {
         if ($column === 'view_count') {
             echo $this->get_post_view_count($post_id);
         }
     }
 
     public function make_view_count_column_sortable($columns) {
         $columns['view_count'] = 'view_count';
         return $columns;
     }
 
     public function shortcode_view_count($atts) {
         $atts = shortcode_atts(array(
             'post_id' => get_the_ID(),
         ), $atts, 'post_view_count');
        
         $view_count = $this->get_post_view_count ($atts['post_id'] );
         return $this->view_count_markup($view_count);
     }

     private function view_count_markup($view_count) {
        ob_start()
        ?>
            <div class='countify-post-view'>
                <h3><?php echo esc_html_e('Post viewed', 'countify'); ?></h3> 
                <div class="countify-post-counter">
                    <i class="dashicons dashicons-visibility"></i>
                    <span class="count"> <?php echo esc_html($view_count); ?> </span>
                </div>
            </div>
        <?php 
        return ob_get_clean();
     }
 
     public function enqueue_styles() {
         wp_enqueue_style('countify-post-view-count-style', plugins_url('assets/css/style.css', __FILE__));
         wp_enqueue_script('countify-post-view-count-script', plugins_url('assets/js/countify.js', __FILE__), ['jQuery'],'100',false);
     }
 }
 
 $post_view_count_plugin = new Countify();
 