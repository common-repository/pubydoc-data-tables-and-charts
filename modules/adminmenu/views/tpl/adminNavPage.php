<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<style type="text/css">
.pubydoc-main {
	display: none;
}
.pubydoc-plugin-loader {
	width: 100%;
	height: 100px;
	text-align: center;
}
.pubydoc-plugin-loader div {
	font-size: 30px;
	position: relation;
	margin-top: 40px;
}

</style>
<div class="pubydoc-wrap">
	<div class="pubydoc-plugin pubydoc-main">
		<section class="pubydoc-content">
			<nav class="pubydoc-navigation pubydoc-sticky <?php DispatcherPyt::doAction('adminMainNavClassAdd'); ?>">
				<ul>
					<?php foreach ($this->tabs as $tabKey => $t) { ?>
						<?php 
						if (isset($t['hidden']) && $t['hidden']) {
							continue;
						}
						?>
						<li class="pubydoc-tab-<?php echo esc_attr($tabKey); ?> <?php echo ( ( $this->activeTab == $tabKey || in_array($tabKey, $this->activeParentTabs) ) ? 'active' : '' ); ?>">
							<a href="<?php echo esc_url($t['url']); ?>" title="<?php echo esc_attr($t['label']); ?>">
								<?php if (isset($t['fa_icon'])) { ?>
									<i class="fa <?php echo esc_attr($t['fa_icon']); ?>"></i>
								<?php } elseif (isset($t['wp_icon'])) { ?>
									<i class="dashicons-before <?php echo esc_attr($t['wp_icon']); ?>"></i>
								<?php } elseif (isset($t['icon'])) { ?>
									<i class="<?php echo esc_attr($t['icon']); ?>"></i>
								<?php } ?>
								<span class="sup-tab-label"><?php echo esc_html($t['label']); ?></span>
							</a>
						</li>
					<?php } ?>
				</ul>
			</nav>
			<div class="pubydoc-container pubydoc-<?php echo esc_attr($this->activeTab); ?>">
				<?php HtmlPyt::echoEscapedHtml($this->content); ?>
				<div class="clear"></div>
			</div>
		</section>
		<div id="pytAddDialog" class="pubydoc-plugin pubydoc-hidden" title="<?php echo esc_attr__('Enter product filter name', 'publish-your-table'); ?>">
			<div>
				<form id="tableForm">
					<input id="addDialog_title" class="pubydoc-text pubydoc-width-full" type="text"/>
					<input type="hidden" id="addDialog_duplicateid" class="pubydoc-text pubydoc-width-full"/>
				</form>
				<div id="formError" class="pubydoc-hidden">
					<p></p>
				</div>
			</div>
		</div>
	</div>
	<div class="pubydoc-plugin-loader">
		<div>Loading...<i class="fa fa-spinner fa-spin"></i></div>
	</div>
</div>

