(function () {
	let params = new URLSearchParams(window.location.search);
	let body = {};

	let bodyObj = JSON.parse(decodeURIComponent(searchKeys.body));

	Object.entries(bodyObj).forEach((e) => {
		let [adKey, param] = e;
		let value = param;

		if (param.startsWith("~")) {
			param = param.substring(1);
			value = params.get(param);
		}

		if (adKey.startsWith("#")) {
			adKey = adKey.substring(1);
			value = parseFloat(value);
		}

		body[adKey] = value;
	});
	//FOR DEBUGGING ONLY
	function gtag() {
		window.dataLayer = [];
		window.dataLayer.push(arguments);
	}

	gtag("event", "conversion", body);
	console.info("Conversion recorded", body);
})();
