import {useState} from '@wordpress/element';
import {Button} from '@wordpress/components';

import {activatePlugin, installPluginOrTheme} from "../../common/utils";

export default function ProductCard({product, slug}) {
    const {icon, name, description, status, premiumUrl, activationLink} = product;
    const {strings} = window.tiSDKAboutData;
    const {
        installNow,
        installed,
        notInstalled,
        active,
        activate,
        learnMore
    } = strings;

    const isPremium = !!premiumUrl;
    const [productStatus, setProductStatus] = useState(status);
    const [loading, setLoading] = useState(false);

    const runInstall = async () => {
        setLoading(true);
        await installPluginOrTheme(slug, slug === 'neve').then((res) => {
            if (res.success) {
                setProductStatus('installed');
            }
        });
        setLoading(false);
    }

    const runActivate = async () => {
        setLoading(true);
        await activatePlugin(activationLink).then((res) => {
            if (res.success) {
                setProductStatus('active');
            }
        });
        setLoading(false);
    }

    return (<div className="product-card">
        <div className="header">
            {icon && <img src={icon} alt={name}/>}
            <h2>{name}</h2>
        </div>
        <div className="body">
            <p dangerouslySetInnerHTML={{__html: description}}/>
        </div>
        <div className="footer">
            <p>Status:
                {" "}
                <span className={productStatus}>
                    {productStatus === 'installed' && installed}
                    {productStatus === 'not-installed' && notInstalled}
                    {productStatus === 'active' && active}
                </span>
            </p>
            {productStatus !== 'active' && !loading && (
                <>
                    {productStatus === 'not-installed' && isPremium &&
                        <Button isLink icon={'external'} href={premiumUrl} target="_blank">{learnMore}</Button>}
                    {productStatus === 'not-installed' && !isPremium &&
                        <Button isPrimary onClick={runInstall}>{installNow}</Button>}
                    {productStatus === 'installed' && <Button isSecondary onClick={runActivate}>{activate}</Button>}
                </>
            )}

            {loading && <span className="dashicons dashicons-update spin"/>}
        </div>
    </div>)
}