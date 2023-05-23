export default function Header() {
    const {currentProduct, logoUrl, strings, links} = window.tiSDKAboutData;

    return (
        <div>
            <div className="head">
                <div className="container">
                    <img src={logoUrl} alt={currentProduct.name}/>
                    <p>by <a href="https://themeisle.com">Themeisle</a></p>
                </div>
            </div>
            <div className="container">
                <ul className="nav">
                    <li>
                        <a href={window.location}>{strings.aboutUs}</a>
                    </li>

                    {links.map((link, index) => (
                        <li key={index}>
                            <a href={link.url}>
                                {link.text}
                            </a>
                        </li>
                    ))}
                </ul>
            </div>
        </div>
    );
}