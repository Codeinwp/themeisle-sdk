import hash from 'object-hash';

/**
 * Return the value of pair [condition, value] which has the first true condition.
 *
 * @param {([bool, any]|[any])[]} arr - Array of pairs [condition, value].
 * @returns {*}
 */
export const getChoice = arr => {
	const r = arr?.filter( x => x?.[0])?.[0];
	return r?.[1] ?? r?.[0];
};

/**
 * @typedef {Object} TrackingData
 * @property {string} [block] - The block identifier. (E.g: 'core/paragraph', 'core/image')
 * @property {string} [env] - The environment. (E.g: 'customizer', 'site-editor', 'widgets', 'post-editor')
 * @property {('block-created'|'block-updated'|'block-deleted')} [action] - The action performed.
 * @property {string} [feature] - The feature identifier. (E.g: 'webhooks', 'form-file')
 * @property {string} [groupID] - The group identifier. Used for tracking the evolution of the features in a group (it can be a block, a component, etc.)
 * @property {string} [featureComponent] - The component of the feature. (E.g: 'file-size', 'file-number')
 * @property {string} [featureValue] - The value of the feature.
 * @property {boolean} [hasOpenAIKey] - Indicates whether an OpenAI key is present.
 * @property {string} [usedTheme] - The theme used.
 */

/**
 * @typedef {Object} TrackingPayload
 * @property {string} slug - The slug identifier, always 'otter'.
 * @property {string} site - The site identifier.
 * @property {string} license - The license identifier.
 * @property {TrackingData} data - The tracking data.
 * @property {string} createdAt - The creation timestamp.
 */

/**
 * @typedef {Object} EventResponse
 * @property {string} [error] - Description of the error, if any.
 * @property {boolean} [success] - Indicates whether the operation was successful.
 * @property {*} [response] - The response data.
 */

/**
 * @typedef {Object} EventOptions
 * @property {boolean} [directSave] - If true, the data will be saved without any modification from the accumulator. Check the `trkMetadata` function for more details.
 * @property {boolean} [consent] - Bypass the consent check. Use this for data that does not need consent.
 * @property {boolean} [refreshTimer] - Refresh the timer to send the events automatically.
 * @property {boolean} [sendNow] - Send the events immediately.
 * @property {boolean} [ignoreLimit] - Ignore the limit of the events to be send.
 */

export class EventTrackingAccumulator {
	constructor( tiTelemetry ) {

		tiTelemetry ??= {}

		/**
		 * @type {Map<string, TrackingData>} - The events to be sent.
		 */
		this.events = new Map();

		/**
		 * @type {number} - The maximum number of events to be sent at once.
		 * @private
		 * @readonly
		 * @constant
		 */
		this.eventsLimit = 50;

		/**
		 * @type {Array<(response: EventResponse) => void>} - The listeners to be notified when the events are sent.
		 * @private
		 * @readonly
		 */
		this.listeners = [];

		/**
		 * @type {number|null} - The interval to send the events automatically.
		 * @private
		 */
		this.interval = null;

		/**
         * @type {boolean} - Indicates whether the user has given consent to send the events.
         */
		this.consent = false;

		/**
         * @type {string} - The endpoint to send the events.
         */
		this.endpoint = tiTelemetry?.endpoint;

        /**
         * @type {{slug: string, trackHash: string, consent: bool}[]} - The products to send the events.
         */
        this.products = tiTelemetry?.products;
     
		/**
		 * @type {number} - The interval to send the events automatically.
		 */
		this.autoSendIntervalTime = 5 * 60 * 1000; // 5 minutes
	}

	/**
	 * Set tracking data to the accumulator. If the key already exists, it will overwrite the existing data.
	 *
	 * @param {string} key - The key to store the data under. With the same key, the data will be overwritten.
	 * @param {TrackingData} data - Tracking data to be sent.
	 * @param {EventOptions} [options] - Options to be passed to the accumulator.
	 */
	_set = ( key, data, options ) => {
		
		// Check the slug is it can tracked.
        if ( ! this.hasProduct( data.slug ) ) {
            return;
        }

		if ( ! ( options?.consent || this.getProductConsent( data.slug ) ) ) {
			return;
		}

		if ( ! this.validate( data ) ) {
			return;
		}

		const enhancedData = options?.directSave ? data : this.trkMetadata( data );
		this.events.set( key, enhancedData );

		if ( options?.refreshTimer ) {
			this.refreshTimer();
		}

		if ( options?.sendNow ) {
			this.uploadEvents();
		} else if ( ! options?.ignoreLimit ) {
			this.sendIfLimitReached();
		}
	};

	/**
	 * Add tracking data to the accumulator. If the hash of the data already exists, it will overwrite the existing data.
	 *
	 * @param {TrackingData} data - Tracking data to be sent.
	 * @param {EventOptions} [options] - Options to be passed to the accumulator.
	 * @returns {string} - Hash of the data.
	 */
	_add = ( data, options ) => {
		const h = hash( data );
		this._set( h.toString(), data, options );
		return h.toString();
	};

	/**
     * Enhance the tracking data with the plugin slug and the environment information.
     *
     * @param {string} pluginSlug  - The slug of the plugin.
     */
	with = ( pluginSlug ) => {
		const payload = {
			slug: pluginSlug,
			...this.envInfo()
		};
		return {
			add: ( data, options ) => this._add({ ...payload, ...data }, options ),
			set: ( key, data, options ) => this._set( key, { ...payload, ...data }, options ),
			base: this
		};
	};

	/**
     * Get the environment information.
     *
     * @returns {Object} - The environment information.
     */
	envInfo = () => {
		return {
			site: window.location.hostname,
		};
	};

	/**
	 * Send all the events in the accumulator. Clears the accumulator after sending. All the listeners will be notified.
	 */
	uploadEvents = async() => {
		if ( 0 === this.events.size ) {
			return;
		}
		try {
			const events = Array.from( this.events.values() );
			this.events.clear();
			const response = await this.sendBulkTracking( events.map( ({ slug, site, license, ...data }) => ({ slug, site, license, data }) ) );

			if ( ! response.ok ) {
				this.listeners.forEach( listener => listener({ success: false, error: 'Failed to send tracking events' }) );
			}

			const body = await response.json();
			this.listeners.forEach( listener => listener({ success: true, response: body }) );
		} catch ( error ) {
			console.error( error );
		}
	};

	/**
	 * Automatically send all the events if the limit is reached.
	 * 
	 * @returns - Promise that resolves when all the events are sent.
	 */
	sendIfLimitReached = () => {
		if ( this.events.size >= this.eventsLimit ) {
			return this.uploadEvents();
		}
	};

	/**
	 * Subscribe to the event when the events are sent.
	 *
	 * @param {(response: EventResponse) => void} callback - Callback to be called when the events are sent.
	 * @returns {() => void} - Function to unsubscribe from the event.
	 */
	subscribe = ( callback ) => {
		this.listeners.push( callback );
		return () => {
			this.listeners = this.listeners.filter( listener => listener !== callback );
		};
	};

	/**
	 * Check if the user has given consent to send the events.
	 *
	 * @returns - True if the user has given consent to send the events.
	 */
	hasConsent = () => {
		return this.consent;
	};

	/**
	 * Send the tracking data to the server.
	 *
	 * @param {Array<TrackingData>} payload - Tracking data to be sent.
	 * @returns {Promise<Response>} - Response from the server.
	 */
	sendBulkTracking = ( payload ) => {
		return fetch( this.endpoint, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json'
			},
			body: JSON.stringify( payload )
		});
	};

	/**
	 * Add common metadata to the tracking data. Metadata includes the environment, etc. It does not overwrite the given data.
	 *
	 * @param {TrackingData} data - Tracking data to be sent.
	 * @returns {TrackingData} - Tracking data with the common metadata.
	 */
	trkMetadata = ( data ) => {
		return {
			env: getChoice([
				[ window.location.href.includes( 'customize.php' ), 'customizer' ],
				[ window.location.href.includes( 'site-editor.php' ), 'site-editor' ],
				[ window.location.href.includes( 'widgets.php' ), 'widgets' ],
				[ window.location.href.includes( 'admin.php' ), 'admin' ],
				[ 'post-editor' ]
			]),
            license: this.getProductTackHash( data.slug ),
			...( data ?? {})
		};
	};

	/**
	 * Check if the product is tracked.
	 * 
	 * @param {string} slug Product slug
	 * @returns {boolean} True if the product is tracked.
	 */
	hasProduct = ( slug ) => {
		return this.products.some( product => product?.slug?.includes( slug ) );
	}

	/**
	 * Get the track hash of the product.
	 * 
	 * @param {string} slug Product slug
	 * @returns {string} The track hash of the product.
	 */
	getProductTackHash = ( slug ) => {
		return this.products.find( product => product?.slug?.includes(slug) )?.trackHash;
	};

	/**
	 * Get the consent of the product.
	 * 
	 * @param {string} slug Product slug
	 * @returns {boolean} The consent of the product.
	 */
	getProductConsent = ( slug ) => {
		return this.products.find( product => product?.slug?.includes( slug ) )?.consent;
	}

	/**
	 * Start the interval to send the events automatically.
	 */
	start = () => {
		if ( this.interval ) {
			return;
		}

		this.interval = window.setInterval( () => {
			this.uploadEvents();
		}, this.autoSendIntervalTime ); // 5 minutes
	};

	/**
	 * Stop the interval to send the events automatically.
	 */
	stop = () => {
		if ( this.interval ) {
			window.clearInterval( this.interval );
			this.interval = null;
		}
	};

	/**
	 * Refresh the interval to send the events automatically.
	 */
	refreshTimer = () => {
		this.stop();
		this.start();
	};

	/**
	 * Validate the tracking data. The data is valid if it has at least one property and all the values are defined.
	 *
	 * @param {any} data - Tracking data to be validated.
	 * @returns {boolean} - True if the data is valid.
	 */
	validate = ( data ) => {
		if ( 'object' === typeof data ) {

			if ( 0 === Object.keys( data ).length ) {
				return false;
			}

			return Object.values( data ).every( this.validate );
		}

		return 'undefined' !== typeof data;
	};

	/**
     * Clone the accumulator.
     * @returns {EventTrackingAccumulator} - A clone of the accumulator.
     */
	clone = () => {
		const clone = new EventTrackingAccumulator();
		clone.events = new Map( this.events );
		clone.listeners = [ ...this.listeners ];
		clone.interval = this.interval;
		clone.consent = this.consent;
		clone.endpoint = this.endpoint;
		return clone;
	};
}

// Initialize the accumulator.
window.tiTrk = new EventTrackingAccumulator( window?.tiTelemetry );

// Send the events on save for the customizer.
window?.wp?.customize?.bind( 'save', () => {
	window?.tiTrk?.uploadEvents();
} );

// When tab is closed, send all events.
window.addEventListener( 'beforeunload', async() => {
	window?.tiTrk?.uploadEvents();
});