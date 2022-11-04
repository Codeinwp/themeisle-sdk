import {registerPlugin} from '@wordpress/plugins';
import {PluginPostPublishPanel} from '@wordpress/edit-post';
import {useSelect} from '@wordpress/data';
import OptimoleNotice from "./OptimoleNotice";
import {getBlocksByType} from "./common/utils";

const PluginPostPublishPanelTest = () => {
        const {getBlocks} = useSelect( ( select ) => {
            const {getBlocks} = select( 'core/block-editor' );
            return {
                getBlocks
            };
        });

    const imageBlocksCount = getBlocksByType( getBlocks(), 'core/image' ).length;

    if( imageBlocksCount < 2 ) {
        return null;
    }

    return (
        <PluginPostPublishPanel className="ti-sdk-optimole-post-publish">
            <OptimoleNotice stacked type="editor"/>
        </PluginPostPublishPanel>
    );
};

registerPlugin('post-publish-panel-test', {
    render: PluginPostPublishPanelTest,
});