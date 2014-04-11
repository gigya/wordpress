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
      var gigyaFollowParams = JSON.parse( dataEl.text() );
      gigyaFollowParams.buttons = JSON.parse(gigyaFollowParams.buttons);
      // Define the Follow Bar Plugin params object.
      var params = $.extend( true, {}, gigyaFollowParams );
      params.containerID = id;
      params.onError = GigyaWp.errHandle;

      // Load the gamification block Plugin.
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