<?php

namespace Gigya\WordPress;

class GigyaOfflineSync {
	private $settings;

	public function __construct() {
		$this->settings = get_option( GIGYA__SETTINGS_FIELD_MAPPING );
	}

	/**
	 * @param string $job_type
	 * @param string $job_status
	 * @param string $to
	 * @param int|null $processed_items
	 * @param int|null $failed_items
	 * @param string $custom_email_body
	 */
	public function sendCronEmail( $job_type, $job_status, $to, $processed_items = null, $failed_items = null, $custom_email_body = '' ) {
		$email_body = $custom_email_body;
		if ( $job_status == 'succeeded' or $job_status == 'completed with errors' ) {
			$email_body = 'Job ' . $job_status . ' on ' . gmdate( "F n, Y H:i:s" ) . ' (UTC).';
			if ( $processed_items !== null ) {
				$email_body .= PHP_EOL . $processed_items . ' ' . ( ( $processed_items > 1 ) ? 'items' : 'item' ) . ' successfully processed, ' . $failed_items . ' failed.';
			}
		} elseif ( $job_status == 'failed' ) {
			$email_body = 'Job failed. No items were processed. If the WordPress debug log is enabled, please consult it for more information. For information on enabling the log, please consult the WordPress documentation.';
		}

		wp_mail( $to, 'SAP CDC cron job of type ' . $job_type . ' ' . $job_status . ' on website ' . get_bloginfo(), $email_body );
	}
}
