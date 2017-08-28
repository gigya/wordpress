(function ( $ ) {

  $( document ).ready( function () {

// --------------------------------------------------------------------

    /**
     * Show Notification.
     * @param response
     * @returns {boolean}
     */
    var notificationCB = function ( response ) {
      if ( response.errorCode === 0 ) {
        if ( typeof response.UID !== 'undefined' ) {
          var params = gigyaGmNotificationParams;
          gigya.gm.showNotifications( params );
        }
      }

      return false;
    }

// --------------------------------------------------------------------

    /**
     * Set notification if enabled.
     */
    var setNotification = function () {
      if ( typeof gigyaGmNotificationParams !== 'undefined' && gigyaGmNotificationParams.length > 0 ) {
        gigya.services.socialize.getUserInfo( {
          callback: notificationCB
        } )
      }
    }

// --------------------------------------------------------------------

    /**
     * Get Gamification type.
     * @param gigyaGamificationParams
     * @returns {string}
     */
    var getType = function ( params ) {
      var type = 'showUserStatusUI';
      switch ( params.type ) {
        case 'leaderboard' :
          type = 'showLeaderboardUI';
          break;

        case 'achievements' :
          type = 'showAchievementsUI';
          break;

        case 'challenge' :
          type = 'showChallengeStatusUI';
          break;

        case 'game' :
          type = 'showUserStatusUI';
          break;

      }

      return type;
    }

// --------------------------------------------------------------------

    /**
     * Show the Gigya's Gamification block.
     * @param id
     */
    var showGamification = function ( id ) {

      // Get the data.
      var dataEl = $( '#' + id ).next( 'script.data-gamification' );
      var params = JSON.parse( dataEl.text() );

      // Define the Gamification Bar Plugin params object.
      params.containerID = id;
      params.context = { id: id };
      params.onError = GigyaWp.errHandle;

      // Load the gamification block Plugin.
      gigya.gm[getType( params )]( params );

    };

// --------------------------------------------------------------------

    /**
     * Start.
     */
    $( '.gigya-gamification-widget' ).each( function ( index, value ) {
      var id = 'gigya-gamification-widget-' + index;
      $( this ).attr( 'id', id );
      showGamification( id );
      setNotification();
    } );

// --------------------------------------------------------------------

  } );
})( jQuery );