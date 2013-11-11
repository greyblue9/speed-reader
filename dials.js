
$(document).ready(function () {

	$(window).resize()

});


$(window).resize(function () {

	var $body = $('body');

	var outerMarginTotal = $body.outerWidth(true) - $body.width();
	var outerMarginEachSide = Math.floor(outerMarginTotal / 2);

	// All available area, including that available for scrollbars
	$body.css('overflow', 'hidden');
	var viewportAll = {
		width: $body.outerWidth(true), // incl. padding+border
		height: $body.height() // incl. padding+border
	}

	// Available area, inside scrollbars (when both present)
	$body.css('overflow', 'scroll');
	var viewportInside = {
		width: $body.outerWidth(true), // incl. padding+border
		height: $body.height() // incl. padding+border
	}

	// Assume thumbnail source image sizes are 319x179px
	var thumbSrcSize = {
		width: 319,
		height: 179
	}

	var thumbHorizSep = outerMarginEachSide;
	var $renderedThumb = $('a.thumbnail_link:first');
	var thumbBorderWidthTotal =
		$renderedThumb.outerWidth(false) // element width with padding, border (but not margin)
			- $renderedThumb.innerWidth(); // element width with padding (but not border or margin)

	var thumbWidthsByRowCount = {};

	for (var thumbsPerRow = 2; thumbsPerRow < 10; thumbsPerRow++) {

		/*

			Calculation:
			------------

			viewportInside.width =
			thumbNewWidth * thumbsPerRow +
			thumbBorderWidthTotal * thumbsPerRow +
			thumbHorizSep * (thumbsPerRow - 1) +
			outerMarginTotal

			(viewportInside.width - (thumbBorderWidthTotal * thumbsPerRow) - (thumbHorizSep * (thumbsPerRow - 1)) - (outerMarginTotal)) / thumbsPerRow =
			thumbNewWidth

		 */

		var thumbNewWidth = Math.floor(
			(viewportInside.width
				- (thumbBorderWidthTotal * thumbsPerRow)
				- (thumbHorizSep * (thumbsPerRow - 1))
				- (outerMarginTotal)
				) / thumbsPerRow
		);

		thumbWidthsByRowCount[thumbsPerRow] = thumbNewWidth;
	}

	var selThumbsPerRow = 5; // Fixed at 5 rows for now

	var thumbWidth = thumbWidthsByRowCount[selThumbsPerRow];
	if (thumbWidth >= thumbSrcSize.width) thumbWidth = thumbSrcSize.width;

	var thumbHeight = (thumbSrcSize.height / thumbSrcSize.width) * thumbWidth;

	var $thumbnailLinks = $('a.thumbnail_link');

	$thumbnailLinks.css({
		width: thumbWidth + 'px',
		height: thumbHeight + 'px',
		margin: '0'
	})


	var idxGroupOffset = 0;
	$thumbnailLinks.each(function (idx) {
		var $a = $(this);

		if ($a.prev('.group').length) {
			idxGroupOffset = idx
		}

		idx -= idxGroupOffset;

		if (idx % selThumbsPerRow != 0) {
			$a.css('margin-left', thumbHorizSep + 'px');
		}
	})

	$thumbnailLinks.children('img').each(function () {

		var $img = $(this);
		var $a = $img.parent(); // a.thumbnail_link


		$img.on('load', function () {
			$a.css('transform', 'scale(1,1)');
			$a.css('opacity', '1');
		});

		if (this.complete) $img.trigger('load');

	})


});





