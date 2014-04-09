(function ( $ ) {

  $( document ).ready( function () {

// --------------------------------------------------------------------

    /**
     * Get image object.
     * @returns {{type: string, href: *}}
     */
    var getImage = function ( gigyaShareParams ) {
      var image = {
        type: 'image',
        href: gigyaShareParams.ua.linkBack
      };

      if ( typeof $( 'meta[property="og:image"]' ).length > 0 ) {
        // Image source taken from og meta tag.
        image.src = $( 'meta[property="og:image"]' ).attr( 'content' );
      }
      else {
        // Image source taken from data.
        image.src = gigyaShareParams.ua.imageURL;
      }

      return image;
    }

// --------------------------------------------------------------------

    /**
     * Get user action.
     * @returns {gigya.services.socialize.UserAction}
     */
    var getUserAction = function ( gigyaShareParams ) {
      var ua = new gigya.services.socialize.UserAction();

//			if (typeof gigyaShareParams.userMessage !== 'undefined') {
//				ua.setUserMessage(gigyaShareParams.userMessage);
//			}

      // Set link back.
      var linkBack = typeof $( 'meta[property="og:url"]' ).attr( 'content' ) !== 'undefined' ? $( 'meta[property="og:url"]' ).attr( 'content' ) : gigyaShareParams.ua.linkBack;
      if ( linkBack !== '' ) {
        ua.setLinkBack( linkBack );
      }

      // Set title.
      var postTitle = typeof $( 'meta[property="og:title"]' ).attr( 'content' ) !== 'undefined' ? $( 'meta[property="og:title"]' ).attr( 'content' ) : gigyaShareParams.ua.postTitle;
      if ( postTitle !== '' ) {
        ua.setTitle( postTitle );
      }

      // Set action link.
      if ( postTitle !== '' && linkBack !== '' ) {
        ua.addActionLink( postTitle, linkBack );
      }

      // Set subtitle.
//			if (typeof gigyaShareParams.subtitle !== 'undefined') {
//				ua.setSubtitle(gigyaShareParams.subtitle);
//			}

      // Set the description.
      var postDesc = typeof $( 'meta[property="og:description"]' ).attr( 'content' ) !== 'undefined' ? $( 'meta[property="og:description"]' ).attr( 'content' ) : gigyaShareParams.ua.postDesc;
      if ( postDesc !== '' ) {
        ua.setDescription( postDesc );
      }

      // Set the image.
      ua.addMediaItem( getImage( gigyaShareParams ) );

      return ua;
    }

// --------------------------------------------------------------------

    /**
     * Show the Gigya's share bar.
     * @param settings
     */
    var showShareBar = function ( id ) {

      // Get the data.
      var dataEl = $( '#' + id ).next( 'script.data-share' );
      var gigyaShareParams = JSON.parse( dataEl.text() );

      // Define the Share Bar Plugin params object.
      var params = $.extend( true, {}, gigyaShareParams );
      params.containerID = id;
      params.userAction = getUserAction( gigyaShareParams );
      params.onError = GigyaWp.errHandle;

      delete params.ua;

      // Load the Share Bar Plugin.
      gigya.socialize.showShareBarUI( params );

    };

// --------------------------------------------------------------------

    $( '.gigya-share-widget' ).each( function ( index, value ) {
      var id = 'gigya-share-widget-' + index;
      $( this ).attr( 'id', id );
      showShareBar( id );
    } );

// --------------------------------------------------------------------

  } );
})( jQuery );