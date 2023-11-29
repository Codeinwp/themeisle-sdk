/**
 * WordPress dependencies.
 */
import api from '@wordpress/api';

import {
	dispatch,
	useSelect
} from '@wordpress/data';

import {
	useState
} from '@wordpress/element';

/**
 * useSettings Hook.
 *
 * useSettings hook to get/update WordPress' settings database.
 *
 * Setting field needs to be registered to REST for this function to work.
 *
 * This hook works similar to get_option and update_option in PHP just without the option for a default value.
 * For notificiations to work, you need to add a Snackbar section to your React codebase if it isn't being
 * used inside the block editor.
 *
 * @see https://github.com/WordPress/gutenberg/blob/trunk/packages/editor/src/components/editor-snackbars/index.js
 * @author  Hardeep Asrani <hardeepasrani@gmail.com>
 * @version 1.1
 *
 */
const useSettings = () => {
	const { createNotice } = dispatch( 'core/notices' );

	const [ settings, setSettings ] = useState({});
	const [ status, setStatus ] = useState( 'loading' );

	useSelect( select => {
		
		// Bail out if settings are already loaded.
		if ( Object.keys( settings ).length ) {
			return;
		}
		
		const { getEntityRecord } = select( 'core' );
		const request = getEntityRecord( 'root', 'site' );

		if ( request ) {
			setStatus( 'loaded' );
			setSettings( request );
		}
	}, []);

	const getOption = option => settings?.[option];

	const updateOption = ( option, value, success = 'Settings saved.' ) => {
		setStatus( 'saving' );

		const save = new api.models.Settings({ [option]: value }).save();

		save.success( ( response, status ) => {
			if ( 'success' === status ) {
				setStatus( 'loaded' );

				createNotice(
					'success',
					success,
					{
						isDismissible: true,
						type: 'snackbar'
					}
				);
			}

			if ( 'error' === status ) {
				setStatus( 'error' );

				createNotice(
					'error',
					'An unknown error occurred.',
					{
						isDismissible: true,
						type: 'snackbar'
					}
				);
			}
			
			setSettings( response );
		});

		save.error( ( response ) => {
			setStatus( 'error' );

			createNotice(
				'error',
				response.responseJSON.message ? response.responseJSON.message : 'An unknown error occurred.',
				{
					isDismissible: true,
					type: 'snackbar'
				}
			);
		});
	};

	return [ getOption, updateOption, status ];
};

export default useSettings;
