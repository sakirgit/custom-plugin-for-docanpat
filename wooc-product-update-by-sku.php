<?php 

//add_action('woocommerce_init', 'devs_add_product_n_cat_api_init');

add_action('rest_api_init', function() {
	register_rest_route('/devs-api', '/api-add-product-sm-thumb-n-price-update/', array(
		'methods' => WP_REST_Server::CREATABLE,
		'callback' => 'from_api_req_to_update_product_img_price_stock',
		'args' => array(),
		'permission_callback' => function () {
			return true;
		}
	));
});

function from_api_req_to_update_product_img_price_stock( \WP_REST_Request $request ) {
	global $wp;

	$parameters = $request->get_params();
//	return new WP_REST_Response($parameters, 200);
	
	$page_url_slug = '/devs-api/api-add-product-sm-thumb-n-price-update';
	
	if( isset($wp->query_vars['rest_route']) && $wp->query_vars['rest_route'] == $page_url_slug ){
		
//		return $parameters;exit;
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
				

		 $product_id = wc_get_product_id_by_sku( $parameters['productSKU'] );
		 
	//	return $product_id;exit;
		if( $product_id ){
			$params['product_id'] = $product_id;	
			$product = wc_get_product( $product_id );
				
				
			/* ==================================== Product Price ==================================== */
			$prev_price = $product->get_price();
			$params['price']['prev'] = $prev_price;
			$params['price']['new'] = (int) $parameters['productPrice'];
			$params['price']['status'] = 'no-need-to-update';
			if( $prev_price != $parameters['productPrice'] ){
				
				$params['price']['status'] = 'not-updated';
				if($product->set_regular_price( $parameters['productPrice'] )){
					
					$params['price']['status'] = 'updated';
				}
			}
			
			
			/* ==================================== Stock status ==================================== */
			$prev_stock = $product->get_stock_status();
			$params['stock']['prev'] = $prev_stock;
			$params['stock']['new'] = $parameters['stock'];
			$params['stock']['status'] = 'no-need-to-update';
			if( $prev_stock != $parameters['stock'] ){
				$stock_status = 'instock';
				if( $parameters['stock'] != "yes" ){

					$stock_status  = 'outofstock';
				}
				
				$params['stock']['status'] = 'not-updated';
				if($product->set_stock_status($stock_status )){
					$params['stock']['status'] = 'updated';
				}
			}
			
			$product->save(); // <<<=====Saved=====||<
			
				
				
			/* ==================================== Small thumbnail ==================================== */
			$smImg = str_replace("/200x200/", "/", $parameters['thumb']);
			
			$product_small_thumbnail = get_post_meta($product_id, 'product_small_thumbnail', true);

			$params['sm_thumb']['prev'] = $product_small_thumbnail;
			$params['sm_thumb']['new'] = $smImg;
			$params['sm_thumb']['status'] = 'no-need-to-update';
			if ( $product_small_thumbnail != $smImg ) {
				
				$params['sm_thumb']['status'] = 'not-updated';
				if(update_post_meta( $product_id, 'product_small_thumbnail', $smImg )){
					$params['sm_thumb']['status'] = 'updated';
				}
			}
			
			
			/* ==================================== Checkbox Price ==================================== */
			$checkbox_price_old = get_post_meta($product_id, 'checkbox_price', true);
			
			$params['checkbox_price']['prev'] = $checkbox_price_old;
			$params['checkbox_price']['new'] = $checkbox_price;
			$params['checkbox_price']['status'] = 'no-need-to-update';
			if ( $checkbox_price_old != $checkbox_price) {
				
				$params['checkbox_price']['status'] = 'not-updated';
				if(update_post_meta( $product_id, 'checkbox_price', $checkbox_price )){
					$params['checkbox_price']['status'] = 'updated';
				}
			}
			
			$params['status'] = ['success'];
			
		}else{
			$params = ['status'=>'not_found','product'=>[]];
		}
		
		return $params;
		
	} else {
		$params = ['status'=>'mismatched endpoint.'];
		return new WP_REST_Response($params, 400);
	}
}


