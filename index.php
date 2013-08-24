<?php


class MyDB extends SQLite3
{
	function __construct()
	{
		$this->open('SpeedDial2.sqlite');
	}
}

function rgbToHsl( $r, $g, $b ) {
	$oldR = $r;
	$oldG = $g;
	$oldB = $b;

	$r /= 255;
	$g /= 255;
	$b /= 255;

	$max = max( $r, $g, $b );
	$min = min( $r, $g, $b );

	$h;
	$s;
	$l = ( $max + $min ) / 2;
	$d = $max - $min;

	if( $d == 0 ){
		$h = $s = 0; // achromatic
	} else {
		$s = $d / ( 1 - abs( 2 * $l - 1 ) );

		switch( $max ){
			case $r:
				$h = 60 * fmod( ( ( $g - $b ) / $d ), 6 );
				break;

			case $g:
				$h = 60 * ( ( $b - $r ) / $d + 2 );
				break;

			case $b:
				$h = 60 * ( ( $r - $g ) / $d + 4 );
				break;
		}
	}

	return array( $h, $s, $l );
}
function hslToRgb( $h, $s, $l ){
	$r;
	$g;
	$b;

	$c = ( 1 - abs( 2 * $l - 1 ) ) * $s;
	$x = $c * ( 1 - abs( fmod( ( $h / 60 ), 2 ) - 1 ) );
	$m = $l - ( $c / 2 );

	if ( $h < 60 ) {
		$r = $c;
		$g = $x;
		$b = 0;
	} else if ( $h < 120 ) {
		$r = $x;
		$g = $c;
		$b = 0;
	} else if ( $h < 180 ) {
		$r = 0;
		$g = $c;
		$b = $x;
	} else if ( $h < 240 ) {
		$r = 0;
		$g = $x;
		$b = $c;
	} else if ( $h < 300 ) {
		$r = $x;
		$g = 0;
		$b = $c;
	} else {
		$r = $c;
		$g = 0;
		$b = $x;
	}

	$r = ( $r + $m ) * 255;
	$g = ( $g + $m ) * 255;
	$b = ( $b + $m  ) * 255;

	return array( $r, $g, $b );
}



$db = new MyDB();
if(!$db){
	echo $db->lastErrorMsg();
} else {
	//echo "Opened database successfully\n";
}



?><!DOCTYPE html>
<head>
	<style type="text/css">

		body {
			background: #343434;
			color: #fff;
			margin: 0;

		}

		a.thumbnail_link {
			display: block;
			width: 319px;
			height: 179px;
			float: left;
			border: 2px solid #000;
			background-size: contain;

			text-decoration: none;
			color: transparent;
			cursor: pointer;

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

		/*a.thumbnail_link + div.group {
			clear: both;
		}*/

	</style>
</head>
<body>
<?php



$sql = 'SELECT * from bookmarks left join groups on bookmarks.idgroup = groups.id ORDER BY groups.position, idgroup, bookmarks.position;';

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
				 $groupColor[0].$groupColor[0]
				.$groupColor[1].$groupColor[1]
				.$groupColor[2].$groupColor[2];
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


		list($decH, $decS, $decL) = rgbToHsl($decR, $decG, $decB);

		$decL *= .60; // Half velocity (brightness)
		list($decRNew, $decGNew, $decBNew) = hslToRgb($decH, $decS, $decL);

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
					border: 2px solid #'.$groupColor.';
				"
			>
				'.$groupTitle.'
			</div>
		</div>';
	}

	$LinksHTML .= '
	<a
		class="thumbnail_link"
		href="'.$url.'" style="background-image: url('.$thumbnail.');"
		title="'.$title.'"
	>&nbsp;</a>';

}

$db->close();

?>

<div style="margin: 0 16px">
	<?=$LinksHTML ?>
	<div style="clear: both"></div>
</div>
<div style="overflow: hidden; height: 24px">&nbsp;</div>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script type="text/javascript">

	$(document).ready(function() {

		$(window).resize(function () {

			var origWidth = 319
			var origHeight = 179
			var hMargin = 16

			//window.innerWidth = origWidth*5*x + margin*5*x
			//window.innerWidth = x(5*origWidth + 5*margin)

			//$(document.body).width() - 5(20) = 5*origWidth
			//($(document.body).width() - 5(20))/5 = origWidth
			var newWidth = ($(document.body).width() - 5*4 - hMargin*2)/5

			var newHeight = (newWidth / origWidth) * origHeight

			//var x = $(document.body).width() / (5 * (origWidth + 4))
			//var scaleFactor = (1 / (origWidth*5 + margin*5)) * window.innerWidth

			$('a.thumbnail_link').css({
				width: newWidth + 'px',
				height: newHeight + 'px'
			})


		})

		$(window).resize()

	})

</script>
</body>
</html>