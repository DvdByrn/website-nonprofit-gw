<?php
/**
 * Demo on Foobox Settings Page
 *
 * @author    Brad Vincent
 * @package    foobox/includes
 * @version     1.0
 */

$size     = 60;
$location = 'https://s3.amazonaws.com/foocdn/';
$demo_images = array(
		array(
			'src'  => '1.jpg',
			'title' => __( 'Rusty Padlock', 'foobox' ),
			'desc'  => __( 'You can have a nice image description', 'foobox' ),
			'alt'   => __( 'Rusty Padlock Alt', 'foobox' )
		),
		array(
			'src'  => '2.jpg',
			'title' => __( 'Beach Sandcastle', 'foobox' ),
			'desc'  => __( 'HTML is also <a href=\'#\'>allowed</a> in your <em>descriptions</em>!', 'foobox' )
		),
		array(
			'src'  => '3.jpg',
			'title' => __( 'Pebble Beach', 'foobox' ),
			'desc'  => __( 'A beautiful pebbled beach from some hidden location in the world', 'foobox' )
		),
		array(
			'src'  => '4.jpg',
			'title' => __( 'A Caption With No Description', 'foobox' )
		),
		array(
			'src'  => '5.jpg',
			'desc'  => __( 'A caption with no title, and only a long description describing the image', 'foobox' )
		),
		array(
			'src'  => '6.jpg'
		),
		array(
			'src'  => '7.jpg',
			'title' => __( 'caption-prettification-test-0203', 'foobox' ),
			'desc'  => __( 'Caption prettification will convert auto generated, ugly captions, usually created by cameras or bulk resizing programs, to more readable captions without numbers appended on the end. To test this feature, enable the setting under the Captions tab and open this image again. ', 'foobox' )
		),
		array(
			'src'  => '8.jpg',
			'title' => __( 'Colourful Street', 'foobox' ),
			'desc'  => __( 'A colourful street-scene from a village in a far distant land. Notice the flags above and the cobblestone street below.', 'foobox' )
		),
		array(
			'src'  => '9.jpg',
			'title' => __( 'Haunted Ruin', 'foobox' )
		),
		array(
			'src'  => '10.jpg',
			'title' => __( 'Beautiful Sunset and Ship', 'foobox' )
		),
);
?>
<a href="https://pixabay.com"/>Images found on pixabay.com</a>
<div style="clear:both"></div>
<div class="demo-gallery">
	<?php foreach ($demo_images as $demo_image) {
		$a_href = ' href="' . $location . $demo_image['src'] . '"';
		$a_title = isset( $demo_image['desc'] ) ? ' title="' . $demo_image['desc'] . '"' : '';
		$img_src = ' src="' . $location . 'thumbs/' . $demo_image['src'] . '"';
		$img_title = isset( $demo_image['title'] ) ? ' title="' . $demo_image['title'] . '"' : '';
		$img_alt = isset( $demo_image['alt'] ) ? ' alt="' . $demo_image['alt'] . '"' : '';
		$img_width = ' width="' . $size . '"';
		$img_height = ' height="' . $size . '"';
		?>
	<a<?php echo $a_href . $a_title; ?>>
		<img <?php echo $img_src . $img_title . $img_alt . $img_width . $img_height; ?>/>
	</a>
	<?php } ?>
</div>
<div style="clear:both"></div>
<div class="demo-gallery demo-gallery-advanced">
	<a title="Test a 404 error image" href="http://fooplugins.com/foobox_demo_unknown_pic.jpg"><img src="<?php echo FOOBOX_PLUGIN_URL; ?>img/404.png"/></a>
	<a data-caption-title="Video captions are awesome!" data-caption-desc="Set a video caption title by adding a <code>data-caption-title</code> attribute to your link.<br />You can also set a caption description by adding a <code>data-caption-desc</code> attribute to your link." title="YouTube Video" href="https://youtu.be/ofmzX1nI7SE"><img src="<?php echo FOOBOX_PLUGIN_URL; ?>img/youtube.png"/></a>
	<a data-caption-title="Captions for Vimeo vids are cool!" data-caption-desc="Did you know that you can also include <strong>HTML</strong> in your captions!" title="Vimeo Video" href="https://vimeo.com/143456347"><img src="<?php echo FOOBOX_PLUGIN_URL; ?>img/vimeo.png"/></a>
	<a data-caption-title="I am an iFrame caption" data-caption-desc="This is an iFrame caption description." title="iFrame" href="http://fooplugins.com" target="foobox"><img src="<?php echo FOOBOX_PLUGIN_URL; ?>img/iframe.png"/></a>
	<a data-caption-title="I am an HTML caption" data-caption-desc="This is an HTML caption description." title="Inline Element" href="#foobox-inline" data-width="600px" data-height="420px" target="foobox"><img src="<?php echo FOOBOX_PLUGIN_URL; ?>img/inline.png"/></a>
</div>

<div id="foobox-inline" style="display: none;">
	<img style="float: left" src="<?php echo FOOBOX_PLUGIN_URL; ?>/img/foobot.png"/>

	<div class="demo_inline">
		<h1>Join Our Newsletter!</h1>
		<input type="text" placeholder="Name"/><br/>
		<input type="text" placeholder="Email address"/><br/>
		<a class="demo_subscribe" href="#" onclick="alert('This is only a demo to show off an inline HTML FooBox');">Subscribe!</a>

		<p><strong>Some reasons why you should stay in the loop:</strong></p>
		<ol>
			<li>Stay informed about new releases</li>
			<li>Make sure your site is bug free and secure</li>
			<li>New themes and features released all the time</li>
			<li>Get special offers on other plugins</li>
		</ol>
	</div>
</div>

<div style="clear:both"></div>