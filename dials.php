<?php

require_once('Colors.class.inc');
require_once('Auth.class.inc');


$file = ''; // file data

$cachedFile = file_exists('cache.dat')?
	file_get_contents('cache.dat'): '';

if ($cachedFile && !isset($_GET['nocache'])) {
	$parts = explode("||", $cachedFile);
	$cacheTime = $parts[0];
	$cachePayload = $parts[1];
	
	if (time() - $cacheTime <= 60 * 5) {
		$file = $cachePayload;
	}
}

if (!$file) {
	$speedDial2SyncUrl = 'http://speeddial2.com/sync2/get'
		.'?username='.Auth::getUsername().
		'&password='.base64_encode(Auth::getPassword()).
		'&_='.time();


	$file = file_get_contents($speedDial2SyncUrl);
	
	file_put_contents('cache.dat', time().'||'.$file);
}





$data = json_decode($file, true);

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