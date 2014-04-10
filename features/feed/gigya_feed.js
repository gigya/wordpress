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
      var gigyaFeedParams = JSON.parse( dataEl.text() );

      // Define the Feed Plugin params object.
      var params = $.extend( true, {}, gigyaFeedParams );
      params.containerID = id;
      params.context = {id: id};
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