/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./assets/js/src/common/useSettings.js":
/*!*********************************************!*\
  !*** ./assets/js/src/common/useSettings.js ***!
  \*********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_api__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/api */ "@wordpress/api");
/* harmony import */ var _wordpress_api__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_3__);
/**
 * WordPress dependencies.
 */




/**
 * useSettings Hook.
 *
 * useSettings hook to get/update WordPress' settings database.
 *
 * Setting field needs to be registered to REST for this function to work.
 *
 * This hook works similar to get_option and update_option in PHP just without the option for a default value.
 * For notificiations to work, you need to add a Snackbar section to your React codebase if it isn't being
 * used inside the block editor.
 *
 * @see https://github.com/WordPress/gutenberg/blob/trunk/packages/editor/src/components/editor-snackbars/index.js
 * @author  Hardeep Asrani <hardeepasrani@gmail.com>
 * @version 1.1
 *
 */

const useSettings = () => {
  const {
    createNotice
  } = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_2__.dispatch)('core/notices');
  const [settings, setSettings] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_3__.useState)({});
  const [status, setStatus] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_3__.useState)('loading');

  const getSettings = () => {
    _wordpress_api__WEBPACK_IMPORTED_MODULE_0___default().loadPromise.then(async () => {
      try {
        const settings = new (_wordpress_api__WEBPACK_IMPORTED_MODULE_0___default().models.Settings)();
        const response = await settings.fetch();
        setSettings(response);
      } catch (error) {
        setStatus('error');
      } finally {
        setStatus('loaded');
      }
    });
  };

  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_3__.useEffect)(() => {
    getSettings();
  }, []);

  const getOption = option => {
    return settings === null || settings === void 0 ? void 0 : settings[option];
  };

  const updateOption = function (option, value) {
    let success = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Settings saved.', 'textdomain');
    setStatus('saving');
    const save = new (_wordpress_api__WEBPACK_IMPORTED_MODULE_0___default().models.Settings)({
      [option]: value
    }).save();
    save.success((response, status) => {
      if ('success' === status) {
        setStatus('loaded');
        createNotice('success', success, {
          isDismissible: true,
          type: 'snackbar'
        });
      }

      if ('error' === status) {
        setStatus('error');
        createNotice('error', (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('An unknown error occurred.', 'textdomain'), {
          isDismissible: true,
          type: 'snackbar'
        });
      }

      getSettings();
    });
    save.error(response => {
      setStatus('error');
      createNotice('error', response.responseJSON.message ? response.responseJSON.message : (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('An unknown error occurred.', 'textdomain'), {
        isDismissible: true,
        type: 'snackbar'
      });
    });
  };

  return [getOption, updateOption, status];
};

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (useSettings);

/***/ }),

/***/ "./assets/js/src/common/utils.js":
/*!***************************************!*\
  !*** ./assets/js/src/common/utils.js ***!
  \***************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "activatePlugin": () => (/* binding */ activatePlugin),
/* harmony export */   "getBlocksByType": () => (/* binding */ getBlocksByType),
/* harmony export */   "installPlugin": () => (/* binding */ installPlugin)
/* harmony export */ });
const installPlugin = slug => {
  return new Promise(resolve => {
    wp.updates.ajax('install-plugin', {
      slug,
      success: () => {
        resolve({
          success: true
        });
      },
      error: err => {
        resolve({
          success: false,
          code: err.errorCode
        });
      }
    });
  });
};

const activatePlugin = url => {
  return new Promise(resolve => {
    jQuery.get(url).done(() => {
      resolve({
        success: true
      });
    }).fail(() => {
      resolve({
        success: false
      });
    });
  });
};

const flatRecursively = (r, a) => {
  const b = {};
  Object.keys(a).forEach(function (k) {
    if ('innerBlocks' !== k) {
      b[k] = a[k];
    }
  });
  r.push(b);

  if (Array.isArray(a.innerBlocks)) {
    b.innerBlocks = a.innerBlocks.map(i => {
      return i.id;
    });
    return a.innerBlocks.reduce(flatRecursively, r);
  }

  return r;
};
/**
 * Get blocks by type.
 *
 * @param {Array} blocks blocks array.
 * @param {string} type type of block looking for.
 *
 * @return {Array} array of blocks of {type} in page
 */


const getBlocksByType = (blocks, type) => blocks.reduce(flatRecursively, []).filter(a => type === a.name);



/***/ }),

/***/ "@wordpress/api":
/*!*****************************!*\
  !*** external ["wp","api"] ***!
  \*****************************/
/***/ ((module) => {

module.exports = window["wp"]["api"];

/***/ }),

/***/ "@wordpress/block-editor":
/*!*************************************!*\
  !*** external ["wp","blockEditor"] ***!
  \*************************************/
/***/ ((module) => {

module.exports = window["wp"]["blockEditor"];

/***/ }),

/***/ "@wordpress/components":
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
/***/ ((module) => {

module.exports = window["wp"]["components"];

/***/ }),

/***/ "@wordpress/compose":
/*!*********************************!*\
  !*** external ["wp","compose"] ***!
  \*********************************/
/***/ ((module) => {

module.exports = window["wp"]["compose"];

/***/ }),

/***/ "@wordpress/data":
/*!******************************!*\
  !*** external ["wp","data"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["data"];

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ ((module) => {

module.exports = window["wp"]["element"];

/***/ }),

/***/ "@wordpress/hooks":
/*!*******************************!*\
  !*** external ["wp","hooks"] ***!
  \*******************************/
/***/ ((module) => {

module.exports = window["wp"]["hooks"];

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["i18n"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!********************************!*\
  !*** ./assets/js/src/index.js ***!
  \********************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _wordpress_compose__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/compose */ "@wordpress/compose");
/* harmony import */ var _wordpress_compose__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_compose__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _wordpress_hooks__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @wordpress/hooks */ "@wordpress/hooks");
/* harmony import */ var _wordpress_hooks__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(_wordpress_hooks__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var _common_useSettings_js__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./common/useSettings.js */ "./assets/js/src/common/useSettings.js");
/* harmony import */ var _common_utils_js__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./common/utils.js */ "./assets/js/src/common/utils.js");


/**
 * WordPress dependencies.
 */







/**
 * Internal dependencies.
 */



const style = {
  button: {
    display: 'flex',
    justifyContent: 'center',
    width: '100%'
  },
  image: {
    padding: '20px 0'
  },
  skip: {
    container: {
      display: 'flex',
      flexDirection: 'column',
      alignItems: 'center'
    },
    button: {
      fontSize: '9px'
    },
    poweredby: {
      fontSize: '9px',
      textTransform: 'uppercase'
    }
  }
};
const upsells = {
  'blocks-css': {
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Custom CSS', 'textdomain'),
    description: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Enable Otter Blocks to add Custom CSS for this block.'),
    image: 'css.jpg'
  },
  'blocks-animation': {
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Animations', 'textdomain'),
    description: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Enable Otter Blocks to add Animations for this block.'),
    image: 'animation.jpg'
  },
  'blocks-conditions': {
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Visibility Conditions', 'textdomain'),
    description: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Enable Otter Blocks to add Visibility Conditions for this block.'),
    image: 'conditions.jpg'
  }
};

const Footer = _ref => {
  let {
    onClick
  } = _ref;
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: style.skip.container
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.Button, {
    style: style.skip.button,
    variant: "tertiary",
    onClick: onClick
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Skip for now')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    style: style.skip.poweredby
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Recommended by ') + window.themeisleSDKPromotions.product));
};

const withInspectorControls = (0,_wordpress_compose__WEBPACK_IMPORTED_MODULE_4__.createHigherOrderComponent)(BlockEdit => {
  return props => {
    if (props.isSelected && Boolean(window.themeisleSDKPromotions.showPromotion)) {
      const [isLoading, setLoading] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
      const [installStatus, setInstallStatus] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)('default');
      const [hasSkipped, setHasSkipped] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
      const [getOption, updateOption, status] = (0,_common_useSettings_js__WEBPACK_IMPORTED_MODULE_7__["default"])();

      const install = async () => {
        setLoading(true);
        await (0,_common_utils_js__WEBPACK_IMPORTED_MODULE_8__.installPlugin)('otter-blocks');
        updateOption('themeisle_sdk_promotions_otter_installed', !Boolean(getOption('themeisle_sdk_promotions_otter_installed')));
        await (0,_common_utils_js__WEBPACK_IMPORTED_MODULE_8__.activatePlugin)(window.themeisleSDKPromotions.activationUrl);
        setLoading(false);
        setInstallStatus('installed');
      };

      const Install = () => {
        if ('installed' === installStatus) {
          return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("strong", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Awesome! Refresh the page to see Otter Blocks in action.')));
        }

        return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.Button, {
          variant: "secondary",
          onClick: install,
          isBusy: isLoading,
          style: style.button
        }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Install & Activate Otter Blocks'));
      };

      const onSkip = () => {
        const option = JSON.parse(window.themeisleSDKPromotions.promotions_otter);
        option[window.themeisleSDKPromotions.showPromotion] = new Date().getTime() / 1000 | 0;
        updateOption('themeisle_sdk_promotions_otter', JSON.stringify(option));
        window.themeisleSDKPromotions.showPromotion = false;
      };

      (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
        if (hasSkipped) {
          onSkip();
        }
      }, [hasSkipped]);

      if (hasSkipped) {
        return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(BlockEdit, props);
      }

      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(BlockEdit, props), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__.InspectorControls, null, Object.keys(upsells).map(key => {
        if (key === window.themeisleSDKPromotions.showPromotion) {
          const upsell = upsells[key];
          return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.PanelBody, {
            title: upsell.title,
            initialOpen: false
          }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, upsell.description), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(Install, null), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("img", {
            style: style.image,
            src: window.themeisleSDKPromotions.assets + upsell.image
          }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(Footer, {
            onClick: () => setHasSkipped(true)
          }));
        }
      })));
    }

    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(BlockEdit, props);
  };
}, 'withInspectorControl');

if (!(0,_wordpress_data__WEBPACK_IMPORTED_MODULE_5__.select)('core/edit-site')) {
  (0,_wordpress_hooks__WEBPACK_IMPORTED_MODULE_6__.addFilter)('editor.BlockEdit', 'themeisle-sdk/with-inspector-controls', withInspectorControls);
}
})();

/******/ })()
;
//# sourceMappingURL=index.js.map