var GigyaWp = GigyaWp || {};

(function ( $ ) {

// --------------------------------------------------------------------

  window.__gigyaConf = {
    connectWithoutLoginBehavior: gigyaParams.connectWithoutLoginBehavior,
    enabledProviders           : gigyaParams.enabledProviders,
    lang                       : gigyaParams.lang
  }

// --------------------------------------------------------------------

  $( document ).ready( function () {
    // jQueryUI dialog element.
    $( 'body' ).append( '<div id="dialog-modal"></div>' );

    GigyaWp.logout = function ( response ) {
      if ( typeof response.context.id !== 'undefined' ) {
        location.replace( gigyaParams.logoutUrl );
      }
    }
  } );

// --------------------------------------------------------------------

  GigyaWp.errHandle = function ( errEvent ) {
    console.log( errEvent );
  }

// --------------------------------------------------------------------

})( jQuery );

