import OptimoleNotice from "./OptimoleNotice";
import {render} from '@wordpress/element';

const runElementorActions = (panel, model, view) => {

    console.log({model, view, panel})

    const controlsWrap = document.querySelector('#elementor-panel__editor__help');
    const mountPoint = document.createElement('div');
    mountPoint.id = 'ti-optml-notice';

    if (controlsWrap) {
        // insert before the help button
        controlsWrap.parentNode.insertBefore(mountPoint, controlsWrap);
        render(<OptimoleNotice stacked type="elementor"/>, mountPoint);
    }


}

elementor.hooks.addAction('panel/open_editor/widget/image', runElementorActions);