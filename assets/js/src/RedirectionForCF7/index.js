import {useState} from '@wordpress/element';
import {Button} from '@wordpress/components';

import './style.scss';
import {activatePlugin, installPluginOrTheme} from "../common/utils";
import useSettings from "../common/useSettings";

export default function RedirectionForCF7({stacked = false, noImage = false, type, onDismiss, onSuccess, initialStatus = null }) {
    const {
        assets,
        title,
        email: initialEmail,
        option,
        optionKey,
        labels,
        rfCF7ActivationUrl,
        cf7Dash,
    } = window.themeisleSDKPromotions;

    const [showStatus, setShowStatus] = useState(false);
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

    const installPluginRequest = async (e) => {
        e.preventDefault();
        setShowStatus(true);
        setProgress('installing');
        await installPluginOrTheme('wpcf7-redirect');

        setProgress('activating');
        await activatePlugin(rfCF7ActivationUrl);

        updateOption('themeisle_sdk_promotions_redirection_cf7_installed', !Boolean(getOption('themeisle_sdk_promotions_redirection_cf7_installed')));

        setProgress('done');

    }

    if (dismissed) {
        return null;
    }

    const installPluginRequestStatus = () => {
        if (progress === 'done') {
            return (
                <div className={"done"}>
                    <p> {labels.redirectionCF7.all_set}</p>
                    <Button icon={'external'} isPrimary href={cf7Dash} target="_blank">
                        {labels.redirectionCF7.gotodash}
                    </Button>
                </div>
            );
        }

        if (progress) {
            return (
                <p className="rf-cf7-progress">
                    <span className="dashicons dashicons-update spin"/>
                    <span>
                        {progress === 'installing' && labels.redirectionCF7.installing}
                        {progress === 'activating' && labels.redirectionCF7.activating}
                        &hellip;
                    </span>
                </p>
            );
        }
    };

    const dismissButton = () => (
        <Button disabled={progress && progress !== 'done'} onClick={dismissNotice} isLink
                className="rf-cf7-notice-dismiss">
            <span className="dashicons-no-alt dashicons"/>
            <span className="screen-reader-text">{labels.redirectionCF7.dismisscta}</span>
        </Button>
    );


    if (stacked) {
        return (
            <div className="ti-rf-cf7-stack-wrap">
                <div className="rf-cf7-stack-notice">
                    {dismissButton()}
                    <h2>{labels.redirectionCF7.heading}</h2>

                    <p>{labels.redirectionCF7.message}</p>

                    {( !showStatus && 'done' !== progress ) && (
                        <Button isPrimary onClick={installPluginRequest} className="cta">
                            {labels.redirectionCF7.gst}
                        </Button>
                    )}
                    {( showStatus || 'done' === progress ) && installPluginRequestStatus()}

                    <i>{title}</i>
                </div>
            </div>
        );
    }

    return (
        <>
            {dismissButton()}
            <div className="content">
                <div>
                    <p>{title}</p>
                    <p className="description">{labels.redirectionCF7.message}</p>
                    {!showStatus && (
                        <div className="actions">
                            <Button isPrimary onClick={installPluginRequest}>
                                {labels.redirectionCF7.gst}
                            </Button>
                            <Button isLink target="_blank" href="https://wordpress.org/plugins/wpcf7-redirect/">
                                <span className="dashicons dashicons-external"/>
                                <span> {labels.redirectionCF7.learnmore}</span>
                            </Button>
                        </div>
                    )}
                    {showStatus && (
                        <div className="form-wrap">
                            {installPluginRequestStatus()}
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}