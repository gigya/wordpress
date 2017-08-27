(function ( $ ) {

  $( document ).ready( function () {

// --------------------------------------------------------------------

    /**
     * Get image object.
     * @returns {{type: string, href: *}}
     */
    var getImage = function ( params ) {
      var image = {
        type: 'image',
        href: params.ua.linkBack
      };

      if ( typeof $( 'meta[property="og:image"]' ).length > 0 ) {
        // Image source taken from og meta tag.
        image.src = $( 'meta[property="og:image"]' ).attr( 'content' );
      }
      else {
        // Image source taken from data.
        image.src = params.ua.imageURL;
      }

      return image;
    }

// --------------------------------------------------------------------

    /**
     * Get user action.
     * @returns {gigya.services.socialize.UserAction}
     */
    var getUserAction = function ( params ) {
      var ua = new gigya.services.socialize.UserAction();

//			if (typeof params.userMessage !== 'undefined') {
//				ua.setUserMessage(params.userMessage);
//			}

      // Set link back.
      var linkBack = typeof $( 'meta[property="og:url"]' ).attr( 'content' ) !== 'undefined' ? $( 'meta[property="og:url"]' ).attr( 'content' ) : params.ua.linkBack;
      if ( linkBack !== '' ) {
        ua.setLinkBack( linkBack );
      }

      // Set title.
      var postTitle = typeof $( 'meta[property="og:title"]' ).attr( 'content' ) !== 'undefined' ? $( 'meta[property="og:title"]' ).attr( 'content' ) : params.ua.postTitle;
      if ( postTitle !== '' ) {
        ua.setTitle( postTitle );
      }

      // Set action link.
      if ( postTitle !== '' && linkBack !== '' ) {
        ua.addActionLink( postTitle, linkBack );
      }

      // Set subtitle.
//			if (typeof params.subtitle !== 'undefined') {
//				ua.setSubtitle(params.subtitle);
//			}

      // Set the description.
      var postDesc = typeof $( 'meta[property="og:description"]' ).attr( 'content' ) !== 'undefined' ? $( 'meta[property="og:description"]' ).attr( 'content' ) : params.ua.postDesc;
      if ( postDesc !== '' ) {
        ua.setDescription( postDesc );
      }

      // Set the image.
      ua.addMediaItem( getImage( params ) );

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
      var params = JSON.parse( dataEl.text() );

      // Define the Share Bar Plugin params object.
      params.containerID = id;
      params.context = { id: id };
      params.userAction = getUserAction( params );
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