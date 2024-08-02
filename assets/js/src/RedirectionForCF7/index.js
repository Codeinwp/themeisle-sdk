import {useState} from '@wordpress/element';
import {Button} from '@wordpress/components';

import {activatePlugin, installPluginOrTheme} from "../common/utils";
import useSettings from "../common/useSettings";

export default function RedirectionForCF7({type, onDismiss, initialStatus = null}) {
  const {
    title,
    option,
    optionKey,
    labels,
    rfCF7ActivationUrl,
    cf7Dash,
  } = window.themeisleSDKPromotions;

  const [showStatus, setShowStatus] = useState(false);
  const [dismissed, setDismissed] = useState(false);
  const [progress, setProgress] = useState(initialStatus);
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
          <div className="done">
            <p> {labels.redirectionCF7.all_set}</p>
            <Button icon="external" variant="primary" href={cf7Dash} target="_blank">
              {labels.redirectionCF7.gotodash}
            </Button>
          </div>
      );
    }

    if (progress) {
      return (
          <p className="om-progress">
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

  return (
      <>
        <Button disabled={progress && progress !== 'done'}
                onClick={dismissNotice} isLink
                className="om-notice-dismiss">
          <span className="dashicons-no-alt dashicons"/>
          <span className="screen-reader-text">{labels.redirectionCF7.dismisscta}</span>
        </Button>
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