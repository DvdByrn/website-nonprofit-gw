<?php
/** @var $this S8\Zipper\Admin\MenuPage */
?>

<style>
	.error {
		color: red;
	}

	.success {
		color: green;
	}
</style>

<div class="wrap">
	<h2>
		Zipper
	</h2>

	<?php echo $this->render_tab_html(); ?>

	<?php foreach( $this->get_tabs() as $key => $label ) : ?>

	<?php endforeach; ?>

	<?php echo $this->render_tab_content_html(); ?>

    <hr>

    <p>

        Developed by <a href="http://sideways8.com/" target="_blank">Sideways8</a>.
    </p>
</div>