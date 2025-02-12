import formbricks from "@formbricks/js";

/**
 * Load the formbricks library and expose it to the global scope.
 * Emit a custom event to let other scripts know that formbricks is loaded.
 */
document.addEventListener("DOMContentLoaded", () => {
    window.tsdk_formbricks = {
        init: (args) => {
            args = {
                ...args,
                ...window.tsdk_survey_data
            }

            formbricks?.init(args)
        }
    };

    // Auto-trigger if the survey use the new format delivered with SDK.
    if ( window.tsdk_survey_data?.attributes?.install_days_number ) {
        window.tsdk_formbricks?.init({});
    }
 
    window.dispatchEvent(new Event("themeisle:survey:loaded"));
});
