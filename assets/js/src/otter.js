import {__} from '@wordpress/i18n';
import {InspectorControls} from '@wordpress/block-editor';
import {Button, PanelBody} from '@wordpress/components';
import {createHigherOrderComponent} from '@wordpress/compose';
import {select} from '@wordpress/data';
import {Fragment, useEffect, useState} from '@wordpress/element';
import {addFilter} from '@wordpress/hooks';

import useSettings from './common/useSettings.js';
import {installPlugin, activatePlugin} from './common/utils.js';

const style = {
    button: {
        display: 'flex',
        justifyContent: 'center',
        width: '100%'
    },
    image: {
        padding: '20px 0'
    },
    skip: {
        container: {
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'center'
        },
        button: {
            fontSize: '9px'
        },
        poweredby: {
            fontSize: '9px',
            textTransform: 'uppercase'
        }
    }
};

const upsells = {
    'blocks-css': {
        title: __('Custom CSS', 'textdomain'),
        description: __('Enable Otter Blocks to add Custom CSS for this block.'),
        image: 'css.jpg'
    },
    'blocks-animation': {
        title: __('Animations', 'textdomain'),
        description: __('Enable Otter Blocks to add Animations for this block.'),
        image: 'animation.jpg'
    },
    'blocks-conditions': {
        title: __('Visibility Conditions', 'textdomain'),
        description: __('Enable Otter Blocks to add Visibility Conditions for this block.'),
        image: 'conditions.jpg'
    }
};

const Footer = ({onClick}) => {
    return (
        <div style={style.skip.container}>
            <Button
                style={style.skip.button}
                variant="tertiary"
                onClick={onClick}
            >
                {__('Skip for now')}
            </Button>
            <span style={style.skip.poweredby}>{__('Recommended by ') + window.themeisleSDKPromotions.product}</span>
        </div>
    );
};

const withInspectorControls = createHigherOrderComponent((BlockEdit) => {
    return (props) => {
        if (props.isSelected && Boolean(window.themeisleSDKPromotions.showPromotion)) {

            const [isLoading, setLoading] = useState(false);
            const [installStatus, setInstallStatus] = useState('default');
            const [hasSkipped, setHasSkipped] = useState(false);

            const [getOption, updateOption, status] = useSettings();

            const install = async () => {
                setLoading(true);
                await installPlugin('otter-blocks');
                updateOption('themeisle_sdk_promotions_otter_installed', !Boolean(getOption('themeisle_sdk_promotions_otter_installed')));
                await activatePlugin(window.themeisleSDKPromotions.otterActivationUrl);
                setLoading(false);
                setInstallStatus('installed');
            };

            const Install = () => {
                if ('installed' === installStatus) {
                    return <p><strong>{__('Awesome! Refresh the page to see Otter Blocks in action.')}</strong></p>;
                }

                return (
                    <Button
                        variant="secondary"
                        onClick={install}
                        isBusy={isLoading}
                        style={style.button}
                    >
                        {__('Install & Activate Otter Blocks')}
                    </Button>
                );
            };

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
                <Fragment>
                    <BlockEdit {...props} />

                    <InspectorControls>
                        {Object.keys(upsells).map(key => {
                            if (key === window.themeisleSDKPromotions.showPromotion) {
                                const upsell = upsells[key];

                                return (
                                    <PanelBody
                                        key={key}
                                        title={upsell.title}
                                        initialOpen={false}
                                    >
                                        <p>{upsell.description}</p>

                                        <Install/>

                                        <img style={style.image}
                                             src={window.themeisleSDKPromotions.assets + upsell.image}/>

                                        <Footer onClick={() => setHasSkipped(true)}/>
                                    </PanelBody>
                                );
                            }
                        })}
                    </InspectorControls>
                </Fragment>
            );
        }

        return <BlockEdit {...props} />;
    };
}, 'withInspectorControl');

if (!select('core/edit-site')) {
    addFilter('editor.BlockEdit', 'themeisle-sdk/with-inspector-controls', withInspectorControls);
}
