(function ( $ ) {

  $( document ).ready( function () {

// --------------------------------------------------------------------

    /**
     * Override default WP links to use Gigya's RaaS behavior.
     */
    var overrideLinks = function () {
      $( document ).on( 'click', 'a[href]', function ( e ) {
        var path = $( this )[0].pathname;
        var search = $( this )[0].search;
        if ( path.indexOf( 'wp-login.php' ) != -1 ) {
          switch ( search ) {

            case '':
              // Login page
              gigya.accounts.showScreenSet( {screenSet: gigyaRaasParams.raasWebScreen, mobileScreenSet: gigyaRaasParams.raasMobileScreen, startScreen: gigyaRaasParams.raasLoginScreen} );
              e.preventDefault();
              break;

            case '?action=register':
              // Register page
              gigya.accounts.showScreenSet( {screenSet: gigyaRaasParams.raasWebScreen, mobileScreenSet: gigyaRaasParams.raasMobileScreen, startScreen: gigyaRaasParams.raasRegisterScreen} );
              e.preventDefault();
              break;

            case '?action=lostpassword':
              // Lost Password page
              e.preventDefault();
              break;
          }
        }
        else if ( path.indexOf( 'profile.php' ) != -1 ) {

          // Profile page
          gigya.accounts.showScreenSet( {screenSet: gigyaRaasParams.raasProfileWebScreen, mobileScreenSet: gigyaRaasParams.raasProfileMobileScreen} );
          e.preventDefault();
        }
      } );

      // Hide the WP login screens navigation.
      $( '#login #nav' ).hide();
    }

// --------------------------------------------------------------------

    /**
     * On RaaS login with Gigya behavior.
     * @param data
     */
    var raasLogin = function ( response ) {

      if ( response.provider === 'site' ) {
        return false;
      }

      var options = {
        url     : gigyaParams.ajaxurl,
        type    : 'POST',
        dataType: 'json',
        data    : {
          data  : response,
          action: gigyaRaasParams.actionRaas
        }
      };

      var req = $.ajax( options );
      $( 'body' ).prepend( '<span class="spinner"></span>' );
      $( '.spinner' ).show();

      req.done( function ( res ) {
        if ( res.success == true ) {
          if ( location.pathname.indexOf( 'wp-login.php' ) != -1 ) {
            // Redirect.
            location.replace( gigyaRaasParams.redirect );
          }
          else {
            location.reload();
          }
        }
        else {
          if ( typeof res.data != 'undefined' ) {
            // The user didn't register, and need more field to fill.
            $( '#dialog-modal' ).html( res.data.msg );
            $( '#dialog-modal' ).dialog( { modal: true } );

          }
        }
      } );

      req.fail( function ( jqXHR, textStatus, errorThrown ) {
        console.log( errorThrown );
      } );

      $( "#dialog-modal" ).on( "dialogclose", function ( event, ui ) {
        location.reload();
      } );
    }

// --------------------------------------------------------------------

    var raasInit = function () {
      // Override default WP links to use Gigya's RaaS behavior.
      if ( gigyaRaasParams.raasOverrideLinks > 0 ) {
        overrideLinks();
      }

      // Embed Screens.
      if ( location.search.indexOf( 'admin=true' ) == -1 ) {
        gigya.accounts.showScreenSet( {screenSet: gigyaRaasParams.raasWebScreen, mobileScreenSet: gigyaRaasParams.raasMobileScreen, startScreen: gigyaRaasParams.raasLoginScreen, containerID: gigyaRaasParams.raasLoginDiv} );
        gigya.accounts.showScreenSet( {screenSet: gigyaRaasParams.raasWebScreen, mobileScreenSet: gigyaRaasParams.raasMobileScreen, startScreen: gigyaRaasParams.raasRegisterScreen, containerID: gigyaRaasParams.raasRegisterDiv} );
        gigya.accounts.showScreenSet( {screenSet: gigyaRaasParams.raasProfileWebScreen, mobileScreenSet: gigyaRaasParams.raasProfileMobileScreen, containerID: gigyaRaasParams.raasProfileDiv} );
      }

      // Attach event handlers.
      if ( typeof GigyaWp.regEvents === 'undefined' ) {

        // Raas Login.
        gigya.accounts.addEventHandlers( {
          onLogin : raasLogin,
          onLogout: GigyaWp.logout
        } );

        GigyaWp.regEvents = true;
      }
    }

// --------------------------------------------------------------------

    raasInit();

// --------------------------------------------------------------------

    // Check Connection to RaaS
//		function AccountInfoResponse(response) {
//			if (response.errorCode == 0) {
//				console.log(response);
//			}
//			else {
//				console.log('Gigya RaaS Error: ' + response.errorMessage);
//			}
//		}
//
//		gigya.accounts.getAccountInfo({ callback: AccountInfoResponse });

  } );
})( jQuery );

