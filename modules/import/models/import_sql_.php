<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Import_sql_ModelPyt extends ModelPyt {
	public function import( $file ) {
		if (!is_readable($file)) {
			FramePyt::_()->pushError(esc_html__('File is not readable.', 'publish-your-table'));
			return false;
		}

		if (false === $data = file_get_contents($file)) {
			FramePyt::_()->pushError(esc_html__('Failed to read file.', 'publish-your-table'));
			return false;
		}
		$delim = '----------------------------------';

		if (!strpos($data, $delim)) {
			FramePyt::_()->pushError(esc_html__('Invalid file format.', 'publish-your-table'));
			return false;
		}

		$queries = explode($delim, $data);
		$queries = array_map('trim', $queries);
		$queries = array_filter($queries);
		$first = true;

		foreach ($queries as $q) {
			if (!DbPyt::query($q)) {
				FramePyt::_()->pushError(DbPyt::getError());
				return false;
			}
		}
		return true;
	}
}
