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
        else if ( path.indexOf( 'profile.php' ) != -1 && gigyaRaasParams.canEditUsers != 1 ) {

          // Profile page
          gigya.accounts.showScreenSet( {screenSet: gigyaRaasParams.raasProfileWebScreen, mobileScreenSet: gigyaRaasParams.raasProfileMobileScreen, onAfterSubmit: raasUpdatedProfile} );
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
      // Gigya temp user.
      if ( typeof response.UID === 'undefined' || response.UID.indexOf( '_temp_' ) === 0 ) {
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
          GigyaWp.redirect();
        }
        else {
          if ( typeof res.data != 'undefined' ) {
            // The user didn't logged in.
            $( '#dialog-modal' ).html( res.data.msg );
            $( '#dialog-modal' ).dialog( { modal: true } );
          }
          gigya.accounts.logout();
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

      // Get admin=true cookie.
      var admin = false;
      var name = "gigya_admin=true";
      var ca = document.cookie.split( ';' );
      for ( var i = 0; i < ca.length; i++ ) {
        var c = ca[i].trim();
        if ( c.indexOf( name ) == 0 && location.pathname.indexOf( 'wp-login.php' ) != -1 ) {
          admin = true
        }
      }

        // Embed Screens.
        if ( location.search.indexOf( 'admin=true' ) == -1 && admin == false) {
          gigya.accounts.showScreenSet( {screenSet: gigyaRaasParams.raasWebScreen, mobileScreenSet: gigyaRaasParams.raasMobileScreen, startScreen: gigyaRaasParams.raasLoginScreen, containerID: gigyaRaasParams.raasLoginDiv} );
          gigya.accounts.showScreenSet( {screenSet: gigyaRaasParams.raasWebScreen, mobileScreenSet: gigyaRaasParams.raasMobileScreen, startScreen: gigyaRaasParams.raasRegisterScreen, containerID: gigyaRaasParams.raasRegisterDiv} );

          if ( gigyaRaasParams.canEditUsers != 1 ) {
            gigya.accounts.showScreenSet( {screenSet: gigyaRaasParams.raasProfileWebScreen, mobileScreenSet: gigyaRaasParams.raasProfileMobileScreen, containerID: gigyaRaasParams.raasProfileDiv, onAfterSubmit: raasUpdatedProfile} );
          }
        }
        else {
          // Set admin=true cookie
          var d = new Date();
          d.setTime( d.getTime() + (60 * 60 * 1000) );
          var expires = "; expires=" + d.toGMTString();
          document.cookie = "gigya_admin=true" + expires;
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
      };

      var raasUpdatedProfile = function (res) {
          var esData = GigyaWp.getEssentialParams(res);
          var options = {
              url     : gigyaParams.ajaxurl,
              type    : 'POST',
              dataType: 'json',
              data    : {
                  data  : esData,
                  action: 'raas_update_profile'
              }
          };
          var req = $.ajax( options);
      };

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

    }
    )
    ;
  } )( jQuery );

