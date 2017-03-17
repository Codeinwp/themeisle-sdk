import { useState } from '@wordpress/element';
import { Button } from '@wordpress/components';
import useSettings from "./useSettings";
import { activatePlugin, installPluginOrTheme } from './utils';

const PromotionNotice = ({
    title,
    option,
    optionKey,
    labels,
    pluginSlug,
    activationUrl,
    dashboardUrl,
    learnMoreUrl,
    labelKey,
    optionInstallKey,
    installedOptionKey,
    onDismiss = () => {}
}) => {
    const [ dismissed, setDismissed ] = useState( false );
    const [ progress, setProgress ] = useState( null );
    const [ getOption, updateOption ] = useSettings();

    const dismissNotice = async () => {
        setDismissed( true );
        const newValue = { ...option };
        newValue[optionInstallKey] = new Date().getTime() / 1000 | 0;
        if (window.themeisleSDKPromotions) {
            window.themeisleSDKPromotions.option = newValue;
        }
        await updateOption( optionKey, JSON.stringify( newValue ) );
        if ( onDismiss ) {
            onDismiss();
        }
    };

    const installPluginRequest = async e => {
        e.preventDefault();
        setProgress( 'installing' );
        await installPluginOrTheme( pluginSlug );
        setProgress( 'activating' );
        await activatePlugin( activationUrl );
        updateOption( installedOptionKey, ! Boolean( getOption( installedOptionKey ) ) );
        setProgress('done');
    };

    if ( dismissed ) {
      return null;
    }

    const installPluginRequestStatus = () => {
      return progress === 'done' ? (
          <div className="done">
            <p>{ labels.all_set }</p>
            <Button
                icon="external"
                variant="primary"
                href={ dashboardUrl }
                target="_blank"
            >
              { labels[labelKey].gotodash }
            </Button>
          </div>
      ) : (
          <p className="om-progress">
            <span className="dashicons dashicons-update spin"/>
            <span>
              { progress === 'installing' && labels.installing }
              { progress === 'activating' && labels.activating }
              &hellip;
            </span>
          </p>
      );
    };

    const actionButtons = (
        <div className="actions">
            <Button
                variant="primary"
                onClick={ installPluginRequest }
            >
                { labels[labelKey].install }
            </Button>
            <Button
                variant="link"
                target="_blank"
                href={learnMoreUrl}
            >
                <span className="dashicons dashicons-external"/>
                <span>{ labels.learnmore }</span>
            </Button>
        </div>
    );

    return (
        <>
            <Button
                disabled={ progress && progress !== 'done' }
                onClick={ dismissNotice }
                variant="link"
                className="om-notice-dismiss"
            >
                <span className="dashicons-no-alt dashicons"/>
                <span className="screen-reader-text">{ labels[labelKey].dismisscta }</span>
            </Button>
            <div className="content">
                <div>
                    <p>{ title }</p>
                    <p className="description">{labels[labelKey].message}</p>
                    { progress ? installPluginRequestStatus() : actionButtons }
                </div>
            </div>
        </>
    );
};

export default PromotionNotice;
