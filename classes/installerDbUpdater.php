<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class InstallerDbUpdaterPyt {
	public static function runUpdate() {
		if ( DbPyt::get( "SELECT 1 FROM `@__modules` WHERE code='diagrams'", 'one' ) != 1 ) {
			DbPyt::query( "INSERT INTO `@__modules` (id, code, active, type_id, label) VALUES (NULL, 'optionspro', 1, 1, 'Options Pro');" );
			DbPyt::query( "INSERT INTO `@__modules` (id, code, active, type_id, label) VALUES (NULL, 'tablespro', 1, 1, 'Tables Pro');" );
			DbPyt::query( "INSERT INTO `@__modules` (id, code, active, type_id, label) VALUES (NULL, 'import', 1, 1, 'Import');" );
			DbPyt::query( "INSERT INTO `@__modules` (id, code, active, type_id, label) VALUES (NULL, 'export', 1, 1, 'Export');" );
			DbPyt::query( "INSERT INTO `@__modules` (id, code, active, type_id, label) VALUES (NULL, 'diagrams', 1, 1, 'Diagrams');" );
		}
	}
}
