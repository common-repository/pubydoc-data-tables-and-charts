<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="pubydoc-bar pubydoc-titlebar">
	<ul class="pubydoc-bar-controls">
		<li class="pubydoc-title-icon">
			<i class="fa fa-fw fa-bar-chart"></i>
		</li>
		<li class="pubydoc-title-text">
			Diagrams List
		</li>
	</ul>
	<div class="pubydoc-clear"></div>
</section>
<section>
	<div class="pubydoc-item pubydoc-panel">
		<div class="pubydoc-main-container">
			<div class="pubydoc-table-list pubydoc-diagram-list">
				<table id="pytDiagramsList" data-settings="<?php echo esc_attr(htmlspecialchars(json_encode($this->settings), ENT_QUOTES, 'UTF-8')); ?>">
					<thead>
						<tr>
							<th><input type="checkbox" class="pytCheckAll"></th>
							<th><?php esc_html_e('ID', 'publish-your-table'); ?></th>
							<th><?php esc_html_e('Title', 'publish-your-table'); ?></th>
							<th><?php esc_html_e('Type', 'publish-your-table'); ?></th>
							<th><?php esc_html_e('Table', 'publish-your-table'); ?></th>
							<th><?php esc_html_e('Range', 'publish-your-table'); ?></th>
							<th><?php esc_html_e('Shortcode', 'publish-your-table'); ?></th>
							<th><?php esc_html_e('Preview', 'publish-your-table'); ?></th>
						</tr>
					</thead>
				</table>
			</div>
			<div class="pubydoc-clear"></div>
			<?php DispatcherPyt::doAction('tablesIncludeTpl', 'tablesFormMigration', array()); ?>
		</div>
		<div class="pubydoc-clear"></div>
	</div>
	<div class="pubydoc-hidden">
		<?php include 'diagramsEditDiagram.php'; ?>
	</div>
</section>
