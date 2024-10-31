<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="pytAdminFooterShell pytHidden">
	<div class="pytAdminFooterCell">
		<?php echo esc_html(PYT_WP_PLUGIN_NAME); ?>
		<?php esc_html_e('Version', 'publish-your-table'); ?>:
		<a target="_blank" href="http://wordpress.org/plugins/publish-your-table/changelog/"><?php echo esc_html(PYT_VERSION); ?></a>
	</div>
	<div class="pytAdminFooterCell">|</div>
	<?php if (!FramePyt::_()->getModule(implode('', array('l', 'ic', 'e', 'ns', 'e')))) { ?>
	<div class="pytAdminFooterCell">
		<?php esc_html_e('Go', 'publish-your-table'); ?>&nbsp;<a target="_blank" href="<?php echo esc_url($this->getModule()->getMainLink()); ?>"><?php esc_html_e('PRO', 'publish-your-table'); ?></a>
	</div>
	<div class="pytAdminFooterCell">|</div>
	<?php } ?>
	<div class="pytAdminFooterCell">
		<a target="_blank" href="https://wordpress.org/support/plugin/publish-your-table"><?php esc_html_e('Support', 'publish-your-table'); ?></a>
	</div>
	<div class="pytAdminFooterCell">|</div>
	<div class="pytAdminFooterCell">
		Add your <a target="_blank" href="http://wordpress.org/support/view/plugin-reviews/publish-your-table?filter=5#postform">&#9733;&#9733;&#9733;&#9733;&#9733;</a> on wordpress.org.
	</div>
</div>
