<?php 

function devs_post_api_res( $url, $params_arr ){
	
	//	$url = "https://example.com/api/endpoint";
	//	$params_arr = array('param1' => 'value1', 'param2' => 'value2');
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params_arr));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);
		
		return $response;
}

//add_action('woocommerce_init', 'devs_add_product_n_cat_api_init');

add_action('rest_api_init', function() {
	register_rest_route('/devs-api', '/api-add-product-n-cat/', array(
		'methods' => WP_REST_Server::CREATABLE,
		'callback' => 'get_awesome_params',
		'args' => array(),
		'permission_callback' => function () {
			return true;
		}
	));
});

function get_awesome_params( \WP_REST_Request $request ) {
	global $wp;
//	return json_encode($wp->query_vars);exit;
	$parameters = $request->get_params();
//	return new WP_REST_Response($parameters, 200);
	
	$page_url_slug = '/devs-api/api-add-product-n-cat';
	
	if( isset($wp->query_vars['rest_route']) && $wp->query_vars['rest_route'] == $page_url_slug ){
		
		$checkbox_price = $price =  preg_replace("/[^0-9]/", "", $parameters['productPrice']);
		if( $price > 0 && $price <= 50 ){
			$parameters['productPrice'] =  $price + 20;
		}else if( $price > 50 && $price <= 80){
			$parameters['productPrice'] =  $price + 30;
		}else if( $price > 80 && $price <= 100){
			$parameters['productPrice'] =  $price + 40;
		}else if( $price > 100 && $price <= 200){
			$parameters['productPrice'] =  $price + 50;
		}else if( $price > 200 && $price <= 300){
			$parameters['productPrice'] =  $price + 80;
		}else if( $price > 300 && $price <= 400){
			$parameters['productPrice'] =  $price + 90;
		}else if( $price > 400 && $price <= 500){
			$parameters['productPrice'] =  $price + 100;
		}else if( $price > 500 && $price <= 800){
			$parameters['productPrice'] =  $price + 110;
		}else if( $price > 800 && $price <= 900){
			$parameters['productPrice'] =  $price + 120;
		}else if( $price > 900 && $price <= 1000){
			$parameters['productPrice'] =  $price + 130;
		}else if( $price > 1000 && $price <= 1100){
			$parameters['productPrice'] =  $price + 130;
		}else if( $price > 1100 && $price <= 1200){
			$parameters['productPrice'] =  $price + 140;
		}else if( $price > 1200 && $price <= 1300){
			$parameters['productPrice'] =  $price + 150;
		}else if( $price > 1300 && $price <= 1500){
			$parameters['productPrice'] =  $price + 160;
		}else if( $price > 1500 && $price <= 1700){
			$parameters['productPrice'] =  $price + 170;
		}else if( $price > 1700 ){
			$parameters['productPrice'] = $price + ($price * 0.08);
		}
		
		
		$api_url = get_site_url() . "/wp-json/devs-api/add-product-category";
		$category_id = json_decode( devs_post_api_res( $api_url, ["category"=>$parameters['breCategories']] ) );
		
		
		
		
	//	/*
		$post_id = wp_insert_post( array(
			'post_title' => $parameters['productTitle'],
			'post_type' => 'product',
			'post_status' => 'publish',
			'post_content' => $parameters['full_description'],
		));
		
		// Set product category IDs (replace 123 with the actual category ID(s))
		$cat_ids = array( $category_id->cat_id );

		// Set the product category for the new product
		wp_set_object_terms( $post_id, $category_id->cat_id, 'product_cat' );




		$api_url_brand = get_site_url() . "/wp-json/devs-api/add-product-brand";

		if( $parameters['productBrand']['status'] ){

			$brand_title = $parameters['productBrand']['response'];
			if( $brand_title != "-" ){
				
				$brand_id = json_decode( devs_post_api_res( $api_url_brand, [ "brand"=>$brand_title ] ) );

				wp_set_object_terms( $post_id, $brand_id->brand_id, 'product_brand' );
			}
		}



		$product['prod_id']=$post_id;
		$product['cat_id']=$category_id->cat_id;
		
		$product = wc_get_product( $post_id );
		$product->set_sku( $parameters['productSKU'] );

		if( $parameters['isStock'] != "yes" ){

			$product->set_stock_status('outofstock');
		}

		$product->set_regular_price( $parameters['productPrice'] );
		update_post_meta( $post_id, 'product_urls', $parameters['allImages'] );
		update_post_meta( $post_id, 'product_instruction_from_checkbox', $parameters['instructions'] );
		update_post_meta( $post_id, 'checkbox_price', $checkbox_price );
		update_post_meta( $post_id, 'product_small_thumbnail', $parameters['smallImageUrl'] );
		$product->save();
		return $product;

	//	*/
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


function add_custom_javascript_to_product_edit_page() {
    // Check if we're on the product edit page
    global $post;
    if ( $post && $post->post_type == 'product' ) {
        ?>
        <script type="text/javascript">
		//  /*
        jQuery(document).ready(function($) {
            // Get the value of the custom metabox field
            var prodImgLink = $('#acf-field_642870641da1b').val();
				prodImgLink = prodImgLink.split(",");
            // Do something with the value
				
				const imageContainer = document.getElementById('acf-group_64287062cfc90');
				var i = 0;
				prodImgLink.forEach(url => {
				  const img = document.createElement('img');
				  img.src = url.trim();
				  if( i == 0 ){
					img.style.cssText = "max-width: 250px;margin-left:1px;margin-right:1px;";
				  }else{
					img.style.cssText = "max-width: 180px;margin-left:1px;margin-right:1px;";
				  }
				  imageContainer.appendChild(img);
				  i++;
				});
				
            console.log("prodImgLink:: ",prodImgLink);
        });
		//  */
		  
		  

		  
        </script>
        <?php
    }
}
add_action( 'admin_footer', 'add_custom_javascript_to_product_edit_page' );
