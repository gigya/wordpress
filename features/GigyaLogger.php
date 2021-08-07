<?php


namespace Gigya\WordPress;


class GigyaLogger {


	protected $wp_user_username;
	protected $wp_user_id;
	protected $gigya_uid;
	protected $default_user_name;


	public function __construct() {
		$this->default_user_name = 'unknown_user';
		$wp_user                 = wp_get_current_user();
		$this->wp_user_id        = $wp_user->ID;
		$this->wp_user_username  = ( $this->wp_user_id == 0 ) ? $this->default_user_name : $wp_user->display_name;
		$this->gigya_uid         = get_user_meta( get_current_user_id(), 'gigya_uid', true );
		$this->gigya_uid         = ( $this->gigya_uid !== false ) ? $this->gigya_uid : $this->default_user_name;


	}

	/**
	 * @param string $message_type
	 * @param $message
	 * @param string $uid
	 */
	public function log( $message_type, $message, $uid = '' ) {

		if ( ! $this->isTypeValid( $message_type ) ) {
			$error_message = 'Could not write the log info, the "Log Level". doesn\'t include the type (' . $message_type . ') of the message.';
			error_log( $error_message );

			return;

		}
		if ( ! $this->isMessageTypeIncludedInLogLevel( GIGYA__LOG_LEVEL, $message_type ) ) {
			return;
		}

		$this->updateUserData( $uid );

		$file = $this->getGigyaLogFilePointer();
		if ( $file === false ) {
			return;
		}


		if ( ! is_string( $message ) ) {
			$message = json_encode( $message );
		}

		$log_message = '[' . date( 'd-M-Y H:i:s e' ) . '] ' . $this->wp_user_id . ' ' . $this->wp_user_username . ' ' . $this->gigya_uid . ' - ' . strtoupper( $message_type ) . ' - ' . $message . PHP_EOL;
		fwrite( $file, $log_message );
		fclose( $file );

	}

	/**
	 * @param $message
	 * @param string $uid
	 */
	public function error( $message, $uid = '' ) {
		$this->log( 'error', $message, $uid );
	}

	/**
	 * @param $message
	 * @param string $uid
	 */
	public function info( $message, $uid = '' ) {
		$this->log( 'info', $message, $uid );
	}

	/**
	 * @param $message
	 * @param string $uid
	 */
	public function debug( $message, $uid = '' ) {
		$this->log( 'debug', $message, $uid );
	}


	public function getGigyaLogFilePointer() {
		$file = fopen( GIGYA__LOG_FILE, 'a' );
		if ( $file === false ) {
			$error_message = "Could not open the SAP CDC log file at: " . GIGYA__LOG_FILE . ". The parent directory of the file does not exist, or the file is not writable.";
			error_log( $error_message );

			return false;
		};

		return $file;
	}

	private function isMessageTypeIncludedInLogLevel( $curr_log_level, $message_type ) {
		$message_type = strtolower( $message_type );

		switch ( $message_type ) {
			case 'error':
				return true;
			case 'info':
				return ( ( $curr_log_level === 'info' ) or ( $curr_log_level === 'debug' ) );
			case 'debug':
				return ( $curr_log_level === 'debug' );
			default:
				return false;

		}

	}

	private function isTypeValid( $message_type ) {

		$message_type = strtolower( $message_type );

		return ( $message_type === 'debug' or $message_type === 'info' or $message_type === 'error' );

	}

	private function updateUserData( $uid ) {
		if ( empty( $uid ) ) {
			return;
		}
		$wp_users = get_users( array(
			'meta_key'   => 'gigya_uid',
			'meta_value' => $uid
		) );
		if ( ! empty( $wp_users ) ) {
			$wp_user                = $wp_users[0];
			$this->wp_user_username = $wp_user->display_name;
			$this->wp_user_id       = $wp_user->ID;
			$this->gigya_uid        = $uid;

		} else {
			if ( $this->gigya_uid === $this->default_user_name and ! empty( $uid ) ) {
				$this->gigya_uid = $uid;
			}

		}
	}
}


