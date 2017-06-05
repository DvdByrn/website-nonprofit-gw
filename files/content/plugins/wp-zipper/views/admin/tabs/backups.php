<h3>
	Backups
</h3>

<?php
$archives = new \S8\Zipper\Archivers\Archiver();
$files = $archives->get_archives();
?>

<p>
    Backups older than 24 hours will be automatically deleted.
</p>
<div class="s8">
	<table class="table table-hover">
		<thead>
		<tr>
			<th>File</th>
			<th>Size</th>
			<th>Delete</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach( $files as $file ) : ?>
		<tr>
			<td>
				<a href="<?php echo $file['url']; ?>">
					<?php echo $file['filename']; ?>
				</a>
			</td>
			<td><?php echo $file['size']; ?></td>
			<td>
				<a href="<?php echo admin_url() . 'tools.php?page=zipper&tab=backups&file=' . urlencode( $file['filename'] ); ?>">
					Delete
				</a>
			</td>
			</tr>
		<?php endforeach; ?>
		<?php if ( 0 == count( $files ) ) : ?>
		<tr>
			<td colspan="4">
				No backups created.
			</td>
		</tr>
		<?php endif; ?>
		</tbody>
	</table>

</div>
