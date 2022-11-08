import {render, unmountComponentAtNode} from '@wordpress/element';
import {registerPlugin} from '@wordpress/plugins';
import {PluginPostPublishPanel} from '@wordpress/edit-post';
import {useSelect} from '@wordpress/data';

import {getBlocksByType} from "./common/utils";
import OptimoleNotice from "./OptimoleNotice";

const {showPromotion, option} = window.themeisleSDKPromotions;

const remove = () => {
    const div = document.querySelector('#ti-optml-notice-helper');
    if (!div) {
        return;
    }
    unmountComponentAtNode(div);
}

const add = () => {
    const mount = document.querySelector('#ti-optml-notice-helper');

    if (option['om-attachment']) {
        return;
    }

    if (!mount) {
        return;
    }

    render(<div className="notice notice-info ti-sdk-om-notice" style={{margin: 0}}>
        <OptimoleNotice
            noImage={true}
            type="om-attachment"
            onDismiss={() => {
                remove();
                window.themeisleSDKPromotions.option['om-attachment'] = true;
            }}/>
    </div>, mount);
}


if (showPromotion === 'om-attachment') {
    wp.media.view.Attachment.Details.prototype.on("ready", () => {
        setTimeout(() => {
            remove();
            add();
        }, 100)
    });
    wp.media.view.Modal.prototype.on("close", () => {
        setTimeout(remove, 100)
    });
}

if (showPromotion === 'om-media') {
    const root = document.getElementById('ti-optml-notice');

    if (root) {
        render(<OptimoleNotice
            type="om-attachment"
            onDismiss={() => {
                unmountComponentAtNode(root);
                root.parentNode.removeChild(root);
            }}
        />, root);
    }
}

if (showPromotion === 'om-editor') {
    const TiSDKPromo = () => {
        const {getBlocks} = useSelect((select) => {
            const {getBlocks} = select('core/block-editor');
            return {
                getBlocks
            };
        });

        const imageBlocksCount = getBlocksByType(getBlocks(), 'core/image').length;

        if (imageBlocksCount < 2) {
            return null;
        }

        return (
            <PluginPostPublishPanel className="ti-sdk-optimole-post-publish">
                <OptimoleNotice stacked type="editor"/>
            </PluginPostPublishPanel>
        );
    };

    registerPlugin('post-publish-panel-test', {
        render: TiSDKPromo,
    });
}
if (showPromotion === 'om-elementor' && window.elementor) {
    const runElementorActions = (panel, model, view) => {

        if (option['om-elementor']) {
            return;
        }

        const controlsWrap = document.querySelector('#elementor-panel__editor__help');
        const mountPoint = document.createElement('div');
        mountPoint.id = 'ti-optml-notice';

        if (controlsWrap) {
            // insert before the help button
            controlsWrap.parentNode.insertBefore(mountPoint, controlsWrap);
            render(<OptimoleNotice stacked type="elementor"
            onDismiss={() => {
                window.themeisleSDKPromotions.option['om-elementor'] = true;
                unmountComponentAtNode(mountPoint);
            }}
            />, mountPoint);
        }


    }

    elementor.hooks.addAction('panel/open_editor/widget/image', runElementorActions);
}