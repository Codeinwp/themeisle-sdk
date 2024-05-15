import formbricks from "@formbricks/js/app";

/**
 * Load the formbricks library and expose it to the global scope.
 * Emit a custom event to let other scripts know that formbricks is loaded.
 */
document.addEventListener("DOMContentLoaded", () => {
    window.tsdk_formbricks = formbricks;
    window.dispatchEvent(new Event("themeisle:survey:loaded"));
});
