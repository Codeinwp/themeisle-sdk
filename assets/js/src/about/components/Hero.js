import {useState} from '@wordpress/element';
import {Button} from '@wordpress/components';

export default function Hero() {
    const {
        strings, teamImage, homeUrl, pageSlug
    } = window.tiSDKAboutData;

    const {
        heroHeader, heroTextFirst, heroTextSecond, teamImageCaption, newsHeading, emailPlaceholder, signMeUp,
    } = strings;

    const [email, setEmail] = useState('');
    const [loading, setLoading] = useState(false);
    const [hasSubscribed, setHasSubscribed] = useState(false);

    const services = [
        {
            title: 'Website Design',
            subtitle: 'Built for your business',
            href: 'https://themeisle.com/wordpress-website-design/',
            icon: 'dashicons-admin-site'
        },
        {
            title: 'Support',
            subtitle: 'On-demand expert help',
            href: 'https://themeisle.com/wordpress-support/',
            icon: 'dashicons-sos'
        },
        {
            title: 'Speed Optimization',
            subtitle: 'Core Web Vitals boost',
            href: 'https://themeisle.com/wordpress-speed-optimization/',
            icon: 'dashicons-dashboard'
        },
        {
            title: 'SEO Foundation',
            subtitle: 'Rank & get found',
            href: 'https://themeisle.com/wordpress-seo-foundation/',
            icon: 'dashicons-search'
        },
        {
            title: 'Maintenance',
            subtitle: 'Updates, backups, security',
            href: 'https://themeisle.com/wordpress-maintenance/',
            icon: 'dashicons-shield'
        },
        {
            title: 'Hacked Site Repair',
            subtitle: 'Malware removed fast',
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

        <section className="services-card" aria-label="Themeisle services">
            <div className="services-header">
                <div>
                    <div className="trustpilot" aria-label="Rated excellent on Trustpilot">
                        <span className="trustpilot-rated">Rated</span>
                        <span className="trustpilot-score">4.8</span>
                        <span className="trustpilot-stars" aria-hidden="true">★★★★★</span>
                        <span className="trustpilot-on">on</span>
                        <span className="trustpilot-brand">Trustpilot</span>
                    </div>
                    <h2>Expert WordPress services from the Themeisle team</h2>
                    <p>Done for you by the same people who build your plugins and themes.</p>
                </div>
                <Button
                    isPrimary
                    href="https://themeisle.com/services/"
                    target="_blank"
                    rel="noopener noreferrer"
                    className="services-cta"
                >
                    Explore all services
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