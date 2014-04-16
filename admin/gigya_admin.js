(function ( $ ) {
  $( document ).ready( function () {

// --------------------------------------------------------------------

    /**
     * Expose the relevant form element for the login mode selected.
     * @param $el
     */
    var userManagementPage = function ( $el ) {
      if ( $el.is( ':checked' ) ) {
        if ( $el.val() == 'wp_only' ) {
          $( '.social-login-wrapper' ).addClass( 'hidden' );
          $( '.raas-login-wrapper' ).addClass( 'hidden' );
        }
        else if ( $el.val() == 'wp_sl' ) {
          $( '.social-login-wrapper' ).removeClass( 'hidden' );
          $( '.raas-login-wrapper' ).addClass( 'hidden' );
        }
        else if ( $el.val() == 'raas' ) {
          $( '.social-login-wrapper' ).addClass( 'hidden' );
          $( '.raas-login-wrapper' ).removeClass( 'hidden' );
        }
      }
    }

    // Set user management page at page load.
    $( '#gigya_mode input' ).each( function () {
      userManagementPage( $( this ) );
    } );

    // Set user management page at modes manually change.
    $( 'input:radio[name="gigya_login_settings[mode]"]' ).change( function () {
      userManagementPage( $( this ) );
    } );

// --------------------------------------------------------------------

    /**
     * JSONLint.
     */
    var jsonValidate = function ( textField, e ) {
      var json = textField.val();
      if ( json.length > 0 ) {
        $( '.msg' ).remove();
        try {
          var result = jsonlint.parse( json );
          if ( result ) {
            textField.after( '<div class="msg updated">JSON is valid</div>' );
            textField.addClass( 'valid' );
          }
        } catch ( err ) {
          textField.after( '<div class="msg error">' + err + '</div>' );
          textField.addClass( 'error' );
          e.preventDefault();
          e.stopPropagation();
        }
      }

    }

    // Validate JSON before submit on settings form.
    $( 'form.gigya-settings' ).on( 'submit', function ( e ) {
      $( 'form.gigya-settings textarea' ).each( function () {
        jsonValidate( $( this ), e );
      } )
    } );

    // Validate JSON before submit on widget form.
    var submitEl = $( '.textarea.json' ).parents( 'form' ).find( 'input[type="submit"]' )
    submitEl.on( 'click', function ( e ) {
      $( '.textarea.json textarea' ).each( function () {
        jsonValidate( $( this ), e );
      } )
    } );

// --------------------------------------------------------------------

    /**
     * Conditional settings field.
     * @param check
     * @param textfield
     */
    var conditionalSettingsField = function ( check, textfield ) {
      check.is( ':checked' ) ? textfield.show() : textfield.hide();
      check.change( function () {
        check.is( ':checked' ) ? textfield.show() : textfield.hide();
      } );
    }

// --------------------------------------------------------------------

    // Conditional settings share/reactions image url field.
    var check1 = $( 'input:checkbox[name="gigya_share_settings[image]"], input:checkbox[name="gigya_reactions_settings[image]"]' );
    var fields1 = $( 'input#gigya_imageURL' ).parent( '.text-field ' );
    conditionalSettingsField( check1, fields1 );

// --------------------------------------------------------------------

    // Conditional.
    var el = $( '.gigya-widget-override' );
    el.find('input').is( ':checked' ) ? el.nextAll().show() : el.nextAll().hide();
    el.find('input').change( function () {
      el.find('input').is( ':checked' ) ? el.nextAll().show() : el.nextAll().hide();
    } );


// --------------------------------------------------------------------

  } );
})( jQuery );