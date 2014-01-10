$(document).ready(function() {
	$('.refresh-interval').each(function() {
		var $this = $(this),
			url = $this.data('refresh-interval-url'),
			wait = $this.data('refresh-interval-wait');
		if (!wait) {
			wait = 5000;
		}
		if (url) {
			setInterval(function() {
				$this.load(url)
			}, wait);
		}
	});
});