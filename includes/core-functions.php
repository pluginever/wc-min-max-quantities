<?php
//function prefix wc_min_max_quantities

function wc_minmax_quantities_get_settings($key, $default = false,  $section='wc_min_max_quantities_simple'){
	$settings = get_option($section, []);

	return isset($settings[$key])? $settings[$key]: $default; 
}
