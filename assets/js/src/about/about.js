import './about.scss';

import {Fragment, render, useState} from '@wordpress/element';
import Header from './components/Header';
import Hero from './components/Hero';
import ProductCards from './components/ProductCards';

document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('#ti-sdk-about');

    if (!root) {
        return;
    }

    render(
        <div className="ti-about">
            <Header/>
            <Hero/>
            <ProductCards/>
        </div>,
        root
    );
});