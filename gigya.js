var GigyaWp = GigyaWp || {};

(function ( $ ) {

// --------------------------------------------------------------------

  {
    connectWithoutLoginBehavior: gigyaParams.connectWithoutLoginBehavior
  }

// --------------------------------------------------------------------

  $( document ).ready( function () {
    // jQueryUI dialog element.
    $( 'body' ).append( '<div id="dialog-modal"></div>' );

    GigyaWp.logout = function ( response ) {
      if (typeof response.context.id !== 'undefined') {
        document.location = gigyaParams.logoutUrl;
      }
    }
  } );

// --------------------------------------------------------------------

  GigyaWp.errHandle = function ( errEvent ) {
    console.log( errEvent );
  }

// --------------------------------------------------------------------

})( jQuery );

