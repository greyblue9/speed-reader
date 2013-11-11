

var SpeedReader = {

	Viewport: {
		EntireWebArea: {width: null, height: null},
		MinWebArea: {width: null, height: null},
		WebArea: {width: null, height: null},

		OuterMarginWidth: null, // set by CSS file


		recalculate: function() {
			var $body = $('body');

			// All available area, including that available for scrollbars
			$body.css('overflow', 'hidden');
			this.EntireWebArea.width = $body.outerWidth(true); // incl. padding+border
			this.EntireWebArea.height = $body.height(); // incl. padding+border

			// Available area, inside scrollbars (when both present)
			$body.css('overflow', 'scroll');
			this.MinWebArea.width = $body.outerWidth(true); // incl. padding+border
			this.MinWebArea.height = $body.height(); // incl. padding+border

			// Set to auto for final use/rendering
			$body.css('overflow', 'auto');
			this.WebArea.width = $body.outerWidth(true); // incl. padding+border
			this.WebArea.height = $body.height(); // incl. padding+border

			var outerMarginTotal = $body.outerWidth(true) - $body.width();
			this.OuterMarginWidth = Math.floor(outerMarginTotal / 2);
		}
	},

	Thumbnails: {
		SourceSize: {width: 319, height: 179}
	}

};



$(document).ready(function () {

	$(window).resize()

});


$(window).resize(function () {

	SpeedReader.Viewport.recalculate();

	var thumbHorizSep = SpeedReader.Viewport.OuterMarginWidth;
	var $firstRenderedThumb = $('a.thumbnail_link:first');

	var thumbBorderWidthTotal =
		$firstRenderedThumb.outerWidth(false) // element width with padding, border (but not margin)
		- $firstRenderedThumb.innerWidth(); // element width with padding (but not border or margin)

	var thumbWidthsByRowCount = {};

	for (var thumbsPerRow = 2; thumbsPerRow < 10; thumbsPerRow++) {

		// Calculate thumbnail width when this many thumbnails are displayed per row

		/**
			Calculation as equation:

			viewportInside.width =
				thumbNewWidth * thumbsPerRow +
				thumbBorderWidthTotal * thumbsPerRow +
				thumbHorizSep * (thumbsPerRow - 1) +
				outerMarginTotal
		 */

		thumbWidthsByRowCount[thumbsPerRow] = Math.floor(
			(SpeedReader.Viewport.MinWebArea.width
				- (thumbBorderWidthTotal * thumbsPerRow)
				- (thumbHorizSep * (thumbsPerRow - 1))
				- (SpeedReader.Viewport.OuterMarginWidth * 2)
				) / thumbsPerRow
		);
	}

	var selThumbsPerRow = 5; // Fixed at 5 rows for now

	var thumbWidth = thumbWidthsByRowCount[selThumbsPerRow];
	if (thumbWidth >= SpeedReader.Thumbnails.SourceSize.width) {
		thumbWidth = SpeedReader.Thumbnails.SourceSize.width;
	}

	var thumbHeight = (
		SpeedReader.Thumbnails.SourceSize.height
		/ SpeedReader.Thumbnails.SourceSize.width
	) * thumbWidth;

	var $thumbnailLinks = $('a.thumbnail_link');

	$thumbnailLinks.css({
		width: thumbWidth + 'px',
		height: thumbHeight + 'px',
		margin: '0'
	});


	// Remove left margin on first thumbnails in each row (one for every
	// thumbs-per-row)

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
	});

	$thumbnailLinks.children('img').each(function () {

		var $img = $(this);
		var $a = $img.parent(); // a.thumbnail_link


		$img.on('load', function () {
			$a.css('transform', 'scale(1,1)');
			$a.css('opacity', '1');
		});

		// Trigger load event for cached images
		if (this.complete) $img.trigger('load');

	})


});





