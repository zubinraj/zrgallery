
	var $container = jQuery('#gallery-container');


    $container.imagesLoaded( function(){
		$container.isotope({
			itemSelector : '.gallery-item',
			isAnimated : true
			//isFitWidth : true,
			//resizable: false // disable normal resizing
			// set columnWidth to a percentage of container width
			//masonry: { columnWidth: $container.width() / 3 }
			
		});
		
    });
	

	// more requests
	jQuery("#more").click(function(event){
		loadMore ();
		event.preventDefault();
	});


	
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
			url: "/photos/wp-admin/admin-ajax.php",
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

		// ensure that images load before adding to masonry layout
		$newElems.imagesLoaded(function(){

			$container.append( $newElems ).isotope( 'appended', $newElems );

			// show elems now they're ready
			$newElems.animate({ opacity: 1 });
			
			// reset this flag so that the next scroll can pull in more items
			inAction = false;

		}); 

	}
	
	
/*	function getMoreLink(html) {
		// Get the more link from the html of the new page, else get from current page
		if (html) {
			return (jQuery(html).find("#more a").attr("href"));
		}
		else {
			return (jQuery("#more a").attr("href"));
		}
	}


	
	function setMoreLink(newlink) {
		jQuery("#more a").attr("href", newlink)
	}
*/	
	function toggleLoadingImg() {
		//alert ('toggle');
		jQuery("#loading").toggle();
	}

	function toggleLoadMoreButton() {
		jQuery("#more").toggle();
	}


/* start jquery image gallery */

/* fade in the footer on hover */
/*
jQuery(function() {
   jQuery('.ui-dialog-footer').css('display','none');
   
   jQuery('.ui-dialog-content').hover(function() {
      alert ('in');
      jQuery('.gallery-dialog-fullscreen .ui-dialog-footer',this).fadeIn();
            
      }, function() { jQuery('.gallery-dialog-fullscreen .ui-dialog-footer').fadeOut();
   });
});	
*/

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
	
	
/*	
    // Initialize the theme switcher:
    jQuery('#theme-switcher').change(function () {
        var theme = jQuery('#theme');
        theme.prop(
            'href',
            theme.prop('href').replace(
                /[\w\-]+\/jquery-ui.css/,
                jQuery(this).val() + '/jquery-ui.css'
            )
        );
    });

    // Create a buttonset out of the checkbox options:
    jQuery('#buttonset').buttonset();

    // Listen to options changes:
    jQuery('#buttonset input, #effect').change(function () {
        jQuery('#gallery').imagegallery('option', {
            show: jQuery('#effect').val(),
            hide: jQuery('#effect').val(),
            fullscreen: jQuery('#option-fullscreen').is(':checked'),
            slideshow: jQuery('#option-slideshow').is(':checked') && 5000
        });
    });

    // Enable real fullscreen mode:
    jQuery('#option-fullscreen').click(function () {
        var checkbox = jQuery(this),
            root = document.documentElement;
        if (checkbox.is(':checked')) {
            if (root.webkitRequestFullScreen) {
                root.webkitRequestFullScreen(
                    window.Element.ALLOW_KEYBOARD_INPUT
                );
            } else if (root.mozRequestFullScreen) {
                root.mozRequestFullScreen();
            }
        } else {
            (document.webkitCancelFullScreen ||
                document.mozCancelFullScreen ||
                jQuery.noop).apply(document);
        }
    });

    // Load images via flickr for demonstration purposes:
    jQuery.ajax({
        url: 'http://api.flickr.com/services/rest/',
        data: {
            format: 'json',
            method: 'flickr.interestingness.getList',
            api_key: '7617adae70159d09ba78cfec73c13be3'
        },
	    dataType: 'jsonp',
        jsonp: 'jsoncallback'
    }).done(function (data) {
        var gallery = jQuery('#gallery'),
            url;
        jQuery.each(data.photos.photo, function (index, photo) {
            url = 'http://farm' + photo.farm + '.static.flickr.com/' +
                photo.server + '/' + photo.id + '_' + photo.secret;
            jQuery('<a rel="gallery"/>')
                .append(jQuery('<img>').prop('src', url + '_s.jpg'))
                .prop('href', url + '_b.jpg')
                .prop('title', photo.title)
                .appendTo(gallery);
        });
    });
*/

/* end jquery image gallery */


});
