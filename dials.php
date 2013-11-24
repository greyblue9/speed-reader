<?php

define('DEVELOPMENT_MODE', true);

$cache_filepath = 'cache.dat';
$cache_life = 60 * 5; // time to use cached data (seconds) - set to 5 minutes
$filemtime = @filemtime($cache_filepath);  // returns FALSE if file does not exist
$cache_expired = ($filemtime == false || time() - $filemtime >= $cache_life);

$cache_file_contents = null;
$json_data = null;


if (!$cache_expired && !DEVELOPMENT_MODE) {

	// Cache exists and is not yet expired

	if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $filemtime &&
		strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $filemtime)
	{
		header('HTTP/1.0 304 Not Modified');
		header('Last-Modified: '.gmdate('D, d M Y H:i:s', $filemtime));
		header('Cache-Control: max-age='.$cache_life.', private');
		header('Expires: '.gmdate('D, d M Y H:i:s', time() + $filemtime));
		exit();
	}

	$cache_file_contents = file_get_contents($cache_filepath);
	$mod_time_sep_pos = strpos($cache_file_contents, '||', 0);
	$json_data = substr($cache_file_contents, $mod_time_sep_pos + 2);

} else {

	// Cache does not exist or is out of date
	// Update and rebuild saved cache

	/**
	 * @param $username SpeedDial2 username (plain)
	 * @param $password SpeedDial2 password (plain)
	 * @param $out_cachefilepath PHP-conventional (script-relative or absolute)
	 *          path to use when saving cache file
	 * @return string JSON data string from server
	 */
	function updateCacheDataFromServer($username, $password, $out_cachefilepath)
	{
		$time = time();
		$speedDial2SyncUrl = 'http://speeddial2.com/sync2/get' .
			'?username=' . urlencode($username) .
			'&password=' . base64_encode($password) .
			'&_=' . $time;
		$response = file_get_contents($speedDial2SyncUrl);

		// Save new formatted cache file with updated data from server
		$new_cache_file_contents = $time.'||'.$response;
		file_put_contents($out_cachefilepath, $new_cache_file_contents);
		// Return new cache file contents
		return $response;
	}

	require_once('Auth.class.inc');

	$json_data = updateCacheDataFromServer(
		Auth::getUsername(),
		Auth::getPassword(),
		$cache_filepath
	);

	if (!DEVELOPMENT_MODE) {
		header('Last-Modified: '.gmdate('D, d M Y H:i:s', $time));
		header('Cache-Control: max-age='.$cache_life.', private');
		header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $filemtime));
	}
}



$data = json_decode($json_data, true);
	header('Content-Type: application/json');
print($json_data);

	exit();


$dials = $data['dials'];
$groups = $data['groups'];
$groups[] = array(
	'id' => "0",
	'position' => "0",
	'title' => 'Home',
	'color' => 'ff5537',
	'text-color' => 'ffffff'
);


function sortByPosition($a, $b) {
  return intval($a["position"]) - intval($b["position"]);
}


usort($groups, "sortByPosition");



foreach ($dials as $dial) {
	foreach($groups as $groupIdx => $group) {
		if ($group['id'] == $dial['idgroup']) {
			if (!isset($group['dials'])) {
				$groups[$groupIdx]['dials'] = array();
			}
			$groups[$groupIdx]['dials'][] = $dial;
		}
	}
}







?><!DOCTYPE html>
<head>
	<title>Speed Reader 2</title>

	<link rel="SHORTCUT ICON" href="favicon.ico" />

	<meta name="viewport" content="width=device-width; user-scalable=no"/>
	<link rel="stylesheet" href="dials.css" type="text/css" media="screen" />
</head>
<body>
<?php

$GroupsHTML = '';

require_once('Colors.class.inc');


foreach ($groups as $group) {

	$groupColor = $group['color'];
	$groupTitle = $group['title'];
	// Compute group gradient color
	if (strlen($groupColor) == 3) {
		$groupColor6 =
			$groupColor[0].$groupColor[0].
			$groupColor[1].$groupColor[1].
			$groupColor[2].$groupColor[2];
	} else if (strlen($groupColor) == 6) {
		$groupColor6 = $groupColor;
	}
	$decR = hexdec(substr($groupColor6, 0, 2));
	$decG = hexdec(substr($groupColor6, 2, 2));
	$decB = hexdec(substr($groupColor6, 4, 2));
	list($decH, $decS, $decL) = Colors::rgbToHsl($decR, $decG, $decB);
	$decL *= .60; // luminance adjustment
	list($decRNew, $decGNew, $decBNew) = Colors::hslToRgb($decH, $decS, $decL);
	$hexRNew = dechex($decRNew);
	$hexGNew = dechex($decGNew);
	$hexBNew = dechex($decBNew);
	$groupColorDarker =
		 str_pad($hexRNew, 2, "0", STR_PAD_LEFT).
		 str_pad($hexGNew, 2, "0", STR_PAD_LEFT).
		 str_pad($hexBNew, 2, "0", STR_PAD_LEFT);
	$textColorCss = isset($group['text-color'])? "color: #${group['text-color']}; ": "";

	// Create HTML for group
	$GroupsAndDialsHTML .= '
	<div class="group">
		<div
			style="
				background: -moz-linear-gradient(center top, #'.$groupColor.', #'.$groupColorDarker.');
				background: linear-gradient(center top, #'.$groupColor.', #'.$groupColorDarker.');
				background: -webkit-gradient(linear, center top, center bottom, from(#'.$groupColor.'), to(#'.$groupColorDarker.'));
				border: 2px solid #'.$groupColor.';
				'.$textColorCss.'
			"
		>
			'.$groupTitle.'
		</div>
	</div>';

	usort($group['dials'], "sortByPosition");
	foreach ($group['dials'] as $dial) {

		$url = $dial['url'];
		$title = $dial['title'];
		$thumbnail = $dial['thumbnail'];

		$GroupsAndDialsHTML .= 
		'<a href="'.$url.'" class="thumbnail_link" title="'.$title.'">'.
			'<img src="'.$thumbnail.'" title="'.$title.'" />'.
		'</a>';

	}// foreach dial

}// foreach group



?>

<div class="wrapper">
	<?= $GroupsAndDialsHTML ?>
	<div style="clear: both"></div>
</div>
<div style="overflow: hidden; height: 24px">&nbsp;</div>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="dials.js" type="text/javascript"></script>

</body>
</html>