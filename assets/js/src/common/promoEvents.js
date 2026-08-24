/**
 * Lifecycle telemetry for SDK promotions, on top of the tiTrk accumulator.
 *
 * Events are dropped silently unless the host product has telemetry consent
 * (present in `window.tiTelemetry.products`). Telemetry must never break the
 * promo UI: every entry point is guarded and wrapped.
 */
const ACTIONS = [
	'impression',
	'cta-install',
	'cta-activate',
	'cta-learn-more',
	'cta-preview',
	'cta-gotodash',
	'install-success',
	'install-fail',
	'dismiss',
];

// tiTrk's own hash-dedup resets on every flush, so idempotence lives here.
const emitted = new Set();
const queue = [];
let flushArmed = false;

const canEmit = () => {
	const products = window.tiTelemetry?.products;
	const slug = window.themeisleSDKPromotions?.slug;

	return !! (
		slug &&
		'function' === typeof window.tiTrk?.with &&
		Array.isArray( products ) &&
		products.some( ( product ) => product?.slug === slug )
	);
};

const send = ( event ) => {
	window.tiTrk.with( window.themeisleSDKPromotions.slug ).add(
		{
			feature: 'promotions',
			featureComponent: event.promoKey,
			featureValue: event.action,
			groupID: event.promoted,
		},
		event.options
	);
};

const flushQueue = () => {
	try {
		if ( ! canEmit() ) {
			return;
		}
		while ( queue.length ) {
			send( queue.shift() );
		}
	} catch ( e ) {
		queue.length = 0;
	}
};

/**
 * The promos bundle can execute before tracking.js defines `window.tiTrk`
 * (both load in the footer, order is not guaranteed). Retry the flush at the
 * points where the remaining scripts are guaranteed to have run.
 */
const armFlush = () => {
	if ( flushArmed ) {
		return;
	}
	flushArmed = true;

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', flushQueue, { once: true } );
	}

	if ( 'complete' === document.readyState ) {
		setTimeout( flushQueue, 0 );
	} else {
		window.addEventListener( 'load', flushQueue, { once: true } );
	}
};

/**
 * Record a promotion lifecycle event.
 *
 * @param {string} action   One of ACTIONS.
 * @param {string} promoKey Promotion key, e.g. 'om-media', 'hyve-plugins-install'.
 * @param {string} promoted Slug of the promoted product, e.g. 'optimole-wp'.
 * @param {Object} options  tiTrk event options (e.g. { sendNow: true }).
 */
export const trackPromoEvent = ( action, promoKey, promoted, options = {} ) => {
	try {
		if (
			! ACTIONS.includes( action ) ||
			'string' !== typeof promoKey || ! promoKey ||
			'string' !== typeof promoted || ! promoted
		) {
			return;
		}

		const dedupKey = promoKey + '|' + action;
		if ( emitted.has( dedupKey ) ) {
			return;
		}
		emitted.add( dedupKey );

		const event = { action, promoKey, promoted, options };

		if ( canEmit() ) {
			send( event );
			return;
		}

		queue.push( event );
		armFlush();
	} catch ( e ) {
		// Swallow: analytics must never surface in the promo UI.
	}
};

/**
 * Interaction events flush immediately so they survive an imminent
 * navigation (e.g. the Neve theme-activation redirect).
 *
 * @param {string} action   One of ACTIONS.
 * @param {string} promoKey Promotion key.
 * @param {string} promoted Slug of the promoted product.
 */
export const trackPromoInteraction = ( action, promoKey, promoted ) =>
	trackPromoEvent( action, promoKey, promoted, { sendNow: true } );
