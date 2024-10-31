<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="pubydoc-bar pubydoc-titlebar">
	<ul class="pubydoc-bar-controls">
		<li class="pubydoc-title-icon">
			<i class="fa fa-list"></i>
		</li>
		<li class="pubydoc-title-text">
			Tables List
		</li>
	</ul>
	<div class="pubydoc-clear"></div>
</section>
<section>
	<div class="pubydoc-item pubydoc-panel">
		<div class="pubydoc-main-container">
			<div class="pubydoc-table-list">
				<table id="pytTablesList" data-settings="<?php echo esc_attr(htmlspecialchars(json_encode($this->settings), ENT_QUOTES, 'UTF-8')); ?>">
					<thead>
						<tr>
							<th><input type="checkbox" class="pytCheckAll"></th>
							<th><?php esc_html_e('ID', 'publish-your-table'); ?></th>
							<th><?php esc_html_e('Title', 'publish-your-table'); ?></th>
							<th><?php esc_html_e('Type', 'publish-your-table'); ?></th>
							<th><?php esc_html_e('Shortcode', 'publish-your-table'); ?></th>
							<th><?php esc_html_e('Functions', 'publish-your-table'); ?></th>
						</tr>
					</thead>
				</table>
			</div>
			<div class="pubydoc-clear"></div>
			<?php 
				if ($this->is_pro) {
					DispatcherPyt::doAction('tablesIncludeTpl', 'tablesFormMigration', array());
				}
				?>
		</div>
		<div class="pubydoc-clear"></div>
	</div>
</section>
