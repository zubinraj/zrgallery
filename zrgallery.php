<?php
   /*
   Plugin Name: ZR Gallery
   Plugin URI: 
   Description: Show your wordpress gallery images in a portfolio format. Uses the wonderful jquery masonry by Desandro
   Version: 0.1
   Author: Zubin Raj
   Author URI: http://www.zubinraj.com
   License: GPL2
   */
?>
<?php

//ZRGallery_Shortcode::init();

add_action('init', 'ZRGallery_Shortcode::init');

add_action('wp_ajax_zr_gallery_get', 'zr_gallery_get_callback');
add_action('wp_ajax_nopriv_zr_gallery_get', 'zr_gallery_get_callback');


function zr_gallery_get_callback() {

	$param = stripslashes ($_POST['param']) ;
	
	echo ZRGallery_Shortcode::get_gallery_items($param);

	die(); // this is required to return a proper result
}

class ZRGallery_Shortcode {
	static $add_script;
	

	static function init() {

		//add_action('init', array(__CLASS__, 'ZRGallery_Shortcode::register_script'));
		
		// register the scripts and add an action to print if necessary
		ZRGallery_Shortcode::register_script();
		add_action('wp_footer', array(__CLASS__, 'ZRGallery_Shortcode::print_script'));

		add_shortcode('zrgallery', array(__CLASS__, 'zrgallery_shortcode_handler'));

	}

	// [zrgallery items_per_page="-1" category=""]
	static function zrgallery_shortcode_handler( $atts ) {
		
		// determines if the script has to be loaded
		self::$add_script = true;

		extract( shortcode_atts( array(
			'count' => '-1',
			'category' => '',
			'tag' => ''
		), $atts ) );

		$param = json_encode(array('category' => $category, 'tag' => $tag, 'count' => $count, 'offset' => 0));

		return ZRGallery_Shortcode::display_gallery( $param );
	}
	
	static function register_script() {

		// Register scripts
		wp_register_script('jquery', '//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js' );
		//wp_register_script('jquery-isotope', plugins_url( '/js/jquery.isotope.min.js', __FILE__ ));
		wp_register_script('jquery-masonry', plugins_url( '/js/jquery.masonry.min.js', __FILE__ ));
		//wp_register_script('modernizr-transitions', plugins_url( '/js/modernizr-transitions.js', __FILE__ ));
		wp_register_script('jquery-ui', plugins_url( '/js/jquery-ui.min-1.8.23.js', __FILE__ ));
		wp_register_script('jquery-image-gallery-load-image', plugins_url( '/js/load-image.min.js', __FILE__ ));
		wp_register_script('jquery-image-gallery', plugins_url( '/js/jquery.image-gallery.js', __FILE__ ));
		wp_register_script('zrgallery', plugins_url( '/js/zrgallery.js', __FILE__ ));
		//wp_register_script('zrgallery', plugins_url( '/js/zrgallery.js', __FILE__ ));

		// Register styles
		wp_register_style('zrgallery', plugins_url( '/css/zrgallery.css', __FILE__ ));
		wp_register_style('jquery-ui', plugins_url( '/css/jquery-ui.css', __FILE__ ));
		wp_register_style('jquery-image-gallery', plugins_url( '/css/jquery.image-gallery.css', __FILE__ ));

	}

	static function print_script() {

		// skip loading the scripts if the shortcode is not used in the page
		if ( ! self::$add_script )
			return;

		// Enqueue scripts
		wp_enqueue_script('jquery');
		//wp_enqueue_script('jquery-isotope');
		wp_enqueue_script('jquery-masonry');
		//wp_enqueue_script('modernizr-transitions');
		wp_enqueue_script('jquery-ui');
		wp_enqueue_script('jquery-image-gallery-load-image');
		wp_enqueue_script('jquery-image-gallery');
		wp_enqueue_script('zrgallery');

		// Enqueue styles
		wp_enqueue_style('zrgallery');
		wp_enqueue_style('jquery-ui');
		wp_enqueue_style('jquery-image-gallery');

	}
	
	static function display_gallery( $param ) {

		$str = '';
		$str .= '<div id="gallery-container">';

		$str .= ZRGallery_Shortcode::get_gallery_items( $param );
	
		$str .= '</div>';
		
		$str .= '<div style="clear:both"></div>';
		$str .= '<div id="loading"><img src="' . plugins_url('/images/loading.gif', __FILE__) . '" /></div>';
	
		return $str;

	
	}
	
	static function get_gallery_items($param)
	{
		//var_dump ($param);
	
		$p = json_decode($param, true);

		if (!is_numeric($p['count']))
			$p['count'] = 20; //default
		
		if (!is_numeric($p['offset']))
			$p['offset'] = 0; //default
		
		
		$posts = ZRGallery_Shortcode::get_gallery_items_array($p['category'], $p['tag'], $p['count'], $p['offset']); 

		
		if (count($posts) <= 0)
		{
			header('HTTP/1.0 404 Not Found');
			echo "<h5>Sorry, there are no items to display.</h5>";
			return '';
		}
		
		$str = '';

		// calculate the next offset
		$nextOffset = 0;
		if ($p['count'] == -1)
		{
			// just an imaginary high number, so that it doesn't try to load again
			$p['count'] =  9999999;   
			$p['offset'] =  9999999;   
		}
		else
		{
			$p['offset'] += $p['count'];
		}
		
		// re-encode with the new params
		$p = json_encode(array('category' => $p['category'], 'tag' => $p['tag'], 'count' => $p['count'], 'offset' => $p['offset']));
		
		$str .= '<span id="zrgallery_params">' . $p . '</span>';

		foreach($posts as $post){
			$str .= str_replace("'","'",$post);
		}

		return $str;
	}

	static function get_gallery_items_array( $category_id, $tag, $count, $offset )
	{
		$items = array();

		$args = array ( 
						'category'		=> $category_id,
						'numberposts' 	=> $count, 
						'offset'		=> $offset,
						'post_status' 	=> 'publish'
					); 
						
		$posts = get_posts( $args );
		
		if ($posts) 
		{
			foreach ( $posts as $post ) 
			{
				$permalink = get_permalink( $post->ID );
				
				$title = apply_filters( 'the_title' , $post->post_title );

				$gallery_rel = 'gallery|' . $permalink;

				// get the attachments
				$attargs = array( 
								'post_type' => 'attachment', 
								'numberposts' => -1, 
								'post_status' => null, 
								'post_parent' => $post->ID 
								); 
				
				$attachments = get_posts($attargs);
				
				if ($attachments) 
				{
					foreach ( $attachments as $attachment ) 
					{
						$image_thumb = wp_get_attachment_image_src( $attachment->ID, 'medium' );
						$image_full = wp_get_attachment_image_src ( $attachment-> ID, 'original' );

						// sanity checks
						if (!$image_thumb) echo 'No thumb';
						if (!$image_full) echo 'No full';
						
						//echo apply_filters( 'the_title' , $attachment->post_title );
						//the_attachment_link( $attachment->ID , false );
						
						$item = '<div class="gallery-item">
									<a href="' . $image_full[0] . '" title="' . $title . '" rel="' . $gallery_rel . '" >
									<img title="' . $title . '" alt="' . $title . '" src="' . $image_thumb[0] . '" />
									<div class="overlay" title="' . $title . '" href="javascript:void(0);">
										<div class="content">
											<h5>' . $title . '</h5>
										</div>
									</div>
									</a>
								</div>';		
					
						//echo $item;

						//								<div>
						//									<div class="social-button facebook"><iframe src="//www.facebook.com/plugins/like.php?href='. $parent_permalink .'&amp;send=false&amp;layout=button_count&amp;width=100&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font&amp;height=20" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px;"allowTransparency="true"></iframe></div>
						//								</div>
						
						array_push($items, $item);
					}
				}
					

			}

		}
		
		return $items;
		
	}
	
	/*
	static function get_gallery_items_array($category, $tag, $count, $offset)
	{
		$args = array
					( 'post_type' 		=> 'attachment', 
						'numberposts' 	=> $count, 
						'offset'		=> $offset, 
						'post_status' 	=> null, 
						'post_parent' 	=> null
					); 
						//'orderby'		=> 'post_date',
						//'order'       	=> 'DESC'); 
						
		$attachments = get_posts( $args );
		
		$items = array();
			
		if ($attachments) 
		{
			foreach ( $attachments as $attachment ) 
			{
				// Skip images not attached to a post
				if (!$attachment->post_parent) continue;
				
				$parent_id = $attachment->post_parent;

				// Skip images in posts that are not published
				if (get_post_status ( $parent_id ) != 'publish') continue;
				
				$parent_title = get_the_title( $parent_id );
				$parent_permalink = get_permalink( $parent_id );
				
				$image_thumb = wp_get_attachment_image_src( $attachment->ID, 'medium' );
				$image_full = wp_get_attachment_image_src ( $attachment-> ID, 'original' );

				$gallery_rel = 'gallery|' . $parent_permalink;
				
				// Get categories
				$post_categories = wp_get_post_categories( $parent_id );
				$categories_str = '';

				// Filter based on categories
				if ($category != '') {
					if (!in_array($category, $post_categories)) continue;
				}
				
				foreach($post_categories as $c)
				{
					$cat = get_category( $c );
					$categories_str .= $cat->slug;
				}

				$item = '<div class="gallery-item ' . $categories_str . '">
							<a href="' . $image_full[0] . '" title="' . $parent_title . '" rel="' . $gallery_rel . '" >
							<img title="' . $parent_title . '" alt="' . $parent_title . '" src="' . $image_thumb[0] . '" />
							<div class="overlay" title="' . $parent_title . '" href="javascript:void(0);">
								<div class="content">
									<h5>' . $parent_title . '</h5>
								</div>
							</div>
							</a>
						</div>';		
			
				//echo $item;

	//								<div>
	//									<div class="social-button facebook"><iframe src="//www.facebook.com/plugins/like.php?href='. $parent_permalink .'&amp;send=false&amp;layout=button_count&amp;width=100&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font&amp;height=20" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:20px;"allowTransparency="true"></iframe></div>
	//								</div>
				
				array_push($items, $item);

			}

		}
		
		return $items;
		
	}
	*/
	

}
?>