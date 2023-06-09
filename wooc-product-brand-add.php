<?php 


add_action('rest_api_init', function() {
	register_rest_route('/devs-api', '/add-product-brand/', array(
		'methods' => WP_REST_Server::CREATABLE,
		'callback' => 'devs_api_add_product_brand',
		'args' => array(),
		'permission_callback' => function () {
			return true;
		}
	));
});

function devs_api_add_product_brand( \WP_REST_Request $request ) {
	global $wp;
//	return json_encode($wp->query_vars);exit;
	$parameters = $request->get_params();
//	return new WP_REST_Response($parameters, 200);
	
	$page_url_slug = '/devs-api/add-product-brand';


	if( isset($wp->query_vars['rest_route']) && $wp->query_vars['rest_route'] == $page_url_slug ){
		
	//	return $parameters['category'];
		$brand_name = $parameters['brand'];
		
		$term = get_term_by( 'name', $brand_name, 'product_brand' );
		/*
		 echo '<pre>...';
		 print_r($term);
		 echo '</pre>';
		 exit;
		*/
		$taxonomy = [
			"brand_id" => 0,
			"brand_name" => '',
		];
		if ( ! $term ) {
		//	echo 'Category not found<br>';
			 // Category does not exist, create it
			 $taxonomy['brand_name'] = $brand_name;
			 $parent_id = 0;
			 $taxonomy['brand_id'] = get_prod_cat_or_add( $brand_name, $parent_id, 'product_brand' );
		} else {
			 $taxonomy['brand_id'] = $term->term_id;
			 $taxonomy['brand_name'] = $term->name;
		}
		return $taxonomy;
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



