<?php 
/**
 * Plugin Name: Custom Plugin for DocanPat
 * Plugin URI: https://developer-s.com/
 * Description: Interact with custom product API
 * Version: 1.0
 * Author: Sakir Ahamed
 * Author URI: https://developer-s.com/
 **/

// Hook into WooCommerce

//add_filter( 'woocommerce_api_check_https', '__return_false' );



add_action('woocommerce_init', 'devs_custom_product_api_init');

function devs_custom_product_api_init() {
  // Register a custom endpoint for the API
  add_rewrite_endpoint('api-check-product-by-sku', EP_ALL);

  // Listen for requests to the custom endpoint
  add_action('parse_request', 'devs_custom_product_api_request');

  // Add the API response to the product page
  add_action('woocommerce_single_product_summary', 'my_custom_product_api_output', 25);
}

function devs_custom_product_api_request($wp) {
//	print_r($wp->query_vars);exit;
//	$wp->query_vars['name'] = '';
	if( array_key_exists('api-check-product-by-sku', $wp->query_vars) || isset($wp->query_vars['name']) ){
		
		//	print_r($wp->query_vars['name']);exit;
		  if (
				array_key_exists('api-check-product-by-sku', $wp->query_vars) || 
				$wp->query_vars['name'] == 'api-check-product-by-sku'
				){
		//	print_r($wp->query_vars);exit;
			 // Get the SKU from the query string
			 $sku = '';
			 if( isset($_GET['sku']) ){
				$sku = $_GET['sku'];
			 }

			 // Search for the product with the given SKU
			 $product = wc_get_product_id_by_sku($sku);

			 // If we found the product, get its data and return a JSON response
			 if ($product) {
				$data = wc_get_product($product)->get_data();
				header('Content-Type: application/json');
				$data['status'] = 'found';
				$data['product_id'] = $product;
				echo json_encode($data);
				exit;
			 } else {
				// If the product wasn't found, output a "product not found" message
				echo json_encode(['status'=>'not_found', 'product_id'=>0]);
				exit; 
			 }
		  }
	}
}

function my_custom_product_api_output() {
  global $wp_query;

  // Check if we have an API response
  if (array_key_exists('api_response', $wp_query->query_vars)) {
    $response = $wp_query->query_vars['api_response'];

    // Get the data from the API response
    $data = json_decode(wp_remote_retrieve_body($response));

    // Check if the product was found
    if ($data->status == 'found') {
      // Output the product data as JSON
      header('Content-Type: application/json');
      echo json_encode($data->product);
      exit;
    } else {
      // Output a "product not found" message
      echo json_encode(['status'=>'not_found']);
		exit;
    }
  }
}




/* ================================== */
/* ================================== */
/* ================================== */
/* ================================== */
// Load WooCommerce
//if ( ! defined( 'ABSPATH' ) ) {
  //  require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );
//}



function get_exist_prod_cat_id($string){
	
	$cat_term = get_term_by( 'name', $string, 'product_cat' );
		 /*
		 echo '<pre>mmm2';
		 print_r($cat_term);
		 echo 'uuuu2</pre>';
		 */
	return $cat_term->term_id;
}

function get_prod_cat_or_add( $string, $parent_id = 0, $taxonomy_slug = 'product_cat' ){
	
	$cat_term = get_term_by( 'name', $string, $taxonomy_slug );
	
	if ( $cat_term ) {
		$cat_id = $cat_term->term_id;
	} else {
		
		$term_slug = sanitize_title( $string );

		$args = array(
			 'description' => '',
			 'parent' => $parent_id,
			 'slug' => $term_slug,
			 'name' => $string,
			 'taxonomy' => $taxonomy_slug
		);
		
		$cat_id = wp_insert_term( $string, $taxonomy_slug, $args );
		$cat_id = $cat_id['term_id'];
	}
	return $cat_id;
}


require_once('wooc-product-add.php');
require_once('wooc-product-category-add.php');
require_once('wooc-product-brand-add.php');
require_once('wooc-product-update-by-sku.php');
require_once('shortcodes/sc-nav.php');


//add_action( 'init', 'myplugin_add_product_category' );
function myplugin_add_product_category(){
$dir = plugin_dir_path( __FILE__ );
if (($handle = fopen($dir . "product-data.csv", "r")) !== FALSE) {
	$i = 0;
    // Loop through each row in the CSV
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		 if( $i > 0 ){
					 
				  // Add product categories
				  $categories = array_map( 'trim', explode( ',', $data[5] ) );
				 /*
				 echo '<pre>';
				 print_r($categories);
				 echo '</pre>';
				 */
				  $category_ids = array();
				  foreach ( $categories as $category ) {
						$term = get_term_by( 'name', $category, 'product_cat' );
						 /*
						 echo '<pre>...';
						 print_r($term);
						 echo '</pre>';
						 exit;
						 */
						if ( ! $term ) {
						//	echo 'Category not found<br>';
							 // Category does not exist, create it
							 $parent_id = 0;
							 $categories = array_map( 'trim', explode( '>', $category ) );
							 foreach ( $categories as $cat_k => $cat_v ) {
								
								if( $cat_k == 0 ){
									get_prod_cat_or_add( $cat_v, $parent_id );
									 /*
									 echo '<pre>...';
									 print_r(get_prod_cat_or_add( $cat_v, $parent_id ));
									 echo '</pre>';
									 */
								//	exit;
								}else{
									get_prod_cat_or_add( $cat_v, get_exist_prod_cat_id( $categories[$cat_k - 1] ) );
								}
							 }
						} else {
							echo 'Category found<br>';
							 // Category exists, use the existing category ID
							 $category_ids[] = $term->term_id;
						}
				  }
				  
		 }
		 $i++;
	}
}	
}










function custom_product_thumbnail_column( $column, $post_id ) {
    if ( 'thumb' === $column ) {
        $thumbnail_id = get_post_thumbnail_id( $post_id );
        $thumbnail_image = wp_get_attachment_image( $thumbnail_id, 'thumbnail' );

        if ( empty( $thumbnail_image ) ) {
            // Display your custom placeholder image if no thumbnail found


					$product_urls = get_post_meta( $post_id, 'product_urls', true );

					if ( ! empty( $product_urls ) ) {
						 	// Get product URLs meta field value
							$product_urls = explode(",",$product_urls);
							
	            echo '<img src="' . $product_urls[0] . '" class="cb_thumb" alt="Product from checkbox" width="60" height="60" />';
					}
       		
        }
    }
}

add_action( 'manage_product_posts_custom_column', 'custom_product_thumbnail_column', 10, 2 );

/**
 * Enqueue custom CSS for WooCommerce admin product listing page
 */
function enqueue_custom_admin_css() {
    $screen = get_current_screen();
    if ( $screen && 'edit-product' === $screen->id ) {
        wp_enqueue_style( 'custom-admin-css', plugin_dir_url( __FILE__ ) . '/css/custom-admin.css' );
    }
}
add_action( 'admin_enqueue_scripts', 'enqueue_custom_admin_css' );






/**
 * Register custom taxonomy for product brand.
 */
function register_product_brand_taxonomy() {
    $labels = array(
        'name'                       => _x( 'Product Brands', 'taxonomy general name', 'text-domain' ),
        'singular_name'              => _x( 'Product Brand', 'taxonomy singular name', 'text-domain' ),
        'search_items'               => __( 'Search Product Brands', 'text-domain' ),
        'popular_items'              => __( 'Popular Product Brands', 'text-domain' ),
        'all_items'                  => __( 'All Product Brands', 'text-domain' ),
        'parent_item'                => null,
        'parent_item_colon'          => null,
        'edit_item'                  => __( 'Edit Product Brand', 'text-domain' ),
        'update_item'                => __( 'Update Product Brand', 'text-domain' ),
        'add_new_item'               => __( 'Add New Product Brand', 'text-domain' ),
        'new_item_name'              => __( 'New Product Brand Name', 'text-domain' ),
        'separate_items_with_commas' => __( 'Separate product brands with commas', 'text-domain' ),
        'add_or_remove_items'        => __( 'Add or remove product brands', 'text-domain' ),
        'choose_from_most_used'      => __( 'Choose from the most used product brands', 'text-domain' ),
        'not_found'                  => __( 'No product brands found.', 'text-domain' ),
        'menu_name'                  => __( 'Product Brands', 'text-domain' ),
    );

    $args = array(
        'hierarchical'          => true,
        'labels'                => $labels,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'show_in_nav_menus'     => true,
        'public'                => true,
        'publicly_queryable'    => true,
        'query_var'             => true,
        'rewrite'               => array( 'slug' => 'product_brand', 'with_front' => true ),
        'show_admin_column'     => true,
        'show_in_rest'          => true,
        'show_tagcloud'         => false,
    );

    register_taxonomy( 'product_brand', array( 'product' ), $args );
}
add_action( 'init', 'register_product_brand_taxonomy', 0 );





