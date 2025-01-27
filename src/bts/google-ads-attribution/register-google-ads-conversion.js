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

	let value = parseFloat(params.get(searchKeys.value));
	let transaction = params.get(searchKeys.id);
	let eventId = params.get(searchKeys.eventId);
	function gtag() {
		window.dataLayer = [];
		window.dataLayer.push(arguments);
	}
	//if (!gtag) return; //exit if gtag is not on the page

	// gtag("event", "conversion", {
	// 	send_to: searchKeys.adsId,
	// 	value: value,
	// 	currency: "USD",
	// 	transaction_id: transaction,
	// 	eventid: eventId,
	// });
	gtag("event", "conversion", body);
	console.log("conversion recorded");
})();
