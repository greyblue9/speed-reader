<?php


$dir = realpath(dirname(__FILE__));

if (!file_exists("$dir/SpeedDial2.sqlite")) {

	if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
		$output = shell_exec("cmd /c \"$dir\\copy.bat\"");

		if (!file_exists("$dir/SpeedDial2.sqlite")) {
			die("Unable to load SpeedDial2.sqlite using copy.bat.");
		}
	} else {
		die("SpeedDial2.sqlite not present.");


	}

}


require_once('Colors.class.inc');


class SpeedDial2Db extends SQLite3
{
	function __construct()
	{
		$this->open('SpeedDial2.sqlite');
	}
}


$db = new SpeedDial2Db();

if (!$db) {
	echo $db->lastErrorMsg();
	exit;
} else {
	//echo "Opened database successfully\n";
}



?><!DOCTYPE html>
<head>
	<meta name="viewport" content="width=device-width; user-scalable=no" />
	<title>Speed Reader</title>
	<style type="text/css">

		body {
			background: #124a81;
			color: #fff;
			margin: 8px;
		}
		
		a.thumbnail_link {
			display: inline-block;
			
			margin: 10px 10px;
			
			border: 2px solid #000;

			text-decoration: none;
			position: relative;

		}
		
		a.thumbnail_link > img {
		display: block;
			border: 0;
			width: 100%;
			height: 100%;
			position: absolute;
			left: 0;
			top: 0;
			

		}

		a.thumbnail_link:hover {
			border: 2px solid #fff;
		}
		
		div.group {
			clear: both;
			padding: 24px 0;
			text-align: center;
		}

		div.group > div {
			padding: 7px 14px;
			background: #000;
			border: 2px solid #aaa;
			font-family: sans-serif; font-weight: bold; font-size: 20px;
		}

		@media (max-width:500px) {
			div.group {
				padding: 8px 0;
			}
			
			div.group > div {
				padding: 3px 7px;
				font-family: sans-serif; font-weight: bold; font-size: 14px;
			}
		}
	</style>
</head>
<body>
<?php



$sql = "SELECT *
		from bookmarks
		left join groups on bookmarks.idgroup = groups.id
		order by groups.position, idgroup, bookmarks.position;";

$LinksHTML = '';

$ret = $db->query($sql);

$lastIdGroup = -1;
while($row = $ret->fetchArray(SQLITE3_ASSOC) ){
	$title = $row['title'];
	$url = $row['url'];
	$thumbnail = $row['thumbnail'];

	if ($lastIdGroup != $row['idgroup'])
	{
		$lastIdGroup = $row['idgroup'];

		$gSql = "Select * from groups where groups.id == ".$row['idgroup'];
		$gRes = $db->query($gSql);

		$gRow = $gRes->fetchArray(SQLITE3_ASSOC);
		if ($gRow) {
			$groupTitle = $gRow['title'];
			$groupColor = $gRow['color'];
		} else {
			$groupTitle = 'Home';
			$groupColor = 'aaa';
		}

		// $groupColor is 3 or 6 HEX digits, no leading #
		// Ensure 6 digits
		if (strlen($groupColor) == 3) {
			$groupColor6 =
				$groupColor[0].$groupColor[0].
				$groupColor[1].$groupColor[1].
				$groupColor[2].$groupColor[2];
			if (strlen($groupColor6) != 6) {
				print("Fatal error! Cannot convert '$groupColor' to 6-digit color ($groupColor6)");
				exit;
			}
		} else if (strlen($groupColor) == 6) {
			$groupColor6 = $groupColor;
		}

		$decR = hexdec(substr($groupColor6, 0, 2));
		$decG = hexdec(substr($groupColor6, 2, 2));
		$decB = hexdec(substr($groupColor6, 4, 2));


		list($decH, $decS, $decL) = Colors::rgbToHsl($decR, $decG, $decB);

		$decL *= .60; // Half velocity (brightness)
		list($decRNew, $decGNew, $decBNew) = Colors::hslToRgb($decH, $decS, $decL);

		$hexRNew = dechex($decRNew);
		$hexGNew = dechex($decGNew);
		$hexBNew = dechex($decBNew);


		$groupColorDarker =
			 str_pad($hexRNew, 2, "0", STR_PAD_LEFT).
			 str_pad($hexGNew, 2, "0", STR_PAD_LEFT).
			 str_pad($hexBNew, 2, "0", STR_PAD_LEFT);


		$LinksHTML .= '
		<div class="group">
			<div
				style="
					background: -moz-linear-gradient(center top, #'.$groupColor.', #'.$groupColorDarker.');
					background: linear-gradient(center top, #'.$groupColor.', #'.$groupColorDarker.');
					background: -webkit-gradient(linear, center top, center bottom, from(#'.$groupColor.'), to(#'.$groupColorDarker.'));
					border: 2px solid #'.$groupColor.';
				"
			>
				'.$groupTitle.'
			</div>
		</div>';
	}

	
	$LinksHTML .= 
	'<a 
		href="'.$url.'"
		class="thumbnail_link" title="'.$title.'"
	><img
		src="'.$thumbnail.'"
		title="'.$title.'" /></a>';
}

$db->close();

?>

<div style="margin: 0 auto;">
	<?=$LinksHTML ?>
	<div style="clear: both"></div>
</div>
<div style="overflow: hidden; height: 24px">&nbsp;</div>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script type="text/javascript">
	
	$(window).resize(function () {
		
		var viewport = {
			width  : $(window).width(),
			height : $(window).height()
		};

		var origWidth = 319
		var origHeight = 179
		var hMargin = 8;
		var thumbsPerRow = Math.ceil(viewport.width / (origWidth/2))
		if (thumbsPerRow > 5) thumbsPerRow = 5

		var newWidth = (viewport.width - thumbsPerRow*4 - hMargin*2) / thumbsPerRow
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