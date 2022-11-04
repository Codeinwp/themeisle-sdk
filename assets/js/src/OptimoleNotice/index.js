import {__} from '@wordpress/i18n';
import {useState} from '@wordpress/element';
import {Button} from '@wordpress/components';

import './style.scss';

export default function OptimoleNotice({stacked = false, noImage = false, type }) {
    const [showForm, setShowForm] = useState(false);
    const {logo, title, email: initialEmail} = tiSdkData;
    const [email, setEmail] = useState(initialEmail || '');



    const dismissNotice = () => {

    };

    const toggleForm = () => {
        setShowForm(!showForm);
    }
    const updateEmail = (e) => {
        setEmail(e.target.value);
    }
    const submitForm = (e) => {
        e.preventDefault();
    }

    const form = () => (
        <form onSubmit={submitForm}>
            <input
                defaultValue={email}
                type="email"
                onChange={updateEmail}
                placeholder={__('Email address', 'textdomain')}
            />

            <Button isPrimary type="submit">{__('Start using Optimole', 'textdomain')}</Button>
        </form>
    );

    if (stacked) {
        return (
            <div className="ti-om-stack-wrap">
                <div className="om-stack-notice">
                    <Button onClick={dismissNotice} isLink className="om-notice-dismiss">
                        <span className="dashicons-no-alt dashicons"/>
                        <span className="screen-reader-text">Dismiss this notice.</span>
                    </Button>
                    <img src={logo} alt={__('Optimole logo', 'textdomain')}/>

                    <h2>{__('Get more with Optimole', 'textdomain')}</h2>
                    <p>{__('Optimize, store and deliver this image with 80% less size while looking just as great, using Optimole.', 'textdomain')}</p>

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
            <Button onClick={dismissNotice} isLink className={"om-notice-dismiss"}>
                <span className="dashicons-no-alt dashicons"/>
                <span className="screen-reader-text">Dismiss this notice.</span>
            </Button>

            <div className="content">
                {!noImage && <img src={logo} alt={__('Optimole logo', 'textdomain')}/>}

                <div>
                    <p>{title}</p>
                    <p className="description">{__('Save your server space by storing images to Optimole and deliver them optimized from 400 locations around the globe. Unlimited images, Unlimited traffic.', 'textdomain')}</p>
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
                            <span>{__('Enter your email address to create & connect your account', 'textdomain')}</span>

                            {form()}
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}