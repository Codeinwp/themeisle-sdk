import { Fragment, render, useState } from '@wordpress/element';
import { Button } from '@wordpress/components';
import useSettings from './common/useSettings';

const NeveFSENotice = ({ onDismiss = () => {} }) => {
	const [getOption, updateOption] = useSettings();

	const dismissNotice = async () => {
		const newValue = { ...window.themeisleSDKPromotions.option };
		newValue['neve-fse-themes-popular'] = new Date().getTime() / 1000 | 0;
		window.themeisleSDKPromotions.option = newValue;
		await updateOption( window.themeisleSDKPromotions.optionKey, JSON.stringify( newValue ) );

		if ( onDismiss ) {
			onDismiss();
		}
	};

	const { neveFSEMoreUrl } = window.themeisleSDKPromotions;

	return (
		<Fragment>
			<Button
				onClick={ dismissNotice }
				variant="link"
				className="om-notice-dismiss"
			>
				<span className="dashicons-no-alt dashicons"/>
				<span className="screen-reader-text">Dismiss this notice.</span>
			</Button>

			<p>Meet <b>Neve FSE</b> from the makes of { window.themeisleSDKPromotions.product }. A theme that makes full site editing on WordPress straightforward and user-friendly.</p>

			<div className="neve-fse-notice-actions">
				<Button
					variant="link"
					target="_blank"
					href={ neveFSEMoreUrl }
				>
					<span className="dashicons dashicons-external"/>
					<span>Learn more</span>
				</Button>
			</div>
		</Fragment>
	);
};

class NeveFSE {
	constructor() {
		const {showPromotion, debug} = window.themeisleSDKPromotions;
		this.promo = showPromotion;
		this.debug = debug === '1';
		this.domRef = null;

		this.run();
	}

	run() {
		if ( window.themeisleSDKPromotions.option['neve-fse-themes-popular'] ) {
			return;
		}

		const root = document.querySelector( '#ti-neve-fse-notice' );

		if ( !root ) {
			return;
		}

		render(
			<NeveFSENotice
				onDismiss={() => {
					root.style.display = 'none';
				}}
			/>,
			root
		);
	}
}

new NeveFSE();
