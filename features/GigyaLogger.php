<?php


namespace Gigya\WordPress;


class GigyaLogger {


	protected $wp_user_username;
	protected $wp_user_id;


	public function __construct() {

		$wp_user                = wp_get_current_user();
		$this->wp_user_id       = $wp_user->ID;
		$this->wp_user_username = ( $this->wp_user_id == 0 ) ? 'unknown user' : $wp_user->nickname;

	}


	public function log( $message_type, $message, $wp_user = array() ) {

		if ( ! $this->isTypeValid( $message_type ) ) {
			$error_message = 'Could not write the log info, the "Log Level". doesn\'t include the type (' . $message_type . ') of the message.';
			error_log( $error_message );

			return;

		}
		if ( ! $this->isMessageTypeIncludedInLogLevel( GIGYA__LOG_LEVEL, $message_type ) ) {
			return;
		}

		$this->getUserData( $wp_user );

		$file = $this->getGigyaLogFilePointer();
		if ( $file === false ) {
			return;
		}


		if ( ! is_string( $message ) ) {
			$message = json_encode( $message );
		}

		$log_message = '[' . date( 'd-M-Y H:i:s e' ) . '] ' . $this->wp_user_id . ' ' . $this->wp_user_username . ' - ' . strtoupper( $message_type ) . ' - ' . $message . PHP_EOL;
		fwrite( $file, $log_message );
		fclose( $file );

		return;
	}


	public function error( $message, $wp_user = array() ) {
		$this->log( 'error', $message, $wp_user );
	}

	public function info( $message, $wp_user = array() ) {
		$this->log( 'info', $message, $wp_user );
	}

	public function debug( $message, $wp_user = array() ) {
		$this->log( 'debug', $message, $wp_user );
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

	private function getUserData( $wp_user ) {
		if ( empty( $wp_user ) or ! array_key_exists( 'id', $wp_user ) ) {
			return;
		}

		$this->wp_user_id = $wp_user['id'];

		if ( ! array_key_exists( 'nickname', $wp_user ) ) {
			$this->wp_user_username = get_user_by( 'id', $wp_user['id'] );
			if ( $this->wp_user_username === false ) {
				$this->wp_user_username = 'unknown user';
			} else {
				$this->wp_user_username = $this->wp_user_username->nickname;
			}
		} else {
			$this->wp_user_username = $wp_user['nickname'];
		};
	}
}


