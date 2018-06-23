<?php
/*
Plugin Name: Cookie-Based Layout
Plugin URI:  https://github.com/andrewklimek/
Description: 
Version:     1.0.0
Author:      Andrew J Klimek
Author URI:  https://andrewklimek.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Cookie-Based Layout is free software: you can redistribute it and/or modify 
it under the terms of the GNU General Public License as published by the Free 
Software Foundation, either version 2 of the License, or any later version.

Cookie-Based Layout is distributed in the hope that it will be useful, but without 
any warranty; without even the implied warranty of merchantability or fitness for a 
particular purpose. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with 
Cookie-Based Layout. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/


// add_shortcode( 'cookie_layout', 'ajk_cookie_layout' );
add_filter( 'the_content', 'ajk_cookie_layout_custom_shortcode_parsing', 9 );// Run this early to avoid wpautop
// add_filter( 'widget_text', 'ajk_cookie_layout_custom_shortcode_parsing', 9 );// also process text and HTML widgets
	
function ajk_cookie_layout_custom_shortcode_parsing( $c ) {
    
	if ( false === strpos($c, '[cookie_layout') ) return $c;
		
    $p = "/\[cookie_layout([^\]]*)\]((?:[^\[]*|\[(?!\/cookie_layout\]))*)\[\/cookie_layout\]/";
	
    $tag = "cookie_layout";
    $c = preg_replace_callback(
		"/\[{$tag}([^\]]*)\]((?:[^\[]*|\[(?!\/{$tag}\]))*)\[\/{$tag}\]/",
		function($m){ return ajk_cookie_layout( shortcode_parse_atts($m[1]), $m[2], $tag );},
		$c );

    return $c;
}

function ajk_cookie_layout( $a, $c ) {
	
	if ( ( !isset($a['order']) || !is_numeric($a['order']) ) && ( !isset($a['only']) || !is_numeric($a['only']) ) )
	{
		return '<p>Please add order or only parameters to the [cookie_layout] shortcode.</p>' . do_shortcode($c);
	}

	// assemble divs
	$out = '<div';
	if ( isset($a['order']) ) $out .= ' data-visitor-order=' . $a['order'];
	if ( isset($a['only']) ) $out .= ' data-visitor-only=1';
	if ( isset($a['class']) ) $out .= " class='{$a['class']}'";
	if ( isset($a['id']) ) $out .= " id='{$a['id']}'";
	$out .= '>';
	$out .= $c;
	$out .= '</div>';
	
	// error_log('$c');
	// error_log($c);
	// error_log('$out');
	// error_log($out);
	
	// add JS if it hasn't been done yet
	if ( ! defined('AJK_COOKIE_LAYOUT_JS_HAS_BEEN_ADDED') )
	{
		// set flag so it can be checked on the next shortcode and short circuit this block
		define('AJK_COOKIE_LAYOUT_JS_HAS_BEEN_ADDED', true);
		
		// add JS to be printed
		add_action('wp_print_footer_scripts', 'print_ajk_cookie_layout_js');
	}
	
	return $out;
}


function print_ajk_cookie_layout_js()
{
	$cookie = 'cookiebasedlayout' . get_queried_object_id();
	?>
	<script>(function(){var i,e,n='3',a='[data-visitor-order="',c='<?php echo $cookie ?>=';if(~document.cookie.indexOf(c+n)){if(e=document.querySelectorAll('[data-visitor-only]'))for(i=0;i<e.length;++i)e[i].outerHTML='';}else{if(e=document.querySelectorAll(a+'0"]'))for(i=0;i<e.length;++i)e[i].outerHTML='';for(i=1;e=document.querySelector(a+i+'"]');++i)e.parentElement.appendChild(e);i=1;if(~document.cookie.indexOf(c))i+=parseInt(document.cookie.split(c)[1].slice(0,1));document.cookie=c+i+'; max-age='+3e7+'; path=/';}})();</script>
	<?php
}

/*

Here's the not-as-minified script

(function(){
var i,e,n='3',a='[data-visitor-order="',c='ambientsleepingpill=';

if(~document.cookie.indexOf(c+n)){
	// person has visited n times, hide visitor-only elements
	if(e=document.querySelectorAll('[data-visitor-only]'))
		for(i=0;i<e.length;++i)
			e[i].outerHTML='';
}else{
	// person hasn't visited n times yet, show them custom page
	// hide elements
	if(e=document.querySelectorAll(a+'0"]'))	
		for(i=0;i<e.length;++i)
			e[i].outerHTML='';
	// re-order elements
	for(i=1;e=document.querySelector(a+i+'"]');++i)
		e.parentElement.appendChild(e);
	// increment the cookie
	i=1;
	if(~document.cookie.indexOf(c))
		i+=parseInt(document.cookie.split(c)[1].slice(0,1));
	document.cookie=c+i+'; max-age='+3e7+'; path=/';
}
})();
*/