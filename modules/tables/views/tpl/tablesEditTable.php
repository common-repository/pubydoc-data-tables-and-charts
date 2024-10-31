<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="pubydoc-bar pubydoc-titlebar">
	<ul class="pubydoc-bar-controls">
		<li class="pubydoc-title-icon">
			<i class="fa fa-table pubydoc-title-icon"></i>
		</li>
		<li class="pubydoc-title-text">
			<span class="pyt-title-label"><?php esc_html_e('Table:', 'publish-your-table'); ?></span>
		</li>
		<li class="pubydoc-title-text col-9 p-0">
			<span id="pytTitleShell" class="pyt-title-shell" title="<?php esc_attr_e('Click to edit', 'publish-your-table'); ?>">
				<span class="pyt-title-text"><?php echo esc_html($this->table_title); ?></span>
				<?php
					HtmlPyt::text('title', array(
						'value' => $this->table_title,
						'attrs' => 'class="pytHidden"',
						'required' => true,
					));
					?>
				<i class="fa fa-fw fa-pencil"></i>
			</span>
		</li>
	</ul>
	<div class="pubydoc-clear"></div>
</section>
<section>
	<div class="pubydoc-item pubydoc-panel">
		<div id="pytEditTableForm" data-id="<?php echo esc_attr($this->table_id); ?>" data-type="<?php echo esc_attr($this->type); ?>">
			<div class="pubydoc-main-container">
				<div class="row">
					<div class="col-auto mb-2 pyt-shortcode-select">
						<select id="pytShortcodes">
							<?php 
								$info = '';
								foreach ($this->shortcodes as $key => $data) { 
									$info .= '<b>' . $data['label'] . ':</b> ' . $data['info'] . '<br /><br />';
									?>
									<option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($data['label']); ?></option>
							<?php } ?>
						</select>
						<i class="fa fa-question pubydoc-tooltip" title="<?php echo esc_attr($info); ?>"></i>
					</div>
					<?php foreach ($this->shortcodes as $key => $data) { ?>
						<div class="col-auto mb-2 pyt-shortcode-shell<?php echo ( 'shortcode' == $key ? '' : ' pytHidden' ); ?>" data-key="<?php echo esc_attr($key); ?>">
							<?php
								$value = '[' . $data['name'] . ' id=' . $this->table_id . ( empty($data['attrs']) ? '' : ' ' . $data['attrs'] ) . ']';
								HtmlPyt::text('', array(
									'value' => ( 'php_code' == $key ? "<?php echo do_shortcode('" . $value . "') ?>" : $value ),
									'attrs' => 'readonly class="pubydoc-shortcode pubydoc-width-full"',
									));
								?>
						</div>
					<?php } ?>
											
					<div class="pyt-main-buttons col">
						<ul class="control-buttons">
							<li>
								<button id="pytBtnDelete" class="button button-minor" data-confirm="<?php esc_html_e('Are you sure you want to delete table?', 'publish-your-table'); ?>">
									<i class="fa fa-trash-o" aria-hidden="true"></i><span><?php esc_html_e('Delete', 'publish-your-table'); ?></span>
								</button>
							</li>
							<?php if (empty($this->type)) { ?>
								<li>
									<button id="pytBtnClear" class="button button-minor" data-confirm="<?php esc_html_e('Are you sure you want to clear table?', 'publish-your-table'); ?>">
										<i class="fa fa-eraser" aria-hidden="true"></i><span><?php esc_html_e('Clear', 'publish-your-table'); ?></span>
									</button>
								</li>
								<li>
									<button id="pytBtnImport" class="button button-minor <?php echo ( $this->is_pro ? '' : ' pro-notify pubydoc-show-pro' ); ?>" data-dialog="#import_exportProFeatureDialog">
										<i class="fa fa-fw fa-download"></i><span><?php echo esc_html__('Import', 'publish-your-table'); ?></span>
									</button>
								</li>
								<li>
									<button id="pytBtnExport" class="button button-minor <?php echo ( $this->is_pro ? '' : ' pro-notify pubydoc-show-pro' ); ?>" data-dialog="#import_exportProFeatureDialog">
										<i class="fa fa-fw fa-upload"></i><span><?php echo esc_html__('Export', 'publish-your-table'); ?></span>
									</button>
								</li>
							<?php } ?>
							<li>
								<button id="pytBtnClone" class="button button-minor" data-confirm="<?php esc_html_e('Are you sure you want to clone table?', 'publish-your-table'); ?>">
									<i class="fa fa-clone" aria-hidden="true"></i><span><?php echo esc_html__('Clone', 'publish-your-table'); ?></span>
								</button>
							</li>
							<li>
								<button id="pytBtnSave" class="button button-minor">
									<i class="fa fa-floppy-o" aria-hidden="true"></i><span><?php esc_html_e('Save', 'publish-your-table'); ?></span>
								</button>
							</li>
						</ul>
						<div class="clear"></div>
					</div>
				</div>
				<div class="row">
					<div class="<?php echo $this->full_builder ? 'col-lg-7' : 'col-12'; ?>">
						<ul class="pubydoc-grbtn pyt-main-tabs">
							<?php foreach ($this->main_tabs as $key => $data) { ?>
								<li>
									<a href="#block-tab-<?php echo esc_attr($key); ?>" data-model="<?php echo esc_attr($key); ?>" class="button <?php echo empty($data['class']) ? '' : esc_attr($data['class']); ?>">
										<i class="fa fa-fw <?php echo esc_attr($data['icon']); ?>"></i><?php echo esc_html($data['label']); ?>
									</a>
								</li>
							<?php } ?>
						</ul>
					</div>
					<?php if (isset($this->main_tabs['builder']) && $this->full_builder) { ?>
						<div class="col-lg-5 align-self-center">
							<div class="pyt-builder-switcher<?php echo strpos($this->main_tabs['builder']['class'], 'current') === false ? ' pytHidden' : ''; ?>">
								<span class="pyt-small-label"><?php esc_html_e('Switch View', 'publish-your-table'); ?>:</span>
								<a href="#pyt-builder-simple" data-builder="0"><?php esc_html_e('Simple', 'publish-your-table'); ?></a> | 
								<a href="#pyt-builder-extended" data-builder="1"><?php esc_html_e('Extended', 'publish-your-table'); ?></a>
							</div>
						</div>
					<?php } ?>
				</div>
				<div class="pyt-main-tab-content">
					<?php 
						foreach ($this->main_tabs as $key => $data) { 
							include_once 'tablesEditTab' . strFirstUpPyt($key) . '.php';
						}		
					?>
				</div>
				<?php 
					HtmlPyt::hidden('', array('value' => json_encode($this->getTabsJSLangs($this->main_tabs), JSON_UNESCAPED_UNICODE), 'attrs' => 'id="pytLangStrings"'));
					HtmlPyt::hidden('', array(
						'value' => json_encode(DispatcherPyt::applyFilters('addTablesEditSettings', array())),
						'attrs' => 'id="pytSettingsPro"')
					);
					
				?>
			</div>	
		</div>
	</div>
</section>
