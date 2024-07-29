import {Fragment, render, unmountComponentAtNode, useState} from '@wordpress/element';
import {registerPlugin,} from '@wordpress/plugins';
import {PluginPostPublishPanel} from '@wordpress/edit-post';
import {useSelect} from '@wordpress/data';
import {createHigherOrderComponent} from '@wordpress/compose';
import {addFilter} from '@wordpress/hooks';
import {InspectorControls} from '@wordpress/block-editor';

import {getBlocksByType} from "./common/utils";
import RedirectionForCF7 from "./RedirectionForCF7";

class RedirectionForCF7Notice {
    constructor() {
        const {showPromotion, debug} = window.themeisleSDKPromotions;
        this.promo = showPromotion;
        this.debug = debug === '1';
        this.domRef = null;

        this.run();
    }

    run() {
        if (window.themeisleSDKPromotions.option['redirection-cf7']) {
            return;
        }

        const root = document.querySelector('#ti-redirection-cf7-notice');

        if (!root) {
            return;
        }

        render(
            <RedirectionForCF7
                type="redirection-cf7"
                onDismiss={() => {
                    root.style.opacity = 0;
                }}
            />, root);
    }
}

new RedirectionForCF7Notice();