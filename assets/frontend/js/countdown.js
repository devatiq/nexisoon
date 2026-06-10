(function () {
	'use strict';

	function pad(value) {
		return String(value).padStart(2, '0');
	}

	function setUnit(container, unit, value) {
		var target = container.querySelector('[data-unit="' + unit + '"]');

		if (target) {
			target.textContent = pad(value);
		}
	}

	function updateCountdown(container) {
		var target = new Date(container.getAttribute('data-nexisoon-countdown')).getTime();
		var now = Date.now();
		var distance = Math.max(0, target - now);
		var totalSeconds = Math.floor(distance / 1000);
		var days = Math.floor(totalSeconds / 86400);
		var hours = Math.floor((totalSeconds % 86400) / 3600);
		var minutes = Math.floor((totalSeconds % 3600) / 60);
		var seconds = totalSeconds % 60;

		setUnit(container, 'days', days);
		setUnit(container, 'hours', hours);
		setUnit(container, 'minutes', minutes);
		setUnit(container, 'seconds', seconds);

		return distance > 0;
	}

	document.addEventListener('DOMContentLoaded', function () {
		var countdowns = document.querySelectorAll('[data-nexisoon-countdown]');

		countdowns.forEach(function (container) {
			if (!container.getAttribute('data-nexisoon-countdown')) {
				return;
			}

			updateCountdown(container);

			var timer = window.setInterval(function () {
				if (!updateCountdown(container)) {
					window.clearInterval(timer);
				}
			}, 1000);
		});
	});
})();
