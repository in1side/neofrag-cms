<div class="addons-panel-body" data-type="widget">
	<?php foreach ($this->addons->get_widgets(TRUE) as $widget): ?>
	<?php
		$deactivatable = $widget->is_deactivatable();
		$removable     = $widget->is_removable();
		
		if (!$deactivatable & !$removable)
		{
			continue;
		}
	?>
	<div class="addon-item<?php echo $this->addons->is_enabled($widget->name, 'widget') ? ' active' : ''; ?>" data-name="<?php echo $widget->name; ?>">
		<div class="item-status">
			<div class="item-status-icon">
				<?php echo $this->addons->is_enabled($widget->name, 'widget') ? '<span data-toggle="tooltip" title="Activé">'.icon('fa-check').'</span>' : '<span data-toggle="tooltip" title="Désactivé">'.icon('fa-ban').'</span>'; ?>
			</div>
			<div class="item-status-switch">
				<?php if ($deactivatable) echo $this->addons->is_enabled($widget->name, 'widget') ? '<a href="#" data-toggle="tooltip" title="Désactiver le widget">'.icon('fa-toggle-on text-green').'</a>' : '<a href="#" data-toggle="tooltip" title="Activer le widget">'.icon('fa-toggle-off text-muted').'</a>'; ?>
			</div>
			<div class="item-name">
				<?php echo icon($widget->icon).' <b>'.$widget->get_title().'</b><br />'; ?>
				<small><?php echo $widget->description; ?></small>
			</div>
		</div>
		<div class="item-action">
			<?php if (0 && $removable) echo button_delete('admin/addons/delete/widget/'.$widget->name.'.html'); ?>
		</div>
	</div>
	<?php endforeach; ?>
</div>