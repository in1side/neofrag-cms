<div class="avatar">
<?php if ($data['user_id']): ?>
	<a href="<?php echo url('members/'.$data['user_id'].'/'.url_title($data['username']).'.html'); ?>">
		<img class="img-responsive" src="<?php echo $data['avatar']; ?>" alt="" />
	</a>
<?php else: ?>
	<img class="img-responsive" src="<?php echo $data['avatar']; ?>" alt="" />
<?php endif; ?>
</div>
