import {useState} from '@wordpress/element';
import {Button} from '@wordpress/components';

import './style.scss';
import {activatePlugin, installPluginOrTheme} from "../common/utils";
import useSettings from "../common/useSettings";

export default function OptimoleNotice({stacked = false, type, onDismiss, onSuccess, initialStatus = null}) {
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

  const installAndActivate = async (e) => {
    e.preventDefault();
    setProgress('installing');
    await installPluginOrTheme('optimole-wp');

    setProgress('activating');
    await activatePlugin(optimoleActivationUrl);

    updateOption('themeisle_sdk_promotions_optimole_installed', true).then(() => {
      setProgress('done');
    });
  }

  if (dismissed) {
    return null;
  }

  const renderProgress = () => {
    if (progress === 'done') {
      return (
          <div className="done">
            <p> {labels.all_set}</p>
            <Button icon="external" isPrimary href={optimoleDash} target="_blank">
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
              &hellip;
            </span>
          </p>
      );
    }

    return null;
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
        <div className="ti-om-stack-wrap ti-sdk-om-notice">
          <div className="om-stack-notice">
            {dismissButton()}

            <i>{title}</i>

            <p>
              {(type === 'om-editor' || type === 'om-image-block') ?
                  labels.optimole.message1 :
                  labels.optimole.message2
              }
            </p>

            {(!progress) && (
                <>
                  <Button isPrimary onClick={installAndActivate} className="cta">
                    {labels.optimole.installOptimole}
                  </Button>
                  <Button isLink target="_blank" href="https://wordpress.org/plugins/optimole-wp">
                    <span className="dashicons dashicons-external"/>
                    <span> {labels.learnmore}</span>
                  </Button>
                </>
            )}

            {progress && renderProgress()}

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
            <p className="description">{
              type === 'om-media' ?
                  labels.optimole.message3 :
                  labels.optimole.message4
            }</p>
            {!progress && (
                <div className="actions">
                  <Button isPrimary onClick={installAndActivate}>
                    {labels.optimole.installOptimole}
                  </Button>
                  <Button isLink target="_blank" href="https://wordpress.org/plugins/optimole-wp">
                    <span className="dashicons dashicons-external"/>
                    <span> {labels.learnmore}</span>
                  </Button>
                </div>
            )}
            {renderProgress()}
          </div>
        </div>
      </>
  );
}