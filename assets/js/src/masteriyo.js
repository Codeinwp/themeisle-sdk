import { createRoot, useState } from '@wordpress/element';
import { Button } from '@wordpress/components';
import useSettings from "./common/useSettings";
import { activatePlugin, installPluginOrTheme } from './common/utils';

const MasteriyoNotice = ({
    onDismiss = () => {}
}) => {
    const {
        title,
        option,
        optionKey,
        labels,
        masteriyoActivationUrl,
        masteriyoDash,
    } = window.themeisleSDKPromotions;

    const [ dismissed, setDismissed ] = useState( false );
    const [ progress, setProgress ] = useState( null );
    const [ getOption, updateOption ] = useSettings();

    const dismissNotice = async () => {
        setDismissed( true );
        const newValue = { ...option };
        newValue['masteriyo-plugins-install'] = new Date().getTime() / 1000 | 0;
        window.themeisleSDKPromotions.option = newValue;
        await updateOption( optionKey, JSON.stringify( newValue ) );

        if ( onDismiss ) {
            onDismiss();
        }
    };

    const installPluginRequest = async e => {
        e.preventDefault();
        setProgress( 'installing' );
        await installPluginOrTheme( 'learning-management-system' );

        setProgress( 'activating' );
        await activatePlugin( masteriyoActivationUrl );

        updateOption( 'themeisle_sdk_promotions_masteriyo_installed', ! Boolean( getOption( 'themeisle_sdk_promotions_masteriyo_installed' ) ) );
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
                href={ masteriyoDash }
                target="_blank"
            >
              { labels.masteriyo.gotodash }
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
                { labels.masteriyo.install }
            </Button>

            <Button
                variant="link"
                target="_blank"
                href="https://wordpress.org/plugins/learning-management-system/"
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
                <span className="screen-reader-text">{ labels.masteriyo.dismisscta }</span>
            </Button>

            <div className="content">
                <div>
                    <p>{ title }</p>
                    <p className="description">{labels.masteriyo.message}</p>
                    { progress ? installPluginRequestStatus() : actionButtons }
                </div>
            </div>
        </>
    );
};

const renderMasteriyoNotice = () => {
    if ( window.themeisleSDKPromotions.option['masteriyo-plugins-install']) {
        return;
    }

    const root = document.querySelector( '#ti-masteriyo-notice' );

    if ( ! root ) {
        return;
    }

    createRoot( root ).render(
        <MasteriyoNotice
            onDismiss={() => {
                root.style.display = 'none';
            }}
        />
    );
};

renderMasteriyoNotice();
