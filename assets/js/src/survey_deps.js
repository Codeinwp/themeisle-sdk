import formbricks from "@formbricks/js";

/**
 * Load the formbricks library and expose it to the global scope.
 * Emit a custom event to let other scripts know that formbricks is loaded.
 */
document.addEventListener("DOMContentLoaded", () => {
    window.tsdk_formbricks = {
        init: (args) => {
            if (typeof args !== 'object' || args === null) {
                args = {};
            }

            const mergedArgs = {
                ...window.tsdk_survey_data,
                ...args,
                attributes: {
                    ...(window.tsdk_survey_data.attributes ?? {}),
                    ...(args.attributes ?? {})
                }
            }

            formbricks?.init(mergedArgs)
        }
    };

    const isNumeric = (value) => !isNaN(value) && typeof value !== "boolean";

    let timer = null;

    // Auto-trigger if the survey use the new format delivered with SDK.
    if ( isNumeric( window.tsdk_survey_data?.attributes?.install_days_number ) ) {
        timer = setTimeout(() => {
            window.tsdk_formbricks?.init();
        }, 350);
    }

    // Cancel auto-trigger if a plugin request manual control.
    window.addEventListener( 'themeisle:survey:trigger:cancel', () => {
        clearTimeout( timer );
    })

    window.dispatchEvent(new Event("themeisle:survey:loaded"));
});
