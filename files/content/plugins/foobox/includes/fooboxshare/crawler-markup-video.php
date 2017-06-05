<?php
/**
 * Video HTML Markup generated for crawlers
 */

/** @var FooBoxShare_Network_Base */
global $fooboxshare_current_share_network;

/** @var FooBoxShare_Data_v1 */
global $fooboxshare_current_share_data;

$site_name = get_bloginfo();

$meta_tags['property="og:type"'] = 'video';
if ( isset( $fooboxshare_current_share_data->thumb_url ) ) {
	$meta_tags['property="og:image"'] = $fooboxshare_current_share_data->thumb_url;
}
//$meta_tags['property="og:video:url"'] = $fooboxshare_current_share_data->content_url;
//$meta_tags['property="og:video:type"'] = 'text/html';
$meta_tags['property="og:url"'] = $fooboxshare_current_share_data->content_url;
$meta_tags['property="og:title"'] = $fooboxshare_current_share_data->title;
$meta_tags['property="og:description"'] = $fooboxshare_current_share_data->description;
$meta_tags['property="og:site_name"'] = $site_name;

$meta_tags = $fooboxshare_current_share_network->add_meta( $meta_tags, $fooboxshare_current_share_data );

$json_args['@context'] = 'http://schema.org';
$json_args['@type'] = 'VideoObject';
$json_args['author']['name']  = fooboxshare_get_setting( 'sharing_author_name', $site_name );
$json_args['author']['@type'] = fooboxshare_get_setting( 'sharing_author_type', 'Organization' );
$json_args['contentUrl'] = $fooboxshare_current_share_data->redirect_url;
if ( isset( $fooboxshare_current_share_data->thumb_url ) ) {
	$json_args['thumbnailUrl'] = $fooboxshare_current_share_data->thumb_url;
}
$json_args['name'] = $fooboxshare_current_share_data->title;
$json_args['description'] = $fooboxshare_current_share_data->description;
?><!DOCTYPE html>
<html>
<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# video: http://ogp.me/ns/video#">
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="0" />
<?php
foreach ( $meta_tags as $key => $value ) {
	echo "<meta {$key} content=\"{$value}\">\n";
}
?>
</head>
<body>
<script type="application/ld+json">
<?php echo json_encode( $json_args ); ?>
</script>
</body>
</html>

