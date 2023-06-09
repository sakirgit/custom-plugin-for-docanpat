<?php 


add_action('rest_api_init', function() {
	register_rest_route('/devs-api', '/add-product-category/', array(
		'methods' => WP_REST_Server::CREATABLE,
		'callback' => 'devs_api_add_product_category',
		'args' => array(),
		'permission_callback' => function () {
			return true;
		}
	));
});

function devs_api_add_product_category( \WP_REST_Request $request ) {
	global $wp;
//	return json_encode($wp->query_vars);exit;
	$parameters = $request->get_params();
//	return new WP_REST_Response($parameters, 200);
	
	$page_url_slug = '/devs-api/add-product-category';
	
	if( isset($wp->query_vars['rest_route']) && $wp->query_vars['rest_route'] == $page_url_slug ){
		
	//	return $parameters['category'];
		$category_full_string = $parameters['category'];
		
		$term = get_term_by( 'name', $category_full_string, 'product_cat' );
		 /*
		 echo '<pre>...';
		 print_r($term);
		 echo '</pre>';
		 exit;
		 */
		$category = [
			"cat_id" => 0,
			"cat_name" => '',
		];
		if ( ! $term ) {
		//	echo 'Category not found<br>';
			 // Category does not exist, create it
			 $category['cat_name'] = $category_full_string;
			 $parent_id = 0;
			 $categories = array_map( 'trim', explode( '>', $category_full_string ) );
			 foreach ( $categories as $cat_k => $cat_v ) {
				$category['cat_name'] = $cat_v;
				if( $cat_k == 0 ){
					$category['cat_id'] = get_prod_cat_or_add( $cat_v, $parent_id );
					 /*
					 echo '<pre>...';
					 print_r(get_prod_cat_or_add( $cat_v, $parent_id ));
					 echo '</pre>';
					 */
				//	exit;
				}else{
					$category['cat_id'] = get_prod_cat_or_add( $cat_v, get_exist_prod_cat_id( $categories[$cat_k - 1] ) );
				}
			 }
		} else {
			echo 'Category found<br>';
			 // Category exists, use the existing category ID
			 $category['cat_id'] = $term->term_id;
		}
		return $category;
		//exit;
		//	print_r(json_encode($page_url_slug));exit;
		/*
		  if (
				array_key_exists($page_url_slug, $wp->query_vars) || 
				$wp->query_vars['name'] == $page_url_slug
				){
			 // If we found the product, get its data and return a JSON response
			 return new WP_REST_Response($parameters, 200);
		  } else {
			$params = ['status'=>'not_found'];
			return new WP_REST_Response($params, 400);
		  }
		  */
	} else {
		$params = ['status'=>'mismatched endpoint.'];
		return new WP_REST_Response($params, 400);
	}
}



