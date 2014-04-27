(function ( $ ) {

  $( document ).ready( function () {

// --------------------------------------------------------------------

    /**
     * Show the Gigya's Follow Bar block.
     * @param id
     */
    var showFollow = function ( id ) {

      // Get the data.
      var dataEl = $( '#' + id ).next( 'script.data-follow' );
      var params = JSON.parse( dataEl.text() );

      // Define the Follow Bar Plugin params object.
      params.buttons = JSON.parse( params.buttons );
      params.containerID = id;
      params.context = { id: id };
      params.onError = GigyaWp.errHandle;

      // Load the follow bar block Plugin.
      gigya.socialize.showFollowBarUI( params );

    };

// --------------------------------------------------------------------

    /**
     * Start.
     */
    $( '.gigya-follow-widget' ).each( function ( index, value ) {
      var id = 'gigya-follow-widget-' + index;
      $( this ).attr( 'id', id );
      showFollow( id );
    } );

// --------------------------------------------------------------------

  } );
})( jQuery );