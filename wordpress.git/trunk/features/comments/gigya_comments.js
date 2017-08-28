(function ( $ ) {

  $( document ).ready( function () {

// --------------------------------------------------------------------

    var showComments = function ( cid, gigyaCommentsParams ) {

      if ( typeof gigyaCommentsParams == 'undefined' || typeof cid == 'undefined' ) {
        return false;
      }

      gigyaCommentsParams.containerID = cid;
      gigyaCommentsParams.context = {id: cid};
      gigyaCommentsParams.onError = GigyaWp.errHandle;
      gigya.comments.showCommentsUI( gigyaCommentsParams );
    }
// --------------------------------------------------------------------

    var showRating = function ( rid, cid, gigyaCommentsParams ) {

      var ratingParams = {
        categoryID      : gigyaCommentsParams.categoryID,
        streamID        : gigyaCommentsParams.streamID,
        containerID     : rid,
        linkedCommentsUI: cid
      }
      gigya.comments.showRatingUI( ratingParams );
    }

// --------------------------------------------------------------------

    $( '.gigya-comments-widget' ).each( function ( index, value ) {
      var cid = 'gigya-comments-widget-' + index;
      $( this ).attr( 'id', cid );

      // Get the data.
      var dataEl = $( '#' + cid ).next( 'script.data-comments' );
      var gigyaCommentsParams = JSON.parse( dataEl.text() );

      showComments( cid, gigyaCommentsParams );

      if ( typeof gigyaCommentsParams != 'undefined' && gigyaCommentsParams.rating == true ) {
        var rid = 'gigya-rating-widget-' + index;
        $( this ).siblings( '.gigya-rating-widget' ).attr( 'id', rid );
        showRating( rid, cid, gigyaCommentsParams );
      }
    } );

// --------------------------------------------------------------------

//    if ( typeof gigyaCommentsParams == 'undefined' && gigyaCommentsParams.rating == true ) {
//      $( '.gigya-rating-widget' ).each( function ( index, value ) {
//        var cid = 'gigya-rating-widget-' + index;
//        $( this ).attr( 'id', cid );
//        showRating( id );
//      } );
//    }

// --------------------------------------------------------------------

  } );
})( jQuery );