<?php

/*
Plugin Name: Bricks Custom Elements
Plugin URI: https://github.com/Widdin/wp-bricks-elements
Description: Adds custom elements to Bricks.
Author: Simon Vidman
Author URI: https://github.com/Widdin/
Version: 1.0
*/

add_action('plugins_loaded', 'bricks_custom_elements_init');

		
function bricks_custom_elements_init()
{	
	if (get_template() !==  'bricks') { 
		return;
	}

	add_action( 'init', function() {
		foreach ( glob(plugin_dir_path(__FILE__) . "elements/*/*.php" ) as $filename) {
			\Bricks\Elements::register_element( $filename );
		}
	}, 11 );
}
