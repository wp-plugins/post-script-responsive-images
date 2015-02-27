<?php

defined('ABSPATH') or die("Cannot Access This File Directly");

/*!
 * @wordpress-plugin
 * Plugin Name:		Post Script Responsive Images
 * Plugin URI:		//www.p-stevenson.com
 * Description:		SRCSET responsive images on wordpress for content images. | A special thanks to Joe Lencioni (http://shiftingpixel.com) for use of his image resizing script!
 * Version:		1.0.2
 * Author:		Peter Stevenson
 * Author URI:		//www.p-stevenson.com
 * License: 		GPL-2.0+
 * License URI:		http://www.gnu.org/licenses/gpl-2.0.txt
 */

/* DYNAMIC IMAGE
================================================== */
function ps_image_resize( $data = array( ) ){

	if(!$data['width']){ $data['width']='1024'; }
	if(!$data['height']){ $data['height']='1024'; }
	if(!$data['cropratio']){ $data['cropratio']=''; }
	if(!$data['color']){ $data['color']=''; }
	if(!$data['quality']){ $data['quality']='90'; }
	if(!$data['image']){ $data['image']=''; }

	$baseURL = plugins_url().'/post-script-responsive-images/';
	$responsiveSizes = array(1200,960,768,480,320);
	$returnURL = '';

	$returnURL .= 'srcset="';
		for($i = 0; $i < count($responsiveSizes); ++$i):
			if( $responsiveSizes[$i]<=intval($data['width']) ):
				$returnURL .= $baseURL;
				$returnURL .= 'slir/';
				$returnURL .= 'w' . $responsiveSizes[$i];
				$returnURL .= '-h' . $data['height'];
				$returnURL .= '-c' . $data['cropratio'];
				$returnURL .= '-b' . $data['color'];
				$returnURL .= '-q' . $data['quality'];
				$returnURL .= '/' . $data['image'];
				$returnURL .= ' ' . $responsiveSizes[$i] . 'w';
				if( $i+1 < count($responsiveSizes) ):
				$returnURL .= ', ';
				endif;
			endif;
		endfor;
	$returnURL .= '"';

	$returnURL .= ' '; // ADD SPACE

	$returnURL .= 'sizes="';
	$returnURL .= '100vw';
	$returnURL .= '"';

	return $returnURL;
}

/* AUTO GENERATE RESPONSIVE IMAGE
================================================== */
function ps_content_responsive_images ($content){

	preg_match_all('/<img[^>]+>/i',$content, $result); 
	$img = array();
	foreach( $result[0] as $img_tag){
		preg_match_all('/(alt|title|src|class|height|width)=("[^"]*")/i',$img_tag, $img[$img_tag]);
	}

	$imgNew = array();
	foreach( $img as $key => $image){
		$id = '';
		$width = '';
		$height = '';
		$count = $key;

		$imgNew[$count] = '<img ';
		foreach( $image[0] as $key => $attr){
			$imgNew[$count] .= $attr . ' ';
			
			if (strpos($attr,'class=') !== false) {
				preg_match('/wp-image-[0-9]+/', $attr, $id);
			}
			if (strpos($attr,'width=') !== false) {
				preg_match("/[0-9]+/",$attr,$width);
			}
			if (strpos($attr,'height=') !== false) {
				preg_match("/[0-9]+/",$attr,$height);
			}
		}
		$id = preg_replace('/wp-image-/', '', $id[0]);
		$width = $width[0];
		$height = $height[0];
		if($id){
			$imgAttr = wp_get_attachment_image_src( $id, 'full' );
			$resizeAttr = array(
				'width'=>$imgAttr[1],
				'height'=>$imgAttr[2],
				'image'=>$imgAttr[0]
			);
			if($width && $height && $width == $height){
				$resizeAttr['cropratio'] = '1:1';
			}
			if($width){
				$resizeAttr['width'] = $width;
			}
			if($height){
				$resizeAttr['height'] = $height;
			}
			$imgNew[$count] .= ps_image_resize( $resizeAttr );
		}
		$imgNew[$count] .= ' /> ';
	}
	$forCount = 0;
	foreach ($imgNew as $key => $image) {
		$content = preg_replace('~'.$result[0][$forCount].'~',  $image, $content);
		$forCount++;
	}
	
	return $content;
}
add_action('the_content', 'ps_content_responsive_images');


/* AUTO GENERATE RESPONSIVE THUMBNAIL
================================================== */
function ps_content_responsive_thumbnail( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
	if($html ===''){return;}

	$width = '';
	$height = '';

	preg_match_all('/(alt|title|src|class|height|width)=("[^"]*")/i',$html, $attrs);

	$html = '<img ';
	foreach( $attrs[0] as $key => $attr){
		$html .= $attr . ' ';
		if (strpos($attr,'width=') !== false) {
			preg_match("/[0-9]+/",$attr,$width);
		}
		if (strpos($attr,'height=') !== false) {
			preg_match("/[0-9]+/",$attr,$height);
		}
	}
	$width = $width[0];
	$height = $height[0];
	$imgAttr = wp_get_attachment_image_src( $post_thumbnail_id, 'full' );
	$resizeAttr = array(
		'width'=>$imgAttr[1],
		'height'=>$imgAttr[2],
		'image'=>$imgAttr[0]
	);
	if($width && $height && $width == $height){
		$resizeAttr['cropratio'] = '1:1';
	}
	if($width){
		$resizeAttr['width'] = $width;
	}
	if($height){
		$resizeAttr['height'] = $height;
	}
	$html .= ps_image_resize( $resizeAttr );
	$html .= ' /> ';

	return $html;
}
add_filter( 'post_thumbnail_html', 'ps_content_responsive_thumbnail',0,5);


?>