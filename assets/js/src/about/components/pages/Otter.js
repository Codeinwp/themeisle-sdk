import {Button} from '@wordpress/components';
import {useState} from '@wordpress/element';
import {activatePlugin, installPluginOrTheme} from "../../../common/utils";
export default function Otter( { page = {} } ) {
    const { products } = window.tiSDKAboutData;
    const { strings, plugin } = page;
    const product = page && page.product ? page.product : '';
    const icon = product && products[product] && products[product].icon ? products[product].icon : null;
    const [ testimonial, setTestimonial ] = useState( strings.testimonials.users[0] );

    const [productStatus, setProductStatus] = useState(plugin.status);
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
        window.location.href = plugin.activationLink;
    }

    const toggleTestimonial = ( index ) => {
        const user = strings.testimonials.users[index];
        const testimonial = document.getElementById( 'ts_' + index );
        testimonial.scrollIntoView( { behavior: 'smooth' } );
        setTestimonial( user );
    }

    return (
        <>
          <div className="hero">
              {icon && <img className="logo" src={icon} alt={page.name || ''}/>}
              <span className="label">Neve + Otter = New Possibilities 🤝</span>
              <h1>{ strings.heading }</h1>
              <p>{ strings.text }</p>
              {productStatus === 'not-installed' &&
                  <Button variant="primary" className="otter-button" onClick={runInstall}>{strings.buttons.install_otter_free}</Button>}
              {productStatus === 'installed' &&
                  <Button variant="primary" className="otter-button" onClick={runActivate}>{strings.buttons.install_otter_free}</Button>}
          </div>
          <div className="col-3-highlights">
              <div className="col">
                  <h3>{ strings.features.advancedTitle }</h3>
                  <p>{ strings.features.advancedDesc }</p>
              </div>
              <div className="col">
                  <h3>{ strings.features.fastTitle }</h3>
                  <p>{ strings.features.fastDesc }</p>
              </div>
              <div className="col">
                  <h3>{ strings.features.mobileTitle }</h3>
                  <p>{ strings.features.mobileDesc }</p>
              </div>
          </div>
          <div className="col-2-highlights">
              <div className="col">
                  <img src={ strings.details.s1Image } alt={ strings.details.s1Title } />
              </div>
              <div className="col">
                  <h2>{ strings.details.s1Title }</h2>
                  <p>{ strings.details.s1Text }</p>
              </div>
          </div>
          <div className="col-2-highlights">
              <div className="col">
                  <h2>{ strings.details.s2Title }</h2>
                  <p>{ strings.details.s2Text }</p>
              </div>
              <div className="col">
                  <img src={ strings.details.s2Image } alt={ strings.details.s1Title } />
              </div>
          </div>
          <div className="col-2-highlights">
              <div className="col">
                  <img src={ strings.details.s3Image } alt={ strings.details.s1Title } />
              </div>
              <div className="col">
                  <h2>{ strings.details.s3Title }</h2>
                  <p>{ strings.details.s3Text }</p>
              </div>
          </div>
          <div className="col-2-highlights" style={{backgroundColor: '#F7F7F7', borderBottom: 'none', borderBottomRightRadius: '8px', borderBottomLeftRadius: '8px'}}>
              <div className="col">
                  <h2>{strings.testimonials.heading}</h2>
                  <div className="button-row">
                      {productStatus === 'not-installed' &&
                          <Button variant="primary" className="otter-button" onClick={runInstall}>{strings.buttons.install_now}</Button>}
                      {productStatus === 'installed' &&
                          <Button variant="primary" className="otter-button" onClick={runActivate}>{strings.buttons.install_now}</Button>}
                      <Button variant="secondary" className="otter-button">{strings.buttons.learn_more}</Button>
                  </div>
              </div>
              <div className="col">
                  <div className="testimonials">
                      <ul id="testimonial-container" className="testimonial-container">
                          {strings.testimonials.users.map((user, index) => (
                              <li className="testimonial" id={'ts_' + index} key={'ts_' + index}>
                                  <p>"{user.text}"</p>
                                  <img src={user.avatar} alt={user.name} />
                                  <h3>{user.name}</h3>
                              </li>
                          ))}
                      </ul>
                      <div className="testimonial-nav">
                          {strings.testimonials.users.map((user, index) => (
                              <Button
                                  className={'testimonial-button' + ( user.name === testimonial.name ? ' active' : '' ) }
                                  key={'button_' + index}
                                  onClick={() => toggleTestimonial(index)}
                              />
                          ))}
                      </div>
                  </div>
              </div>
          </div>
        </>
    ) ;
};
