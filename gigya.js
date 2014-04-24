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
//      if ( response.source == "showCommentsUI" ) {
//        return false;
//      }

      document.location = gigyaParams.logoutUrl;
    }
  } );

// --------------------------------------------------------------------

  GigyaWp.errHandle = function ( errEvent ) {
    console.log( errEvent );
  }

// --------------------------------------------------------------------

})( jQuery );

