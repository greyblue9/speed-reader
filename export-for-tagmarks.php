<?php


/**
 * @param $username SpeedDial2 username (plain)
 * @param $password SpeedDial2 password (plain)
 * @return string JSON data string from server
 */
function getSpeedDial2DataFromServer($username, $password)
{
	$time = time();
	$speedDial2SyncUrl = 'http://speeddial2.com/sync2/get' .
		'?username=' . urlencode($username) .
		'&password=' . base64_encode($password) .
		'&_=' . $time;
	$response = file_get_contents($speedDial2SyncUrl);

	return $response;
}

require_once('Auth.class.inc');

$speeddial2_json_response = getSpeedDial2DataFromServer(
	Auth::getUsername(),
	Auth::getPassword()
);



$speeddial2_data = json_decode($speeddial2_json_response, true);

$dials = $speeddial2_data['dials'];
$groups = $speeddial2_data['groups'];


$tags = array();

// Create missing "Home" group in SD2 group set
$groups[] = array(
	'title' => 'Home',
	'position' => 0,
	'background_color' => '#ffffff',
	'id' => 0
);

foreach ($groups as $group) {

	$alnum_tagname = preg_replace("/[^A-Za-z0-9] /", '', $group['title']);
	$alnum_tagname = preg_replace("/\s/", '-', $group['title']);
	$alnum_tagname = strtolower($alnum_tagname);

	$tag = array(
		'name' => $group['title'],
		'description' => '',
		'priority' => $group['position'],
		'background_color' => '#'.strtolower($group['color']),
		'_sd2_group_id' => $group['id'],
		'_name_alnum' => $alnum_tagname
	);

	$tags[] = $tag;
}

function sortByPriority($a, $b)
{
	return intval($a['priority']) - intval($b['priority']);
}
usort($tags, 'sortByPriority');


$sites = array();

foreach ($dials as $dial) {

	$thumbnail_url = $dial['thumbnail'];
	$size = getimagesize($thumbnail_url);
	$width = $size[0];
	$height = $size[1];
	$mime_type = image_type_to_mime_type($size[2]);

	$site = array(
		'name' => $dial['title'],
		'url' => $dial['url'],
		'thumbnail' => $thumbnail_url,
		'width' => $width,
		'height' => $height,
		'mime_type' => $mime_type,
		'tags' => array()
	);


	foreach ($tags as $tag) {
		if ($tag['_sd2_group_id'] == $dial['idgroup']) {
			array_push($site['tags'], $tag['_name_alnum']);
		}
	}

	$sites[] = $site;
}



$tagmarks_data =  array(
	'tags' => $tags,
	'sites' => $sites
);

header('Content-Type: application/json');
print(json_encode($tagmarks_data, JSON_NUMERIC_CHECK));
exit;




