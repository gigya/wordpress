(function ( $ ) {

  $( document ).ready( function () {

// --------------------------------------------------------------------

    /**
     * Get image object.
     * @returns {{type: string, href: *}}
     */
    var getImage = function ( gigyaReactionsParams ) {
      var image = {
        type: 'image',
        href: gigyaReactionsParams.ua.linkBack
      };

      if ( typeof $( 'meta[property="og:image"]' ).length > 0 ) {
        // Image source taken from og meta tag.
        image.src = $( 'meta[property="og:image"]' ).attr( 'content' );
      }
      else {
        // Image source taken from data.
        image.src = gigyaReactionsParams.ua.imageURL;
      }

      return image;
    }

// --------------------------------------------------------------------

    /**
     * Get user action.
     * @returns {gigya.services.socialize.UserAction}
     */
    var getUserAction = function ( gigyaReactionsParams ) {
      var ua = new gigya.services.socialize.UserAction();

//			if (typeof gigyaReactionsParams.userMessage !== 'undefined') {
//				ua.setUserMessage(gigyaReactionsParams.userMessage);
//			}

      // Set link back.
      var linkBack = typeof $( 'meta[property="og:url"]' ).attr( 'content' ) !== 'undefined' ? $( 'meta[property="og:url"]' ).attr( 'content' ) : gigyaReactionsParams.ua.linkBack;
      if ( linkBack !== '' ) {
        ua.setLinkBack( linkBack );
      }

      // Set title.
      var postTitle = typeof $( 'meta[property="og:title"]' ).attr( 'content' ) !== 'undefined' ? $( 'meta[property="og:title"]' ).attr( 'content' ) : gigyaReactionsParams.ua.postTitle;
      if ( postTitle !== '' ) {
        ua.setTitle( postTitle );
      }

      // Set action link.
//      if ( postTitle !== '' && linkBack !== '' ) {
//        ua.addActionLink( postTitle, linkBack );
//      }

      // Set subtitle.
//			if (typeof gigyaReactionsParams.subtitle !== 'undefined') {
//				ua.setSubtitle(gigyaReactionsParams.subtitle);
//			}

      // Set the description.
      var postDesc = typeof $( 'meta[property="og:description"]' ).attr( 'content' ) !== 'undefined' ? $( 'meta[property="og:description"]' ).attr( 'content' ) : gigyaReactionsParams.ua.postDesc;
      if ( postDesc !== '' ) {
        ua.setDescription( postDesc );
      }

      // Set the image.
      ua.addMediaItem( getImage( gigyaReactionsParams ) );

      return ua;
    }

// --------------------------------------------------------------------

    /**
     * Show the Gigya's reactions bar.
     * @param settings
     */
    var showReactionsBar = function ( id ) {

      // Get the data.
      var dataEl = $( '#' + id ).next( 'script.data-reactions' );
      var gigyaReactionsParams = JSON.parse( dataEl.text() );

      // Define the Reactions Bar Plugin params object.
      var params = $.extend( true, {}, gigyaReactionsParams );
      params.containerID = id;
      params.userAction = getUserAction( gigyaReactionsParams );
      params.onError = GigyaWp.errHandle;

      delete params.ua;
      delete params.reactionsButtons;

      // Load the Reactions Bar Plugin.
      gigya.socialize.showReactionsBarUI( params );

    };

// --------------------------------------------------------------------

    $( '.gigya-reactions-widget' ).each( function ( index, value ) {
      var id = 'gigya-reactions-widget-' + index;
      $( this ).attr( 'id', id );
      showReactionsBar( id );
    } );

// --------------------------------------------------------------------

  } );
})( jQuery );