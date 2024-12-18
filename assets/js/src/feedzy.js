import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody } from '@wordpress/components';
import { createHigherOrderComponent } from '@wordpress/compose';
import { addFilter } from '@wordpress/hooks';
import {  useEffect, useState} from '@wordpress/element';

import useSettings from './common/useSettings.js';
const withFeedzyNotice = createHigherOrderComponent((BlockEdit) => {
    return (props) => {

        if (props.name !== 'core/rss' || ! Boolean(window.themeisleSDKPromotions.showPromotion)) {
            return <BlockEdit {...props} />;
        }
        if ('feedzy-editor' !== window.themeisleSDKPromotions.showPromotion) {
            return <BlockEdit {...props} />;
        }
        const [getOption, updateOption, status] = useSettings(); 
        const [hasSkipped, setHasSkipped] = useState(false);

        const onSkip = () => {
            const option = {...window.themeisleSDKPromotions.option};
            option[window.themeisleSDKPromotions.showPromotion] = new Date().getTime() / 1000 | 0;
            updateOption('themeisle_sdk_promotions', JSON.stringify(option));
            window.themeisleSDKPromotions.showPromotion = false;
        };
        useEffect(() => {
            if (hasSkipped) {
                onSkip();
            }
        }, [hasSkipped]);


        if (hasSkipped) {
            return <BlockEdit {...props} />;
        }
        return (
            <>
                <BlockEdit {...props} />
                <InspectorControls>
                    <PanelBody>
                        <div 
                            style={{
                                padding: '10px',
                                backgroundColor: '#f0f6fc',
                                borderLeft: '4px solid #72aee6',
                                margin: '5px 0',
                                fontSize: '13px',
                                color: '#1e1e1e',
                                position: 'relative'
                            }}
                        >
                            <div dangerouslySetInnerHTML={{ __html: window.themeisleSDKPromotions.labels.feedzy.editor_recommends }} />
                            <button
                                onClick={() => setHasSkipped(true)}
                                style={{
                                    position: 'absolute',
                                    top: '8px',
                                    right: '8px',
                                    cursor: 'pointer',
                                    background: 'none',
                                    border: 'none',
                                    padding: '2px',
                                    color: '#757575',
                                    fontSize: '16px'
                                }}
                            >
                                ×
                            </button>
                        </div>
                    </PanelBody>
                </InspectorControls>
            </>
        );
    };
}, 'withFeedzyNotice');

addFilter(
    'editor.BlockEdit',
    'feedzy/with-notice',
    withFeedzyNotice
);
