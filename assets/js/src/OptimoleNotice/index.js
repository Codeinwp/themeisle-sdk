import {useState} from '@wordpress/element';
import {Button} from '@wordpress/components';

import './style.scss';
import {activatePlugin, installPluginOrTheme} from "../common/utils";
import useSettings from "../common/useSettings";

export default function OptimoleNotice({stacked = false, noImage = false, type, onDismiss, onSuccess, initialStatus = null }) {
    const {
        assets,
        title,
        email: initialEmail,
        option,
        optionKey,
        labels,
        optimoleActivationUrl,
        optimoleApi,
        optimoleDash,
        nonce,
    } = window.themeisleSDKPromotions;
    const [showForm, setShowForm] = useState(false);
    const [email, setEmail] = useState(initialEmail || '');
    const [dismissed, setDismissed] = useState(false);
    const [progress, setProgress] = useState( initialStatus );
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
        await installPluginOrTheme('optimole-wp');

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

            if (onSuccess) {
                onSuccess();
            }

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
                    <p> {labels.all_set}</p>
                    <Button icon={'external'} isPrimary href={optimoleDash} target="_blank">
                        {labels.optimole.gotodash}
                    </Button>
                </div>
            );
        }

        if (progress) {
            return (
                <p className="om-progress">
                    <span className="dashicons dashicons-update spin"/>
                    <span>
                        {progress === 'installing' && labels.installing}
                        {progress === 'activating' && labels.activating}
                        {progress === 'connecting' && labels.connecting}
                        &hellip;
                    </span>
                </p>
            );
        }

        return (
            <>
                <span>Enter your email address to create & connect your account</span>
                <form onSubmit={submitForm}>
                    <input
                        defaultValue={email}
                        type="email"
                        onChange={updateEmail}
                        placeholder={labels.optimole.email_placeholder}
                    />

                    <Button isPrimary type="submit">
                        {labels.optimole.start_cta}
                    </Button>
                </form>
            </>
        );
    };

    const dismissButton = () => (
        <Button disabled={progress && progress !== 'done'} onClick={dismissNotice} isLink
                className="om-notice-dismiss">
            <span className="dashicons-no-alt dashicons"/>
            <span className="screen-reader-text">{labels.optimole.dismisscta}</span>
        </Button>
    );


    if (stacked) {
        return (
            <div className="ti-om-stack-wrap">
                <div className="om-stack-notice">
                    {dismissButton()}
                    <img src={assets + '/optimole-logo.svg'} alt="Optimole logo"/>

                    <h2>{labels.optimole.heading}</h2>

                    <p>
                        {(type === 'om-editor' || type === 'om-image-block') ?
                            labels.optimole.message1 :
                            labels.optimole.message2
                        }
                    </p>

                    {( !showForm && 'done' !== progress ) && (
                        <Button isPrimary onClick={toggleForm} className="cta">
                            {labels.optimole.gst}
                        </Button>
                    )}
                    {( showForm || 'done' === progress ) && form()}

                    <i>{title}</i>
                </div>
            </div>
        );
    }

    return (
        <>
            {dismissButton()}
            <div className="content">
                {!noImage && <img src={assets + '/optimole-logo.svg'} alt="Optimole logo"/>}

                <div>
                    <p>{title}</p>
                    <p className="description">{
                        type === 'om-media' ?
                            labels.optimole.message3 :
                            labels.optimole.message4
                    }</p>
                    {!showForm && (
                        <div className="actions">
                            <Button isPrimary onClick={toggleForm}>
                                {labels.optimole.gst}
                            </Button>
                            <Button isLink target="_blank" href="https://wordpress.org/plugins/optimole-wp">
                                <span className="dashicons dashicons-external"/>
                                <span> {labels.learnmore}</span>
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