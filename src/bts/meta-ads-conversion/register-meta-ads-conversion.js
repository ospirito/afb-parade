( function () {
	//For Debugging Only
	function fbq() {
		window.fbdebug = window.fbdebug || [];
		window.fbdebug.push( arguments );
	}
	if ( typeof fbq !== 'function' ) {
		return;
	} //exit if FBQ hasn't loaded

	const params = new URLSearchParams( window.location.search );
	const body = {};

	const bodyParam = params.get( 'body' );
	if ( bodyParam ) {
		const bodyObj = JSON.parse( decodeURIComponent( bodyParam ) );

		Object.entries( bodyObj ).forEach( ( e ) => {
			let [ adKey, param ] = e;
			let value = param;

			if ( adKey.startsWith( '[]' ) ) {
				adKey = adKey.substring( 2 );
				value = params.get( param );
				try {
					value = JSON.parse( value ); //try to parse if the value is passed as JSON
				} finally {
					if ( ! Array.isArray( value ) ) {
						value = [ value ];
					}
				}
			}

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

		fbq( 'track', 'Purchase', body );
	}
} )();
