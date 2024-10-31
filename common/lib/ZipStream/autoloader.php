<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
spl_autoload_register(function ($class_name) {
	$preg_match = preg_match('/^ZipStream\\\/', $class_name);

	if (1 === $preg_match) {
		$class_name = preg_replace('/\\\/', '/', $class_name);
		$class_name = preg_replace('/^ZipStream\\//', '', $class_name);
		require_once(__DIR__ . '/src/' . $class_name . '.php');
	}
});
