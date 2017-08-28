(function ( $ ) {

  $( document ).ready( function () {


// --------------------------------------------------------------------

    /**
     * Show the Gigya's Feed block.
     * @param id
     */
    var showActivityFeed = function ( id ) {

      // Get the data.
      var dataEl = $( '#' + id ).next( 'script.data-feed' );
      var params = JSON.parse( dataEl.text() );

      params.containerID = id;
      params.context = { id: id };
      params.onError = GigyaWp.errHandle;

      // Load the feed block Plugin.
      gigya.socialize.showFeedUI( params );

    };

// --------------------------------------------------------------------

    /**
     * Start.
     */
    $( '.gigya-feed-widget' ).each( function ( index, value ) {
      var id = 'gigya-feed-widget-' + index;
      $( this ).attr( 'id', id );
      showActivityFeed( id );
    } );

// --------------------------------------------------------------------

  } );
})( jQuery );