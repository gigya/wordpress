(function ( $ ) {

  $( document ).ready( function () {

// --------------------------------------------------------------------

    /**
     * Show Gigya's login block on login/register forms.
     */
    var showLoginUI = function () {
      // Add an HTML element to attach the Gigya Login UI to.
      $( '#registerform, #loginform' ).append( '<div id="gigya-login"></div>' );

      // Add the Gigya's social login block to login/register pages.
      var params = {};
      params.containerID = "gigya-login";

      if ( typeof gigyaLoginParams.ui != 'undefined' ) {
        var paramsUI = {};
        $.each( gigyaLoginParams.ui, function ( index, value ) {
          paramsUI[index] = value;
        } );

        $.extend( params, paramsUI );
      }

      // Attach the Gigya block.
      gigya.socialize.showLoginUI( params );
    }

// --------------------------------------------------------------------

    /**
     * Show Gigya's add connections block on profile page.
     */
    var showAddConnectionsUI = function () {
      // Add 'Add Connections UI' block to the profile page.
      $( 'form#your-profile' ).before( '<div id="gigya-add-connections"></div>' );

      // Setting Parameters.
      var addConnectionsParams = {};
      addConnectionsParams.containerID = "gigya-add-connections";

      if ( typeof gigyaLoginParams.addConnection != 'undefined' ) {
        var addConnectionsParamsUI = {};
        $.each( gigyaLoginParams.addConnection, function ( index, value ) {
          addConnectionsParamsUI[index] = value;
        } );

        $.extend( addConnectionsParams, addConnectionsParamsUI );
      }

      // Attach the Gigya block.
      gigya.socialize.showAddConnectionsUI( addConnectionsParams );
    }

// --------------------------------------------------------------------

    /**
     * On Social login with Gigya behavior.
     * Send Gigya's response object to server.
     * @param data
     */
    var socialLogin = function ( data ) {

      var options = {
        url : gigyaParams.ajaxurl,
        type: 'POST',
        data: {
          data  : data,
          action: gigyaLoginParams.actionLogin
        }
      };

      var req = $.ajax( options );

      req.done( function ( res ) {
        if ( res.success == true ) {
          if ( typeof res.data != 'undefined' ) {

            // The user didn't register, and need more field to fill.
            $( '#dialog-modal' ).html( res.data.html ).dialog( { modal: true } );

          }
          else {

            // Redirect.
            location.replace( gigyaLoginParams.redirect );
          }
        }
        else {
          if ( typeof res.data != 'undefined' ) {

            // Message modal.
            $( '#dialog-modal' ).html( res.data.msg ).dialog( { modal: true } );
          }
        }
      } );

      req.fail( function ( jqXHR, textStatus, errorThrown ) {
        console.log( errorThrown );
      } );
    }

// --------------------------------------------------------------------

    /**
     * Login validator.
     * @param response
     * @returns {boolean}
     */
    var loginValidate = function ( response ) {
      if ( response.provider === 'site' ) {
        return false;
      }

      // We check there an email field.
      // Only for the first time.
      if ( ( response.user.email.length === 0 ) && ( response.user.isSiteUID !== true ) ) {

        // Building an 'get email' form.
        var html =
            '<div class="form-get-email">' +
                '<div class="description">' +
                'Additional information is required in order ' +
                'to complete your registration. ' +
                'Please fill-in your Email' +
                '<br><br>' +
                '</div>' +
                '<label for="email">Email</label>' +
                '<input type="text" id="get-email" name="email">' +
                '<button type="button" class="button button-get-email">Submit</button>' +
                '</div>';

        // Modal with the email form.
        $( '#dialog-modal' ).html( html ).dialog( { modal: true } );

        $( document ).on( 'click', '.button-get-email', function () {
          $( '#dialog-modal' ).dialog( "close" );
        } );

        $( "#dialog-modal" ).on( "dialogclose", function ( event, ui ) {
          var email = $( 'input#get-email' ).val();
          if ( email.length > 0 ) {

            // When we get a value, we update the user object,
            // And put a flag for 'email not verified'.
            response.user.email = email;
            response.user.email_not_verified = true;
            $( '#dialog-modal' ).dialog( "close" );

            // Go on with register
            socialLogin( response );
          }
          else {
            gigya.socialize.logout();
          }

        } );
      }

      else {
        socialLogin( response );
      }

    }

// --------------------------------------------------------------------

    var loginInit = function () {
      showLoginUI();
      showAddConnectionsUI();

      // Attach event handlers.
      if ( typeof GigyaWp.regEvents === 'undefined' ) {

        // Social Login.
        gigya.socialize.addEventHandlers( {
          onLogin: loginValidate
        } );

        GigyaWp.regEvents = true;

      }
    }

// --------------------------------------------------------------------

    loginInit();

// --------------------------------------------------------------------

  } );
})( jQuery );

