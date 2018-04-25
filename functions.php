<?php
show_admin_bar( false );
define('SITEURL', site_url());
define('THEMEURL', get_template_directory_uri());
register_nav_menus(
        array(
            'main_menu' => __('Main menu', 'lemonscentedtea'),
            'footer_menu' => __('Footer menu', 'lemonscentedtea')
        )
);

/*
 *  @description: remove WPML styling
 */
define('ICL_DONT_LOAD_LANGUAGE_SELECTOR_CSS', true);
define('ICL_DONT_LOAD_NAVIGATION_CSS', true);
define('ICL_DONT_LOAD_LANGUAGES_JS', true);

/*
 *  @description: remove wp_head and footer clutter
 */
remove_action('wp_head', 'rest_output_link_wp_head');
remove_action('wp_head', 'wp_oembed_add_discovery_links');
remove_action('template_redirect', 'rest_output_link_header', 11, 0);
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'wp_generator');

function my_deregister_scripts() {
    wp_deregister_script('wp-embed');
}

add_action('wp_footer', 'my_deregister_scripts');
global $sitepress;
remove_action('wp_head', array($sitepress, 'meta_generator_tag'));

/*
 *  @description: Add defer/async attrib to enqueued scripts
 */

add_filter('script_loader_tag', 'add_defer_attribute', 10, 2);

function add_defer_attribute($tag, $handle) {
    $handlersuffix = substr($handle, -5);
    if ('Async' === $handlersuffix) {
        return str_replace(' src', ' async="async" src', $tag);
    } elseif ('Defer' === $handlersuffix) {
        return str_replace(' src', ' defer="defer" src', $tag);
    } else {
        return $tag;
    }
}

/*
 *  @description: Enqueue scripts. Put "Async" at the end of name for Async loading and "Defer" for deferred loading
 */

add_action('wp_enqueue_scripts', 'enqueue_scripts');

function enqueue_scripts() {
    if (is_page_template('template-contact.php')) {
        wp_enqueue_script('GoogleMaps', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyCGiM9Lk7ypP5rTKIP-1Dhp78Bd1CEpMgo&v=3', array(), '3.0.0', true);
        wp_enqueue_script('GoogleMapsCustom', get_template_directory_uri() . '/js/map.js', array('GoogleMaps'), '1.0.0', true);
    }

    wp_enqueue_style('main-css', get_template_directory_uri().'/css/main.css');
    /* Custom scripts */
    wp_enqueue_script('plugins-js', get_template_directory_uri() . '/js/plugins.js', array('jquery'), '1.0.0', true);
    wp_enqueue_script('main-js', get_template_directory_uri() . '/js/main.js', array('jquery'), '1.0.0', true);

    wp_localize_script('main-js', 'theme', 
        array(
            'ajax_url' => admin_url('admin-ajax.php')
        )
    ); 
}

// Post types function
function create_posttypes() {
    register_post_type('team',
            // CPT Options
            array(
        'labels' => array(
            'name' => __('Team'),
            'singular_name' => __('Team'),
            'add_new' => __('Nieuw teamlid')
        ),
        'public' => true,
        'has_archive' => true,
        'exclude_from_search' => true,
        'supports' => array('title'),
        'menu_icon' => 'dashicons-admin-users',
        'rewrite' => array('slug' => 'team'),
            )
    );
    register_post_type('vacatures',
            // CPT Options
            array(
        'labels' => array(
            'name' => __('Vacatures'),
            'singular_name' => __('Vacature'),
            'add_new' => __('Nieuwe vacature')
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array('title','editor'),
        'menu_icon' => 'dashicons-format-aside',
        'rewrite' => array('slug' => 'vacatures'),
            )
    );
    register_post_type('cases',
            // CPT Options
            array(
        'labels' => array(
            'name' => __('Cases'),
            'singular_name' => __('Case'),
            'add_new' => __('Nieuwe case')
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'thumbnail'),
        'menu_icon' => 'dashicons-portfolio',
        'rewrite' => array('slug' => 'cases'),
            )
    );
}

add_action('init', 'create_posttypes');


// Move Yoast Meta Box to bottom
function yoasttobottom() {
	return 'low';
}

add_filter( 'wpseo_metabox_prio', 'yoasttobottom');

function footersidebars() {
  register_sidebar( array(
  'name' => 'Footer contact details',
  'id' => 'footer-sidebar-1',
  'description' => '',
  'before_widget' => '',
  'after_widget' => '',
  'before_title' => '',
  'after_title' => '',
  ) );
  register_sidebar( array(
      'name' => 'Header Text',
      'id' => 'header-text',
      'description' => '',
      'before_widget' => '',
      'after_widget' => '',
      'before_title' => '',
      'after_title' => '',
  ) );
}

add_action('widgets_init', 'footersidebars');


add_theme_support('post-thumbnails', array('post', 'page', 'cases'));

add_theme_support('custom-logo', array(
    'height' => 240,
    'width' => 240,
    'flex-height' => true,
    'flex-width' => true
));

function cc_mime_types($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'cc_mime_types');


add_image_size('figure', 1440, 600);
add_image_size('case-thumbnail', 952, 595);

function my_remove_menu_pages() {
    remove_menu_page('link-manager.php');
    remove_menu_page('edit-comments.php');
}

/*  Custom thumbnail quality
  /* ------------------------------------ */

function alx_thumbnail_quality($quality) {
    return 100;
}

add_filter('jpeg_quality', 'alx_thumbnail_quality');
add_filter('wp_editor_set_quality', 'alx_thumbnail_quality');

/* Disable stupid emojis
  /* ------------------------------------ */

function disable_wp_emojicons() {

    // all actions related to emojis
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');

    // filter to remove TinyMCE emojis
    add_filter('tiny_mce_plugins', 'disable_emojicons_tinymce');
}

add_action('init', 'disable_wp_emojicons');

function disable_emojicons_tinymce($plugins) {
    if (is_array($plugins)) {
        return array_diff($plugins, array('wpemoji'));
    } else {
        return array();
    }
}


// load more cases
function load_cases(){
 
	// prepare our arguments for the query
	$args = array();
	$args['paged'] = $_POST['page'] + 1;
    $args['post_status'] = 'publish';
    $args['post_type'] = 'cases';
 
    query_posts( $args );
 
	if( have_posts() ) :
 
		// run the loop
        while( have_posts() ): the_post();
			get_template_part( 'parts/part', 'case');
		endwhile;
 
	endif;
	die;
}
 
 
 
add_action('wp_ajax_loadmorecases', 'load_cases'); // wp_ajax_{action}
add_action('wp_ajax_nopriv_loadmorecases', 'load_cases'); // wp_ajax_nopriv_{action}