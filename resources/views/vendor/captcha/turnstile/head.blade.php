<script>
	turnstileReady = function() {
		window.dispatchEvent(new CustomEvent('turnstile-ready'));
	}

	document.addEventListener('alpine:initialized', () => {
		try {
			window.turnstile?.ready(() => {
				turnstileReady();
			});
		} catch (e) {
		}
	});
</script>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js?onload=turnstileReady&render=explicit" async></script>