import { createRoot } from '@wordpress/element';
import PromotionNotice from './common/PromotionNotice';

const renderEasyMcpNotice = () => {
    const promoKey = window.themeisleSDKPromotions.showPromotion;
    if ( 'easy-mcp-plugins-install' !== promoKey && 'easy-mcp-profile' !== promoKey ) {
        return;
    }
    if ( window.themeisleSDKPromotions.option[ promoKey ] ) {
        return;
    }
    const root = document.querySelector( '#ti-easy-mcp-notice' );
    if ( ! root ) {
        return;
    }
    const {
        title,
        option,
        optionKey,
        labels,
        easyMcpActivationUrl,
        easyMcpDash,
    } = window.themeisleSDKPromotions;
    createRoot( root ).render(
        <PromotionNotice
            title={title}
            option={option}
            optionKey={optionKey}
            labels={labels}
            pluginSlug="easy-mcp-ai"
            activationUrl={easyMcpActivationUrl}
            dashboardUrl={easyMcpDash}
            learnMoreUrl="https://wordpress.org/plugins/easy-mcp-ai/"
            labelKey="easy_mcp"
            optionInstallKey={promoKey}
            installedOptionKey="themeisle_sdk_promotions_easy_mcp_installed"
            onDismiss={() => {
                root.style.display = 'none';
            }}
        />
    );
};

renderEasyMcpNotice();
