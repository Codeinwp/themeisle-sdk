import { createRoot } from '@wordpress/element';
import PromotionNotice from './common/PromotionNotice';

const renderWPFullPayNotice = () => {
    if ( window.themeisleSDKPromotions.option['wp-full-pay-plugins-install']) {
        return;
    }
    const root = document.querySelector( '#ti-wp-full-pay-notice' );
    if ( ! root ) {
        return;
    }
    const {
        title,
        option,
        optionKey,
        labels,
        wpFullPayActivationUrl,
        wpFullPayDash,
    } = window.themeisleSDKPromotions;
    createRoot( root ).render(
        <PromotionNotice
            title={title}
            option={option}
            optionKey={optionKey}
            labels={labels}
            pluginSlug="wp-full-stripe-free"
            activationUrl={wpFullPayActivationUrl}
            dashboardUrl={wpFullPayDash}
            learnMoreUrl="https://wordpress.org/plugins/wp-full-stripe-free/"
            labelKey="wp_full_pay"
            optionInstallKey="wp-full-pay-plugins-install"
            installedOptionKey="themeisle_sdk_promotions_wp_full_pay_installed"
            onDismiss={() => {
                root.style.display = 'none';
            }}
        />
    );
};

renderWPFullPayNotice();
