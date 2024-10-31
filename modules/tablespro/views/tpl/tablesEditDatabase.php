<?php 
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
	//$database = empty($this->source) ? array() : UtilsPyt::getArrayValue($this->source, 'source', array(), 2);//empty($this->source) || !is_array($this->source) ? array() : $this->source;
	$database = empty($this->source) ? array() : $this->source;
	$module = FramePyt::_()->getModule('tablespro');
	$params = $module->getDatabaseParams($database);
	$externalValue = $module->dbExternalValue;
	$sqlValue = $module->dbSQLValue;
	$classHidden = $params['dbName'] == $externalValue ? '' : ' pytHidden';
?>
<div id="pyt-database-settings">
	<div class="pubydoc-result-settings" id="pyt-db-params">
		<div class="settings-wrapper">
			<div class="settings-label">
				<?php esc_html_e('Database', 'publish-your-table'); ?>
				<i class="fa fa-question pubydoc-tooltip" title="<?php esc_attr_e('Retrieve data for table from the database.', 'publish-your-table'); ?>"></i>
			</div>
			<div class="settings-option">
			<?php 
				HtmlPyt::selectbox('source[db_name]', array(
					'options' => $params['databases'],
					'value' => $params['dbName'],
					'attrs' => 'id="pyt-database-name" data-external="' . $externalValue . '"',
					'key' => 'value'
					));
				?>
			</div>
		</div>
		<div class="settings-wrapper<?php esc_attr($classHidden); ?>" data-parent="source[db_name]" data-parent-value="<?php esc_attr($externalValue); ?>">
			<div class="settings-label">
				<?php esc_html_e('DB host', 'publish-your-table'); ?>
				<i class="fa fa-question pubydoc-tooltip" title="<?php esc_attr_e('Enter the host of the external database.', 'publish-your-table'); ?>"></i>
			</div>
			<div class="settings-option">
			<?php 
				HtmlPyt::text('source[db_host_e]', array(
					'value' => UtilsPyt::getArrayValue($database, 'db_host_e', ''),
					)); 
				?>
			</div>
		</div>
		<div class="settings-wrapper<?php esc_attr($classHidden); ?>" data-parent="source[db_name]" data-parent-value="<?php esc_attr($externalValue); ?>">
			<div class="settings-label">
				<?php esc_html_e('DB name', 'publish-your-table'); ?>
				<i class="fa fa-question pubydoc-tooltip" title="<?php esc_attr_e('Enter the name of the external database.', 'publish-your-table'); ?>"></i>
			</div>
			<div class="settings-option">
			<?php 
				HtmlPyt::text('source[db_name_e]', array(
					'value' => UtilsPyt::getArrayValue($database, 'db_name_e', ''),
					)); 
				?>
			</div>
		</div>
		<div class="settings-wrapper<?php esc_attr($classHidden); ?>" data-parent="source[db_name]" data-parent-value="<?php esc_attr($externalValue); ?>">
			<div class="settings-label">
				<?php esc_html_e('DB login', 'publish-your-table'); ?>
				<i class="fa fa-question pubydoc-tooltip" title="<?php esc_attr_e('Enter the login/username for the external database.', 'publish-your-table'); ?>"></i>
			</div>
			<div class="settings-option">
			<?php 
				HtmlPyt::text('source[db_login_e]', array(
					'value' => UtilsPyt::getArrayValue($database, 'db_login_e', ''),
					)); 
				?>
			</div>
		</div>
		<div class="settings-wrapper<?php esc_attr($classHidden); ?>" data-parent="source[db_name]" data-parent-value="<?php esc_attr($externalValue); ?>">
			<div class="settings-label">
				<?php esc_html_e('DB password', 'publish-your-table'); ?>
				<i class="fa fa-question pubydoc-tooltip" title="<?php esc_attr_e('Enter the password of the external database.', 'publish-your-table'); ?>"></i>
			</div>
			<div class="settings-option">
			<?php 
				HtmlPyt::text('source[db_password_e]', array(
					'value' => UtilsPyt::getArrayValue($database, 'db_password_e', ''),
					)); 
				?>
			</div>
		</div>
	</div>
	<div class="pubydoc-result-settings">
		<div class="settings-wrapper">
			<div class="settings-label">
				<?php esc_html_e('Table', 'publish-your-table'); ?>
				<i class="fa fa-question pubydoc-tooltip" title="<?php esc_attr_e('Select the table as a data source. Click the button next to refresh the list of tables.', 'publish-your-table'); ?>"></i>
			</div>
			<div class="settings-option" id="pyt-database-tables">
			<?php 
				HtmlPyt::selectbox('source[tbl_name]', array(
					'options' => $params['tables'],
					'value' => $params['dbTable'],
					'attrs' => 'id="pyt-database-table" data-default="' . $sqlValue . '" data-sql="' . $sqlValue . '"',
					'key' => 'value'
					));
				?>
			</div>
		</div>
		<div class="settings-wrapper pubydoc-width-auto<?php echo $params['dbTable'] == $sqlValue ? ' pytHidden' : ''; ?>" data-parent="source[tbl_name]" data-parent-notvalue="<?php esc_attr($sqlValue); ?>">
			<div class="settings-label">
				<?php esc_html_e('Fields', 'publish-your-table'); ?>
				<i class="fa fa-question pubydoc-tooltip" title="<?php esc_attr_e('Only the selected fields will be used to build the table.', 'publish-your-table'); ?>"></i>
			</div>
			<div class="settings-option" id="pyt-database-fields">
			<?php 
				HtmlPyt::selectlist('source[tbl_fields]', array(
					'options' => $params['fields'],
					'value' => UtilsPyt::getArrayValue($database, 'tbl_fields'),
					'key' => 'value',
					'attrs' => 'data-placeholder="' . __('Select fields', 'publish-your-table') . '" id="pytDatabaseFields"'
					));
			
				?>
			</div>
		</div>
		<div class="settings-wrapper pubydoc-width-auto<?php echo $params['dbTable'] == $sqlValue ? ' pytHidden' : ''; ?>" data-parent="source[tbl_name]" data-parent-notvalue="<?php esc_attr($sqlValue); ?>">
			<div class="settings-label">
				<?php esc_html_e('Unique Fields', 'publish-your-table'); ?>
				<i class="fa fa-question pubydoc-tooltip" title="<?php esc_attr_e('Needed to save editable fields.', 'publish-your-table'); ?>"></i>
			</div>
			<div class="settings-option" id="pyt-database-uniq">
			<?php 
				HtmlPyt::selectlist('source[tbl_uniq]', array(
					'options' => $params['fields'],
					'value' => UtilsPyt::getArrayValue($database, 'tbl_uniq'),
					'key' => 'value',
					'attrs' => 'data-placeholder="' . __('Select fields', 'publish-your-table') . '"'
					));
			
				?>
			</div>
		</div>
	</div>
	<div class="pubydoc-result-settings <?php echo $params['dbTable'] == $sqlValue ? '' : ' pytHidden'; ?>" data-parent="source[tbl_name]" data-parent-value="<?php esc_attr($sqlValue); ?>">
		<div class="settings-wrapper pubydoc-width-full">
			<div class="settings-label">
				<?php esc_html_e('SQL query', 'publish-your-table'); ?>
				<i class="fa fa-question pubydoc-tooltip" title="<?php esc_attr_e('Enter the SQL Query. You can use variables whose values are specified in the shortcode. Variables must begin with \'sql\', be enclosed in {} and can contain only latin characters, digits and underscore character, for example, {sql1} or {sql_id}.', 'publish-your-table'); ?>"></i>
			</div>
			<div class="settings-option">
			<?php 
				HtmlPyt::textarea('source[sql]', array(
					'value' => UtilsPyt::getArrayValue($database, 'sql'),
					'placeholder' => __('Paste SQL query here', 'publish-your-table'),

					));
					?>
			</div>
		</div>
	</div>
	<div class="pubydoc-result-settings">
		<div class="settings-wrapper pubydoc-width-full">
			<div class="settings-option">
				<button class="button button-dark pubydoc-width-full" id="pytLoadInBuilder"><?php esc_attr_e('GO', 'publish-your-table'); ?></button>
			</div>
		</div>
	</div>
</div>
