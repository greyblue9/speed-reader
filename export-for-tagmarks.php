<?php

set_time_limit(0);
define('LOGFILE', 'export-for-tagmarks.log');
define('OUTFILE', 'export-for-tagmarks.json');
define('OVERWRITE_LOG', true);

require_once('export-utils.php');


if (!OVERWRITE_LOG && file_exists(LOGFILE)) {
	$extDotPos = strrpos(LOGFILE,'.');
	$logFileTitle = substr(LOGFILE, 0, $extDotPos);
	$logFileExt = substr(LOGFILE, $extDotPos+1);
	rename(LOGFILE, $logFileTitle.'-'.time().'.'.$logFileExt);
}

function logLine($line, $overwrite = false) {
	file_put_contents(LOGFILE, $line."\n", $overwrite? 0: FILE_APPEND);
}

logLine('--- Export started ---', true);

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

logLine('Auth info for SpeedDial2: username=['.Auth::getUsername().'] password='.(Auth::getPassword()?'[*****]':'NONE'));

$speeddial2_json_response = getSpeedDial2DataFromServer(
	Auth::getUsername(),
	Auth::getPassword()
);

logLine('Response from SpeedDial2 server: '.strlen($speeddial2_json_response).' byte(s)');

$speeddial2_data = json_decode($speeddial2_json_response, true);

$dials = $speeddial2_data['dials'];
$groups = $speeddial2_data['groups'];

logLine(count($dials).' dial(s)');
logLine(count($groups).' group(s) not including home');

$tags = array();

// Create missing "Home" group in SD2 group set
$groups[] = array(
	'title' => 'Home',
	'position' => 0,
	'color' => 'ffffff',
	'id' => 0
);

logLine('Added home group to SD2 data set');

foreach ($groups as $group) {

	$tag_id_name = preg_replace("/[^A-Za-z0-9] /", '', $group['title']);
	$tag_id_name = preg_replace("/\s/", '-', $group['title']);
	$tag_id_name = strtolower($tag_id_name);

	$tag = array(
		'name' => $group['title'],
		'description' => '',
		'priority' => $group['position'],
		'background_color' => '#'.strtolower($group['color']),
		'_sd2_group_id' => $group['id'],
		'id_name' => $tag_id_name
	);

	$tags[] = $tag;
}

function sortByPriority($a, $b)
{
	return intval($a['priority']) - intval($b['priority']);
}
usort($tags, 'sortByPriority');

logLine('Created '.count($tags).' tags');


$sites = array();

logLine('Creating sites...');

$numDials = count($dials);
foreach ($dials as $dialIdx => $dial) {

	logLine('Creating site['.$dialIdx.'] ... ('.$dialIdx.'/'.$numDials.')');

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
			array_push($site['tags'], $tag['id_name']);
		}
	}

	logLine('  -> ['.$site['name'].'] with thumbnail ('.$width.' x '.$height.')');

	$sites[] = $site;
}

logLine('Created '.count($sites).' sites');

$tagmarks_data =  array(
	'tags' => $tags,
	'sites' => $sites
);

logLine('Outputting finished result');

$output_json = json_encode($tagmarks_data, JSON_NUMERIC_CHECK);
$output_json_formatted = get_indented_json_string($output_json);

file_put_contents(OUTFILE, $output_json_formatted);

header('Content-Type: application/json');
print($output_json_formatted);
exit();




