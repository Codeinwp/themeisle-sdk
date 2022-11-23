import {__} from '@wordpress/i18n';
import {useState} from '@wordpress/element';
import {Button} from '@wordpress/components';

import './style.scss';
import {activatePlugin, installPlugin} from "../common/utils";
import useSettings from "../common/useSettings";

export default function OptimoleNotice({stacked = false, noImage = false, type, onDismiss}) {
    const {
        assets,
        title,
        email: initialEmail,
        option,
        optionKey,
        optimoleActivationUrl,
        optimoleApi,
        optimoleDash,
        nonce,
    } = window.themeisleSDKPromotions;
    const [showForm, setShowForm] = useState(false);
    const [email, setEmail] = useState(initialEmail || '');
    const [dismissed, setDismissed] = useState(false);
    const [progress, setProgress] = useState(null);
    const [getOption, updateOption] = useSettings();


    const dismissNotice = async () => {
        setDismissed(true);
        const newValue = {...option};
        newValue[type] = new Date().getTime() / 1000 | 0;
        window.themeisleSDKPromotions.option = newValue;
        await updateOption(optionKey, JSON.stringify(newValue));

        if (onDismiss) {
            onDismiss();
        }
    };

    const toggleForm = () => {
        setShowForm(!showForm);
    }
    const updateEmail = (e) => {
        setEmail(e.target.value);
    }
    const submitForm = async (e) => {
        e.preventDefault();
        setProgress('installing');
        await installPlugin('optimole-wp');

        setProgress('activating');
        await activatePlugin(optimoleActivationUrl);

        updateOption('themeisle_sdk_promotions_optimole_installed', !Boolean(getOption('themeisle_sdk_promotions_optimole_installed')));

        setProgress('connecting');
        try {
            await fetch(optimoleApi, {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': nonce,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    'email': email,
                }),
            });

            setProgress('done');
        } catch (e) {
            setProgress('done');
        }

    }

    if (dismissed) {
        return null;
    }

    const form = () => {
        if (progress === 'done') {
            return (
                <div className={"done"}>
                    <p>{__('Awesome! You are all set!', 'textdomain')}</p>
                    <Button icon={'external'} isPrimary href={optimoleDash} target="_blank">
                        {__('Go to Optimole dashboard', 'textdomain')}
                    </Button>
                </div>
            );
        }

        if (progress) {
            return (
                <p className="om-progress">
                    <span className="dashicons dashicons-update spin"/>
                    <span>
                        {progress === 'installing' && __('Installing', 'textdomain')}
                        {progress === 'activating' && __('Activating', 'textdomain')}
                        {progress === 'connecting' && __('Connecting to API', 'textdomain')}
                        &hellip;
                    </span>
                </p>
            );
        }

        return (
            <>
                <span>{__('Enter your email address to create & connect your account', 'textdomain')}</span>
                <form onSubmit={submitForm}>
                    <input
                        defaultValue={email}
                        type="email"
                        onChange={updateEmail}
                        placeholder={__('Email address', 'textdomain')}
                    />

                    <Button isPrimary type="submit">
                        {__('Start using Optimole', 'textdomain')}
                    </Button>
                </form>
            </>
        );
    };

    const dismissButton = () => (
        <Button disabled={progress && progress !== 'done'} onClick={dismissNotice} isLink
                className="om-notice-dismiss">
            <span className="dashicons-no-alt dashicons"/>
            <span className="screen-reader-text">Dismiss this notice.</span>
        </Button>
    );


    if (stacked) {
        return (
            <div className="ti-om-stack-wrap">
                <div className="om-stack-notice">
                    {dismissButton()}
                    <img src={assets + '/optimole-logo.svg'} alt={__('Optimole logo', 'textdomain')}/>

                    <h2>{__('Get more with Optimole', 'textdomain')}</h2>

                    <p>
                        {type === 'om-editor' ?
                            __('Increase this page speed and SEO ranking by optimizing images with Optimole.', 'textdomain') :
                            __('Leverage Optimole\'s full integration with Elementor to automatically lazyload, resize, compress to AVIF/WebP and deliver from 400 locations around the globe!', 'textdomain')
                        }
                    </p>

                    {!showForm && (
                        <Button isPrimary onClick={toggleForm} className="cta">
                            {__('Get Started Free', 'textdomain')}
                        </Button>
                    )}
                    {showForm && form()}

                    <i>{title}</i>
                </div>
            </div>
        );
    }

    return (
        <>
            {dismissButton()}
            <div className="content">
                {!noImage && <img src={assets + '/optimole-logo.svg'} alt={__('Optimole logo', 'textdomain')}/>}

                <div>
                    <p>{title}</p>
                    <p className="description">{
                        type === 'om-media' ?
                            __('Save your server space by storing images to Optimole and deliver them optimized from 400 locations around the globe. Unlimited images, Unlimited traffic.', 'textdomain') :
                            __('Optimize, store and deliver this image with 80% less size while looking just as great, using Optimole.', 'textdomain')
                    }</p>
                    {!showForm && (
                        <div className="actions">
                            <Button isPrimary onClick={toggleForm}>
                                {__('Get Started Free', 'textdomain')}
                            </Button>
                            <Button isLink target="_blank" href="https://wordpress.org/plugins/optimole-wp">
                                {__('Learn more', 'textdomain')}
                            </Button>
                        </div>
                    )}
                    {showForm && (
                        <div className="form-wrap">
                            {form()}
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}