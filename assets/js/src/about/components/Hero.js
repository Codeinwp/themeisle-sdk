import {useState} from '@wordpress/element';
import {Button} from '@wordpress/components';

export default function Hero() {
    const {
        strings, teamImage, homeUrl, pageSlug
    } = window.tiSDKAboutData;

    const {
        heroHeader, heroTextFirst, heroTextSecond, teamImageCaption, newsHeading, emailPlaceholder, signMeUp, services: servicesStrings,
    } = strings;

    const [email, setEmail] = useState('');
    const [loading, setLoading] = useState(false);
    const [hasSubscribed, setHasSubscribed] = useState(false);

    const services = [
        {
            title: servicesStrings.items.websiteDesign.title,
            subtitle: servicesStrings.items.websiteDesign.subtitle,
            href: 'https://themeisle.com/wordpress-website-design/',
            icon: 'dashicons-admin-site'
        },
        {
            title: servicesStrings.items.support.title,
            subtitle: servicesStrings.items.support.subtitle,
            href: 'https://themeisle.com/wordpress-support/',
            icon: 'dashicons-sos'
        },
        {
            title: servicesStrings.items.speed.title,
            subtitle: servicesStrings.items.speed.subtitle,
            href: 'https://themeisle.com/wordpress-speed-optimization/',
            icon: 'dashicons-dashboard'
        },
        {
            title: servicesStrings.items.seo.title,
            subtitle: servicesStrings.items.seo.subtitle,
            href: 'https://themeisle.com/wordpress-seo-foundation/',
            icon: 'dashicons-search'
        },
        {
            title: servicesStrings.items.maintenance.title,
            subtitle: servicesStrings.items.maintenance.subtitle,
            href: 'https://themeisle.com/wordpress-maintenance/',
            icon: 'dashicons-shield'
        },
        {
            title: servicesStrings.items.hackedSite.title,
            subtitle: servicesStrings.items.hackedSite.subtitle,
            href: 'https://themeisle.com/wordpress-hacked-site-repair/',
            icon: 'dashicons-lock'
        }
    ];

    const submit = (e) => {
        e.preventDefault();
        setLoading(true);
        fetch('https://api.themeisle.com/tracking/subscribe', {
            method: 'POST', headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json, */*;q=0.1',
                'Cache-Control': 'no-cache'
            }, body: JSON.stringify({
                slug: 'about-us',
                site: homeUrl,
                from: pageSlug,
                email
            })
        })
            .then(r => r.json())
            .then((response) => {
                setLoading(false);

                if ( 'success' === response.code ) {
                    setHasSubscribed(true);
                }
            })?.catch((error) => {
                setLoading(false);
            });
    };


    const updateEmail = (e) => {
        setEmail(e.target.value);
    }

    return (<div className="container">
        <div className="story-card">
            <div className="body">
                <div>
                    <h2>{heroHeader}</h2>
                    <p>{heroTextFirst}</p>
                    <p>{heroTextSecond}</p>
                </div>

                <figure>
                    <img src={teamImage} alt={teamImageCaption}/>
                    <figcaption>{teamImageCaption}</figcaption>
                </figure>
            </div>

            <div className="footer">
                <h2>
                    {newsHeading}
                </h2>
                <form onSubmit={submit}>
                    <input
                        disabled={loading || hasSubscribed}
                        type="email"
                        value={email}
                        onChange={updateEmail}
                        placeholder={emailPlaceholder}
                    />
                    {!loading && ! hasSubscribed && <Button isPrimary type="submit">{signMeUp}</Button>}
                    {loading && <span className="dashicons dashicons-update spin"/>}
                    {hasSubscribed && <span className="dashicons dashicons-yes-alt"/>}
                </form>
            </div>
        </div>

        <section className="services-card" aria-label={servicesStrings.ariaLabel}>
            <div className="services-header">
                <div>
                    <div className="trustpilot" aria-label={servicesStrings.trustpilotLabel}>
                        <span className="trustpilot-rated">{servicesStrings.trustpilotRated}</span>
                        <span className="trustpilot-score">4.8</span>
                        <span className="trustpilot-stars" aria-hidden="true">★★★★★</span>
                        <span className="trustpilot-on">{servicesStrings.trustpilotOn}</span>
                        <span className="trustpilot-brand">{servicesStrings.trustpilotBrand}</span>
                    </div>
                    <h2>{servicesStrings.heading}</h2>
                    <p>{servicesStrings.description}</p>
                </div>
                <Button
                    isPrimary
                    href="https://themeisle.com/services/"
                    target="_blank"
                    rel="noopener noreferrer"
                    className="services-cta"
                >
                    {servicesStrings.cta}
                </Button>
            </div>

            <div className="services-grid">
                {services.map((service) => (
                    <a
                        key={service.title}
                        href={service.href}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="service-item"
                    >
                        <div className="service-icon-wrap">
                            <span className={`dashicons ${service.icon}`} aria-hidden="true"/>
                        </div>
                        <div className="service-content">
                            <h3>{service.title}</h3>
                            <p>{service.subtitle}</p>
                        </div>
                        <span className="service-arrow" aria-hidden="true">→</span>
                    </a>
                ))}
            </div>
        </section>
    </div>);
}