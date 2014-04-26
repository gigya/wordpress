(function ( $ ) {
  $( document ).ready( function () {

// --------------------------------------------------------------------

    /**
     * Expose the relevant form element for the login mode selected.
     * @param $el
     */
    var userManagementPage = function ( $el ) {

      if ( $el.attr( "checked" ) == 'checked' ) {
        if ( $el.val() == 'wp_only' ) {
          $( '.social-login-wrapper' ).addClass( 'hidden' );
          $( '.raas-login-wrapper' ).addClass( 'hidden' );
          $( '.raas_enabled' ).find( '.gigya-raas-warn' ).addClass( 'hidden' );
        }
        else if ( $el.val() == 'wp_sl' ) {
          $( '.social-login-wrapper' ).removeClass( 'hidden' );
          $( '.raas-login-wrapper' ).addClass( 'hidden' );
          $( '.raas_enabled' ).find( '.gigya-raas-warn' ).removeClass( 'hidden' );
        }
        else if ( $el.val() == 'raas' ) {
          $( '.social-login-wrapper' ).addClass( 'hidden' );
          $( '.raas-login-wrapper' ).removeClass( 'hidden' );
          $( '.raas_enabled' ).find( '.gigya-raas-warn' ).addClass( 'hidden' );
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

    // Conditional settings share/reactions image url field.
    var el1 = $( '.conditional' );
    el1.find( 'input' ).attr( "checked" ) == 'checked' ? el1.next().show() : el1.next().hide();
    el1.find( 'input' ).change( function () {
      $( this ).attr( "checked" ) == 'checked' ? el1.next().show() : el1.next().hide();
    } );

// --------------------------------------------------------------------

    // Conditional widget overrides fields.
    var el2 = $( '.gigya-widget-override' );
    el2.find( 'input' ).attr( "checked" ) == 'checked' ? el2.nextAll().show() : el2.nextAll().hide();
    el2.find( 'input' ).change( function () {
      $( this ).attr( "checked" ) == 'checked' ? el2.nextAll().show() : el2.nextAll().hide();
    } );

// --------------------------------------------------------------------

    /**
     * Run the clean DB after upgrade script.
     */
    var cleanDB = function () {
      var options = {
        url : gigyaParams.ajaxurl,
        type: 'POST',
        data: {
          data  : '',
          action: 'clean_db'
        }
      };

      var req = $.ajax( options );

      req.done( function ( res ) {
        if ( res.success == true ) {
          alert( res.data.msg );
          location.reload();
        }
      } );

      req.fail( function ( jqXHR, textStatus, errorThrown ) {
        console.log( errorThrown );
      } );
    }

    $( document ).on( 'click', '.gigya-settings .clean-db', function () {
      var r = confirm( "You're about to run a database cleaner.\n\rOld data from Gigya plugin version 4.0 will be deleted permanently from the database.\n\rIt's highly recommended to backup your database before you run this script.\n\rPlease confirm you want to continue." );
      if ( r == true ) {
        cleanDB();
      }
    } );

// --------------------------------------------------------------------

    /**
     * Run the clean DB after upgrade script.
     */
    var debugLog = function () {
      var options = {
        url : gigyaParams.ajaxurl,
        type: 'POST',
        data: {
          data  : '',
          action: 'debug_log'
        }
      };

      var req = $.ajax( options );

      req.done( function ( res ) {
        if ( res.success == true ) {
          var pom = document.createElement( 'a' );
          pom.setAttribute( 'href', 'data:application/json;charset=utf-8,' + encodeURIComponent( JSON.stringify( res.data, null, 4 ) ) );
          pom.setAttribute( 'download', 'gigya-log.json' );
          pom.click();
        }
      } );

      req.fail( function ( jqXHR, textStatus, errorThrown ) {
        console.log( errorThrown );
      } );
    }

    $( document ).on( 'click', '.gigya-debug-log', function () {
      debugLog();
    } );

// --------------------------------------------------------------------

    // Disable the RaaS option when not available.
    $( '.raas_disabled' ).find( 'input[value="raas"]' ).attr( 'disabled', 'disabled' ).parent( 'label' ).css( 'color', '#ccc' );

// --------------------------------------------------------------------

    // JSON example for Additional Parameters (advanced) sections.
    $( document ).on( 'click', '.gigya-json-example', function ( e ) {
      e.preventDefault();
      var w = window.open( "about:blank", "jsonExample", "width=440,height=330" );

      $.getJSON( gigyaParams.jsonExampleURL )
          .done( function ( data ) {
            w.document.write( '<p>JSON Example:</p><textarea  rows="8" cols="45">' + JSON.stringify( data, null, 4 ) + '</textarea><br><small>Tips for valid JSON:<ol><li>{curly brackets} are for objects (key:value).</li><li>[square brackets] are for arrays (value).</li><li>Both keys and values must have double quote ("").</li><li>No trailing commas.</li></ol></small>' );
          } )

    } );

// --------------------------------------------------------------------

  } );
})( jQuery );