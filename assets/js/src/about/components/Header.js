export default  function Header() {
    const {currentProduct, logoUrl} = window.tiSDKAboutData;

    return (
        <div>
            <img src={logoUrl} alt=""/>
            <h1>{currentProduct.name}</h1>
        </div>
    );
}