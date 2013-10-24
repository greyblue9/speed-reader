<?php



require_once('Colors.class.inc');



$time = time();
$file = file_get_contents("http://speeddial2.com/sync2/get?username=greyblue9&password=RmF1eHBhc3M4OA%3D%3D&_=$time");
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
	<meta name="viewport" content="width=device-width; user-scalable=no" />
		<title>Speed Reader 2</title>
		<LINK REL="SHORTCUT ICON" HREF="http://d6r.org/speed-reader/favicon.ico"> 
	<style type="text/css">

		body {
			background: -moz-linear-gradient(center top , #555566 0%, #000000 100%) repeat fixed 0 0 rgba(0, 0, 0, 0);
			color: #FFFFFF;
			margin: 8px;
		}
		
		a.thumbnail_link {
			border: 2px solid #000;
			display: inline-block;
			margin: 7px 7px 0 0;
			position: relative;
			text-decoration: none;
			-moz-transition: border-color .75s ease;
		}
		
		a.thumbnail_link > img {
			border: 0 none;
			display: block;
			height: 100%;
			left: 0;
			position: absolute;
			top: 0;
			width: 100%;
		}
		
		a.thumbnail_link:hover {
			border: 2px solid #ff5353;
			-moz-transition: border-color .01s ease;
			box-shadow: 0 0 15px #ff2323;
		}
		
		div.group {
			clear: both;
			padding: 16px 0;
			text-align: center;
		}
		
		div.group > div {
			background: none repeat scroll 0 0 #000000;
			border: 2px solid #AAAAAA;
			font-family: sans-serif;
			font-size: 20px;
			font-weight: bold;
			padding: 7px 14px;
		}
		
		@media (max-width: 500px) {
		
			div.group {
				padding: 8px 0;
			}
			div.group > div {
				font-family: sans-serif;
				font-size: 14px;
				font-weight: bold;
				padding: 3px 7px;
			}
		}
		
		.wrapper {
			margin: 0 auto;
		}
		
	</style>
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
<script type="text/javascript">
	
	$(window).resize(function () {
		
		var viewport = {
			width  : window.innerWidth,
			height : window.innerHeight
		};

		var origWidth = 319
		var origHeight = 179
		var hMargin = 8;
		var thumbsPerRow = Math.ceil(viewport.width * 5 / 1000)

		var newWidth = (viewport.width - thumbsPerRow*7*2 - thumbsPerRow*2 - 8*2) / 5
		var newHeight = (newWidth / origWidth) * origHeight
		
		if (newWidth > origWidth || newHeight > origHeight) {
			newWidth = origWidth;
			newHeight = origHeight;
		}
		
		

		$('a.thumbnail_link').css({
			width: newWidth + 'px',
			height: newHeight + 'px'
		})


	})

	$(document).ready(function() {

		$(window).resize()

	})

</script>
</body>
</html>