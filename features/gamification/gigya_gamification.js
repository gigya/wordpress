(function ( $ ) {

  $( document ).ready( function () {

// --------------------------------------------------------------------

    /**
     * Get Gamification type.
     * @param gigyaGamificationParams
     * @returns {string}
     */
    var getType = function ( gigyaGamificationParams ) {
      var type = '';
      switch ( gigyaGamificationParams.type ) {
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
      var gigyaGamificationParams = JSON.parse( dataEl.text() );

      // Define the Gamification Bar Plugin params object.
      var params = $.extend( true, {}, gigyaGamificationParams );
      params.containerID = id;
      params.onError = GigyaWp.errHandle;

      // Load the gamification block Plugin.
      gigya.gm[getType( gigyaGamificationParams )]( params );

    };

// --------------------------------------------------------------------

    $( '.gigya-gamification-widget' ).each( function ( index, value ) {
      var id = 'gigya-gamification-widget-' + index;
      $( this ).attr( 'id', id );
      showGamification( id );
    } );

// --------------------------------------------------------------------

  } );
})( jQuery );