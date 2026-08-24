/**
 * Tests for the promotions lifecycle emitter (assets/js/src/common/promoEvents.js).
 *
 * The emitter keeps module-level state (dedup set, queue), so every test
 * loads a fresh module copy via jest.isolateModules.
 */

const loadEmitter = () => {
	let mod;
	jest.isolateModules( () => {
		mod = require( '../../assets/js/src/common/promoEvents' );
	} );
	return mod;
};

const armGlobals = ( { tiTrk = true, products = [ { slug: 'neve', trackHash: 'free', consent: true } ], slug = 'neve' } = {} ) => {
	window.themeisleSDKPromotions = { slug };
	window.tiTelemetry = { products };

	const add = jest.fn();
	const withFn = jest.fn( () => ( { add } ) );

	if ( tiTrk ) {
		window.tiTrk = { with: withFn };
	} else {
		delete window.tiTrk;
	}

	return { add, withFn };
};

describe( 'trackPromoEvent', () => {
	afterEach( () => {
		delete window.tiTrk;
		delete window.tiTelemetry;
		delete window.themeisleSDKPromotions;
		jest.useRealTimers();
	} );

	test( 'emits an event with exactly the allowlisted payload keys', () => {
		const { add, withFn } = armGlobals();
		const { trackPromoEvent } = loadEmitter();

		trackPromoEvent( 'impression', 'hyve-plugins-install', 'hyve-lite' );

		expect( withFn ).toHaveBeenCalledWith( 'neve' );
		expect( add ).toHaveBeenCalledTimes( 1 );

		const [ payload, options ] = add.mock.calls[ 0 ];
		expect( Object.keys( payload ).sort() ).toEqual( [ 'feature', 'featureComponent', 'featureValue', 'groupID' ] );
		expect( payload ).toEqual( {
			feature: 'promotions',
			featureComponent: 'hyve-plugins-install',
			featureValue: 'impression',
			groupID: 'hyve-lite',
		} );
		expect( options ).toEqual( {} );
	} );

	test( 'interactions pass sendNow so they survive navigation', () => {
		const { add } = armGlobals();
		const { trackPromoInteraction } = loadEmitter();

		trackPromoInteraction( 'dismiss', 'rop-posts', 'tweet-old-post' );

		expect( add ).toHaveBeenCalledWith( expect.any( Object ), { sendNow: true } );
	} );

	test( 'same promo/action pair emits once per page session, even after a flush', () => {
		const { add } = armGlobals();
		const { trackPromoEvent } = loadEmitter();

		trackPromoEvent( 'impression', 'om-media', 'optimole-wp' );
		// tiTrk's own map resets on upload; the emitter must stay idempotent regardless.
		trackPromoEvent( 'impression', 'om-media', 'optimole-wp' );
		trackPromoEvent( 'dismiss', 'om-media', 'optimole-wp' );

		expect( add ).toHaveBeenCalledTimes( 2 );
	} );

	test( 'rejects unknown actions and non-string promo keys without throwing', () => {
		const { add } = armGlobals();
		const { trackPromoEvent } = loadEmitter();

		expect( () => {
			trackPromoEvent( 'made-up-action', 'om-media', 'optimole-wp' );
			trackPromoEvent( 'impression', null, 'optimole-wp' );
			trackPromoEvent( 'impression', false, 'optimole-wp' );
			trackPromoEvent( 'impression', '', 'optimole-wp' );
			trackPromoEvent( 'impression', 'om-media', undefined );
		} ).not.toThrow();

		expect( add ).not.toHaveBeenCalled();
	} );

	test( 'does not throw when tiTrk exists but the products list is missing', () => {
		const { add } = armGlobals();
		window.tiTelemetry = {};
		const { trackPromoEvent } = loadEmitter();

		expect( () => trackPromoEvent( 'impression', 'om-media', 'optimole-wp' ) ).not.toThrow();
		expect( add ).not.toHaveBeenCalled();
	} );

	test( 'drops events silently when the host slug has no telemetry entry', () => {
		const { add } = armGlobals( { products: [ { slug: 'otter', trackHash: 'free', consent: true } ] } );
		const { trackPromoEvent } = loadEmitter();

		trackPromoEvent( 'impression', 'om-media', 'optimole-wp' );

		expect( add ).not.toHaveBeenCalled();
	} );

	test( 'buffers events fired before tiTrk exists and flushes once it does', () => {
		jest.useFakeTimers();
		armGlobals( { tiTrk: false } );
		const { trackPromoEvent } = loadEmitter();

		trackPromoEvent( 'impression', 'neve-themes-popular', 'neve' );

		// tracking.js evaluates afterwards and defines the global.
		const add = jest.fn();
		window.tiTrk = { with: jest.fn( () => ( { add } ) ) };

		// jsdom readyState is 'complete', so the retry is a queued timeout.
		jest.runAllTimers();

		expect( add ).toHaveBeenCalledTimes( 1 );
		expect( add.mock.calls[ 0 ][ 0 ] ).toEqual( expect.objectContaining( { featureComponent: 'neve-themes-popular' } ) );
	} );

	test( 'stays silent when tiTrk never becomes available', () => {
		jest.useFakeTimers();
		armGlobals( { tiTrk: false } );
		const { trackPromoEvent } = loadEmitter();

		expect( () => {
			trackPromoEvent( 'impression', 'neve-themes-popular', 'neve' );
			jest.runAllTimers();
			window.dispatchEvent( new Event( 'load' ) );
		} ).not.toThrow();
	} );
} );
