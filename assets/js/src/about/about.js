import './about.scss';

import {Fragment, render, useState} from '@wordpress/element';
import Header from './components/Header';

document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('#ti-sdk-about');

    if (!root) {
        return;
    }

    render(
        <div className="ti-about">
            <Header/>
        </div>,
        root
    );
});