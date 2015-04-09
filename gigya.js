var GigyaWp = GigyaWp || {};

(function ( $ ) {

// --------------------------------------------------------------------

  window.__gigyaConf = gigyaParams;

// --------------------------------------------------------------------

  $( document ).ready( function () {
    // jQueryUI dialog element.
    $( 'body' ).append( '<div id="dialog-modal"></div>' );

    GigyaWp.logout = function ( response ) {
      if ( typeof response.context.id !== 'undefined' ) {
        location.replace( gigyaParams.logoutUrl );
      }
    }
    // If user is logged out from gigya and logged in to WP, log out from WP
      if ( $('body').hasClass('logged-in') || $('body').hasClass('wp-admin')) {
          var user = setTimeout( function() {
              gigya.socialize.getUserInfo();
          }, 500);
          if ( typeof user === 'undefined' )  {
              location.replace( gigyaParams.logoutUrl );
          }
      }
    // SSO:
    //$('a[href*="action=logout"]').click( function (e)  {
    //    e.preventDefault();
    //    gigya.socialize.logout();
    //    setTimeout( function(){ location.replace( gigyaParams.logoutUrl ); }, 500);
    //  });
  } );

// --------------------------------------------------------------------

  GigyaWp.errHandle = function ( errEvent ) {
//    console.log( errEvent );
    return false;
  }

// --------------------------------------------------------------------

  GigyaWp.redirect = function () {
    if ( location.pathname.indexOf( 'wp-login.php' ) != -1 ) {
      // Redirect after login page.
      if ( typeof gigyaLoginParams != 'undefined' ) {
        location.replace( gigyaLoginParams.redirect );
      }
      else if ( typeof gigyaRaasParams != 'undefined' ) {
        location.replace( gigyaRaasParams.redirect );
      }
    }
    else {
      // Refresh.
      location.reload();
    }
  };
    GigyaWp.getEssentialParams = function(gigyaObj) {
        var esData = {};
        var primitive = ['string', 'number', 'boolean'];
        $.each(gigyaObj.response, function(key, val) {
            if ($.inArray($.type(val), primitive) >= 0) {
                esData[key] = val;
            }
        });
        return esData;
    };

// --------------------------------------------------------------------





})( jQuery );

