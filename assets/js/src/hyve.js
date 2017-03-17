import { createRoot } from '@wordpress/element';
import PromotionNotice from './common/PromotionNotice';

const renderHyveNotice = () => {
    if ( window.themeisleSDKPromotions.option['hyve-plugins-install']) {
        return;
    }
    const root = document.querySelector( '#ti-hyve-notice' );
    if ( ! root ) {
        return;
    }
    const {
        title,
        option,
        optionKey,
        labels,
        hyveActivationUrl,
        hyveDash,
    } = window.themeisleSDKPromotions;
    createRoot( root ).render(
        <PromotionNotice
            title={title}
            option={option}
            optionKey={optionKey}
            labels={labels}
            pluginSlug="hyve-lite"
            activationUrl={hyveActivationUrl}
            dashboardUrl={hyveDash}
            learnMoreUrl="https://wordpress.org/plugins/hyve-lite/"
            labelKey="hyve"
            optionInstallKey="hyve-plugins-install"
            installedOptionKey="themeisle_sdk_promotions_hyve_installed"
            onDismiss={() => {
                root.style.display = 'none';
            }}
        />
    );
};

renderHyveNotice();
