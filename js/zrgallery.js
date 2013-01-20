
	var $container = jQuery('#gallery-container');

    $container.masonry({
      itemSelector: '.gallery-item',
      //isAnimatedFromBottom: true
	  isAnimated: true
      //isAnimated: !Modernizr.csstransitions
      //columnWidth: 283,
	  });
	

	// more requests
	jQuery("#more").click(function(event){
		loadMore ();
		event.preventDefault();
	});


/*  disable ajax loading.
	
	var halfWay = (jQuery(document).height()/2);
	var inAction = false;
	var reachedEnd = false;
	
	jQuery(document).scroll(function(){
	
		halfWay = (jQuery(document).height()/2) - 500;
		
		if ( inAction == false ) {
			if(jQuery(document).scrollTop() > halfWay){
			
				loadMore();
			}
		}		
	}); 	
*/

	function loadMore()
	{
		inAction = true;
		
		var p = jQuery("#zrgallery_params").text();
		//alert (p);
		var data = {
			action: 'zr_gallery_get',
			param: p 
		};
		
		jQuery.ajax({
			type: "post",
			url: "./../wp-admin/admin-ajax.php",
			data: data,
			//cache: false,
			beforeSend: toggleLoadingImg(),		// show loading image
			success: function(html){			// handle success
				var success = processLoadMoreResponse (html);

				// reset this flag so that the next scroll can pull in more items
				//inAction = false;  // moved to the isotope function, as that is when it really completes
			},
			error: function (html) {
				//alert ('No more items to load');
				//toggleLoadMoreButton();
			},
			complete: function (html) {
				toggleLoadingImg();
			}

		});
	}

	function processLoadMoreResponse(newElements) {

		// remove the existing parameter element
		$container.find("#zrgallery_params").remove();

		//alert (newElements);
		//hide new items while they are loading
		var $newElems = jQuery( newElements ).css({ opacity: 0 });

		$container.append( $newElems ).masonry( 'appended', $newElems );

		// show elems now they're ready
		$newElems.animate({ opacity: 1 });
		
		// reset this flag so that the next scroll can pull in more items
		inAction = false;

	}
	
	
	function toggleLoadingImg() {
		//alert ('toggle');
		jQuery("#loading").toggle();
	}

	function toggleLoadMoreButton() {
		jQuery("#more").toggle();
	}


/* start jquery image gallery */

jQuery(function () {
    'use strict';

    // Initialize the Image Gallery widget:
    jQuery('#gallery-container').imagegallery();

	
	jQuery('#gallery-container').imagegallery('option', {
		show: 'fade',
		hide: 'fade',
		fullscreen: true,
		canvas: false,
		modal: true
	});
	
	


});
