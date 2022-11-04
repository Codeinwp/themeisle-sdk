import {render, unmountComponentAtNode} from '@wordpress/element';
import OptimoleNotice from './OptimoleNotice';

const root = document.getElementById('ti-optml-notice');

if (root) {
    render(<OptimoleNotice type="dashboard"/>, root);
}

const removeNotice = () => {
    const div = document.querySelector('#ti-optml-notice-attachment');
    if( ! div ) {
        return;
    }
    unmountComponentAtNode(div)
}
const addAttachmentNotice = () => {
    const div = document.querySelector('#ti-optml-notice-attachment');

    if (!div) {
        return;
    }

    render(<OptimoleNotice noImage={true} type="attachment"/>, div);
}

wp.media.view.Attachment.Details.prototype.on("ready", () => setTimeout(() => {
    removeNotice();
    addAttachmentNotice();
}, 100));
wp.media.view.Modal.prototype.on("close", () => setTimeout(removeNotice, 100));