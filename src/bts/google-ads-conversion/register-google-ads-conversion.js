( function () {
	const params = new URLSearchParams( window.location.search );
	const body = {};

	const bodyParam = params.get( 'body' );
	if ( bodyParam ) {
		const bodyObj = JSON.parse( decodeURIComponent( bodyParam ) );

		Object.entries( bodyObj ).forEach( ( e ) => {
			let [ adKey, param ] = e;
			let value = param;

			if ( param.startsWith( '~' ) ) {
				param = param.substring( 1 );
				value = params.get( param );
			}

			if ( adKey.startsWith( '#' ) ) {
				adKey = adKey.substring( 1 );
				value = parseFloat( value );
			}

			body[ adKey ] = value;
		} );
		//FOR DEBUGGING ONLY
		function gtag() {
			window.dataLayer = [];
			window.dataLayer.push( arguments );
		}

		gtag( 'event', 'conversion', body );
	}
} )();
