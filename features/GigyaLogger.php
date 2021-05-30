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


	private function log( $message_type, $message ) {

		if ( ! $this->isTypeValid( $message_type ) ) {
			$error_message = 'Could not write the log info, the "Log Level". doesn\'t include the type (' . $message_type . ') of the message.';
			error_log( $error_message );

			return;

		}

		if ( ! is_dir( GIGYA__LOG_FILE_DIR ) ) {
			$error_message = "Could not open the SAP CDC log file at: " . GIGYA__LOG_FILE . " The parent directory of the file does not exist, or the file is not writable.";
			error_log( $error_message );

			return;
		};


		$file        = fopen( GIGYA__LOG_FILE , 'a' );
		$log_message = '[' . date( 'd-M-Y H:i:s e' ) . '] ' . $this->wp_user_id . ' ' . $this->wp_user_username . '-' . strtoupper( $message_type ) . '-' . json_encode($message) . PHP_EOL;
		if ( fwrite( $file, $log_message ) === false ) {
			$error_message = 'Could not write into the file at the path: ' . GIGYA__LOG_FILE;
			error_log( $error_message );

			return;

		}
		if ( fclose( $file ) === false ) {
			$error_message = 'Could not close the file at the path: ' . GIGYA__LOG_FILE;
			error_log( $error_message );

			return;
		}
	}

	public function error( $message ) {
		$this->log( 'error', $message );
	}

	public function info( $message ) {
		if ( GIGYA__LOG_LEVEL === 'info' or GIGYA__LOG_LEVEL === 'debug' ) {
			$this->log( 'info', $message );
		}
	}

	public function debug( $message ) {
		if ( GIGYA__LOG_LEVEL === 'debug' ) {
			$this->log( 'debug', $message );
		}

	}

	private function isTypeValid( $message_type ) {

		$message_type = strtolower( $message_type );

		switch ( $message_type ) {
			case 'error':
				return true;
			case 'info':
				return ( ( GIGYA__LOG_LEVEL == 'info' ) or ( GIGYA__LOG_LEVEL === 'debug' ) );
			case 'debug':
				return ( GIGYA__LOG_LEVEL === 'debug' );
			default:
				return false;

		}
	}
}
