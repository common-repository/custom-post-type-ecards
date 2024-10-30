<?php
/*
Plugin Name: Custom Post Type eCards
Plugin URI: http://arrowbits.com/blog/wordpress-custom-post-plugin-with-shortcode-and-pagination/
Description: Custom Post Type plugin lets you create Custom Post Types and Custom Taxonomies in a user-friendly way. You can add custom posts and custom taxnomies on any page using this auto-created shortcode.
Version: 1.0.2
Author: Arrowbits
Author URI: http://arrowbits.com/
*/
/**
 * @author      Arrowbits
 * @copyright   Copyright (c) 2018, Arrowbits
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @version     1.0.2
 */
/**
 */
add_action("wp_head", "ecpt_save_post");
function ecpt_save_post(){
    $ecptlabels = array(
        'name'               => _x( 'Ecards', 'post type general name' ),
        'singular_name'      => _x( 'Ecards', 'post type singular name' ),
        'add_new'            => _x( 'Add New', 'ecards' ),
        'add_new_item'       => __( 'Add New ecards' ),
        'edit_item'          => __( 'Edit ecards' ),
        'new_item'           => __( 'New ecards' ),
        'all_items'          => __( 'All ecards' ),
        'view_item'          => __( 'View ecards' ),
        'search_items'       => __( 'Search ecards' ),
        'not_found'          => __( 'No ecards found' ),
        'not_found_in_trash' => __( 'No ecards found in the Trash' ),
        'parent_item_colon'  => '',        
        'parent'             => __( 'Parent Ecards Cards' ),
        'menu_name'          => 'Ecards'
    );      
    $ecptargs = array(
        'labels'             => $ecptlabels,
        'description'        => 'Holds our Ecards specific data',
        'public'             => true,
        'menu_position'      => 5,
        'supports'           => array('title','editor','thumbnail','author','comments','custom-fields','post-formats','page-attributes'),
        'has_archive'        => true,
        'capability_type'    => 'post',
        'rewrite'            => array('slug' => 'ecards' ),
        'hierarchical'       => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_admin_column'  => true,
        'can_export'         => true,
        'show_in_nav_menus'  => true,
        'query_var'          => true,
        'with_front'         => true
    ); 
    register_post_type('ecards',$ecptargs);  
}
add_action('init','ecpt_save_post');
register_activation_hook(__FILE__, 'ecpt_save_post');
function ecpt_frontend_posts($patts){       
global $wpdb;
global $post;
if(isset($patts['posts_per_page'])) {
    $posts_per_page = $patts['posts_per_page'];
    } else {
    $posts_per_page = -1;
    }
    $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

    $args = array(
      'post_type'      => 'ecards',
      'post_status'    => 'publish',
      'posts_per_page' => $posts_per_page,
      'paged'          => $paged
    );
    $the_query = new WP_Query( $args );
    if ( $the_query->have_posts() ) :
    while ( $the_query->have_posts() ) : $the_query->the_post(); 
    echo '<div class="entry">';
    echo '<h2 class="title">';
    echo '<a href="'.get_the_permalink().'">';
    the_title();
    echo '</a>';       
    echo '</h2>';
    the_excerpt();
    echo '</div>';
    endwhile;
    next_posts_link( 'Older Entries', $the_query->max_num_pages );
    previous_posts_link( 'Newer Entries' );
    wp_reset_postdata(); 
    else:
    echo 'Either your shortcode is wrong or there is no post in this category.';
    endif;
}
add_shortcode( 'show_custom_post', 'ecpt_frontend_posts' );
/*Adding TAXONOMIES*/
function ecpt_taxonomy_save_post(){    
    $taxonomylabels = array(
            'name'              => _x( 'Ecards Category', 'taxonomy general name' ),
            'singular_name'     => _x( 'Ecards Category', 'taxonomy singular name' ),
            'search_items'      => __( 'Search Ecards Category' ),
            'all_items'         => __( 'All Ecards Category' ),
            'parent_item'       => __( 'Parent' ),
            'parent_item_colon' => __( 'Parent' ),
            'edit_item'         => __( 'Edit' ), 
            'update_item'       => __( 'Update' ),
            'add_new_item'      => __( 'Add New' ),
            'new_item_name'     => __( 'New' ),
            'menu_name'         => __( 'Ecards Category' )
        ); 
        register_taxonomy(
            'ecards-categories',
            array('ecards'), 
                array(
                    'hierarchical'      => true,
                    'labels'            => $taxonomylabels,
                    'rewrite'           => array('slug' => 'ecards-categories' ),
                    'show_ui'           => true,
                    'show_admin_column' => true,
                    'show_tagcloud'     => false,
                    'query_var'         => true,
                    'rewrite'           => array('slug' => 'ecards-categories')                   
            )
        );
}
add_action('init', 'ecpt_taxonomy_save_post');
function ecpt_taxonomy_frontend_posts($atts){
global $wpdb;
global $post;
if(isset($atts['catprettyname'])){$cats = $atts['catprettyname'];}else{return;}
if(isset($atts['posts_per_page'])){$posts_per_page = $atts['posts_per_page'];}else{$posts_per_page = -1;}
$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
$getallposts = $wpdb->get_results("SELECT * FROM $wpdb->terms WHERE slug LIKE '%".$atts['catprettyname']."%'",OBJECT);
    foreach($getallposts as $category){
    $term_id= $category->term_id;     
    }
    $taxonomyargss = array(
        "posts_per_page" => $posts_per_page,
        "paged"          => $paged, 
        "post_type"      => "ecards", 
        "post_status"    => "publish", 
        "orderby"        => "meta_value",
        "order"          => "DESC",
        "tax_query"      => array(
                            array(
                                "taxonomy" => "ecards-categories",
                                "field" => "term_id",
                                "terms" => $term_id
                                )
                            ),  
    );
    $the_query = new WP_Query( $taxonomyargss );
    if ( $the_query->have_posts() ) :
    while ( $the_query->have_posts() ) : $the_query->the_post();         
    echo '<div class="entry">';
    echo '<h2 class="title">';
    echo '<a href="'.get_the_permalink().'">';
    the_title();
    echo '</a>';       
    echo '</h2>';
    $image = get_the_post_thumbnail( $the_query->ID, 'thumbnail', array( 'class' => '' ) );   
    echo '<a class="postlink" href="'.get_the_permalink().'">'.$image.'</a>';
    the_excerpt();
    echo '</div>';
    endwhile;
    next_posts_link( 'Older Entries', $the_query->max_num_pages );
    previous_posts_link( 'Newer Entries' );
    wp_reset_postdata(); 
    else:
    echo 'Sorry, no post found.';
    endif;
}
add_shortcode( 'categorypost', 'ecpt_taxonomy_frontend_posts' );
add_action( 'admin_menu', 'ecpt_shortcodes_menu' );
function ecpt_shortcodes_menu(){    
$page_title = 'Ecards shortcodes';
$menu_title = 'Ecards shortcodes';
$capability = 'manage_options';
$menu_slug  = 'ecards_shortcodes';
$function   = 'ecpt_shortcodesList_page';
$icon_url   = 'dashicons-media-code';
$position   = 8;    
add_menu_page( $page_title,$menu_title,$capability,$menu_slug,$function,$icon_url,$position );
}
function ecpt_shortcodesList_page(){
global $wpdb;
global $post;   
if(is_plugin_active( 'custom-post-type-ecards/custom_type_post_ecards.php')){
$getallshortcode = $wpdb->get_results("SELECT * FROM $wpdb->term_taxonomy JOIN $wpdb->terms ON 
$wpdb->term_taxonomy.term_taxonomy_id=$wpdb->terms.term_id WHERE $wpdb->term_taxonomy.taxonomy='ecards-categories' AND $wpdb->term_taxonomy.count !='' ",OBJECT);    
        $i=1;
        ?>
        <table id="tbsize">
        <tr>
        <th id="sn">No</th>
        <th id="shorttitle">Name</th>       
        <th id="shortn">Page Shortcode</th>
        <th id="shortntemplate">Template Shortcode</th>
        </tr>
        <?php
            foreach($getallshortcode as $getallcat){
            ?>
            <tr>
            <td id="sn"><?php echo $i;?></td>
            <td id="shorttitle"><?php echo $getallcat->name;?></td>
            <td id="shortn"><?php echo '[categorypost catprettyname="'.$getallcat->slug.'" posts_per_page="1"]';?></td>
            <td id="shortntemplate">do_shortcode('[categorypost catprettyname=<?php echo $getallcat->slug;?> posts_per_page="1"]')</td>
            </tr>
            <?php
            $i++;   
            }                    
    }else{
    echo "Custom post plugin is deactivated. Please active first.";
    }
    echo '</table>';
    ?>
    <style>
#tbsize{
    width: 100%;
}
#sn {
    width: 1%;
    text-align: left;
    padding: .5%;
}
#shorttitle {
    width: 1%;
    text-align: left;
    padding: .5%;
}
#titleslug{
    width: 1%;
    text-align: left;
    padding: .5%;
}
#shortn{
    width: 2%;
    text-align: left;   
    padding: .5%;
}

#shortntemplate{
    width: 2%;
    text-align: left;   
    padding: .5%;
}
</style>
<?php
}
function ecpt_plugin_activate_flush_rewrite(){
ecpt_save_post();
ecpt_taxonomy_save_post();
flush_rewrite_rules();
}
add_action( 'init', 'ecpt_save_post');
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
register_activation_hook( __FILE__, 'ecpt_plugin_activate_flush_rewrite' );
?>