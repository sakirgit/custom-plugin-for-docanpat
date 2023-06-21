<?php

function navigation_menu_shortcode($atts) {
    $atts = shortcode_atts(array(
        'menu_id' => ''
    ), $atts);

    // Get the menu by ID
    $menu_id = intval($atts['menu_id']);
    $menu = wp_get_nav_menu_items($menu_id);

    if (!$menu) {
        return ''; // Return empty string if menu not found
    }

    // Generate the menu HTML
    $output = '<ul class="navigation-menu">';
    $parent_menu_items = array();

    foreach ($menu as $item) {
        if ($item->menu_item_parent == 0) {
            // Parent menu item
            $parent_menu_items[$item->ID] = array(
                'title' => $item->title,
                'url' => $item->url,
                'children' => array()
            );
        } else {
            // Submenu item
            $parent_id = $item->menu_item_parent;
            if (array_key_exists($parent_id, $parent_menu_items)) {
                $parent_menu_items[$parent_id]['children'][] = $item;
            }
        }
    }

    foreach ($parent_menu_items as $parent_item) {
		 
        if (!empty($parent_item['children'])) {
			$output .= '<li class="parent-item"><a href="' . $parent_item['url'] . '">' . $parent_item['title'] . ' <span class="drop">&#9660;</span></a>';

            $output .= '<ul class="submenu">';
            foreach ($parent_item['children'] as $child_item) {
                $output .= '<li class="child-item"><a href="' . $child_item->url . '">' . $child_item->title . '</a></li>';
            }
            $output .= '</ul>';
        }else{
        $output .= '<li class="parent-item"><a href="' . $parent_item['url'] . '">' . $parent_item['title'] . '</a>';
		  }

        $output .= '</li>';
    }

    $output .= '</ul>';

    return $output;
}
add_shortcode('navigation_menu', 'navigation_menu_shortcode');


