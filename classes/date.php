<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class DatePyt {
	public static function _( $time = null ) {
		if (is_null($time)) {
			$time = time();
		}
		return gmdate(PYT_DATE_FORMAT_HIS, $time);
	}
}
