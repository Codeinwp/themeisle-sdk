import { useEffect, useRef } from '@wordpress/element';

import { trackPromoEvent } from './promoEvents';

/**
 * Attach to a notice's DOM node to record an `impression` once the node has
 * actually been seen: at least half visible for one second. Collapsed panels
 * and below-the-fold notices never count. Falls back to mount when
 * IntersectionObserver is unavailable.
 *
 * @param {string} promoKey Promotion key.
 * @param {string} promoted Slug of the promoted product.
 * @return {Object} React ref to place on the notice wrapper.
 */
export const useImpression = ( promoKey, promoted ) => {
	const ref = useRef( null );

	useEffect( () => {
		const node = ref.current;
		if ( ! node ) {
			return;
		}

		if ( 'undefined' === typeof window.IntersectionObserver ) {
			trackPromoEvent( 'impression', promoKey, promoted );
			return;
		}

		let timer = null;
		let observer = null;
		try {
			observer = new window.IntersectionObserver(
				( entries ) => {
					entries.forEach( ( entry ) => {
						if ( entry.isIntersecting ) {
							if ( ! timer ) {
								timer = window.setTimeout( () => {
									trackPromoEvent( 'impression', promoKey, promoted );
									observer.disconnect();
								}, 1000 );
							}
						} else if ( timer ) {
							window.clearTimeout( timer );
							timer = null;
						}
					} );
				},
				{ threshold: 0.5 }
			);
			observer.observe( node );
		} catch ( e ) {
			trackPromoEvent( 'impression', promoKey, promoted );
			return;
		}

		return () => {
			if ( timer ) {
				window.clearTimeout( timer );
			}
			observer.disconnect();
		};
	}, [ promoKey, promoted ] );

	return ref;
};

export default useImpression;
