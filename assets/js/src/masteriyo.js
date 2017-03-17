import { createRoot } from '@wordpress/element';
import PromotionNotice from './common/PromotionNotice';

const renderMasteriyoNotice = () => {
    if ( window.themeisleSDKPromotions.option['masteriyo-plugins-install']) {
        return;
    }
    const root = document.querySelector( '#ti-masteriyo-notice' );
    if ( ! root ) {
        return;
    }
    const {
        title,
        option,
        optionKey,
        labels,
        masteriyoActivationUrl,
        masteriyoDash,
    } = window.themeisleSDKPromotions;
    createRoot( root ).render(
        <PromotionNotice
            title={title}
            option={option}
            optionKey={optionKey}
            labels={labels}
            pluginSlug="learning-management-system"
            activationUrl={masteriyoActivationUrl}
            dashboardUrl={masteriyoDash}
            learnMoreUrl="https://wordpress.org/plugins/learning-management-system/"
            labelKey="masteriyo"
            optionInstallKey="masteriyo-plugins-install"
            installedOptionKey="themeisle_sdk_promotions_masteriyo_installed"
            onDismiss={() => {
                root.style.display = 'none';
            }}
        />
    );
};

renderMasteriyoNotice();
