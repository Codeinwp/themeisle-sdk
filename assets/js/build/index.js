/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./assets/js/src/OptimoleNotice/index.js":
/*!***********************************************!*\
  !*** ./assets/js/src/OptimoleNotice/index.js ***!
  \***********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ OptimoleNotice)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _style_scss__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./style.scss */ "./assets/js/src/OptimoleNotice/style.scss");





function OptimoleNotice(_ref) {
  let {
    stacked = false,
    noImage = false,
    type,
    onDismiss
  } = _ref;
  const [showForm, setShowForm] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  const {
    assets,
    title,
    email: initialEmail,
    option
  } = window.themeisleSDKPromotions;
  const [email, setEmail] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(initialEmail || '');
  const [dismissed, setDismissed] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);

  const dismissNotice = () => {
    console.log('clicked');
    setDismissed(true);

    if (onDismiss) {
      onDismiss();
    } // const value = JSON.parse(option);
    // value[type] = new Date().getTime() / 1000 | 0;
    //
    // window.tiSdkData.option = JSON.stringify(value);

  };

  const toggleForm = () => {
    setShowForm(!showForm);
  };

  const updateEmail = e => {
    setEmail(e.target.value);
  };

  const submitForm = e => {
    e.preventDefault();
  };

  if (dismissed) {
    return null;
  }

  const form = () => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("form", {
    onSubmit: submitForm
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    defaultValue: email,
    type: "email",
    onChange: updateEmail,
    placeholder: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Email address', 'textdomain')
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
    isPrimary: true,
    type: "submit"
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Start using Optimole', 'textdomain')));

  if (stacked) {
    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "ti-om-stack-wrap"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "om-stack-notice"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
      onClick: dismissNotice,
      isLink: true,
      className: "om-notice-dismiss"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
      className: "dashicons-no-alt dashicons"
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
      className: "screen-reader-text"
    }, "Dismiss this notice.")), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("img", {
      src: assets + '/optimole-logo.svg',
      alt: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Optimole logo', 'textdomain')
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h2", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Get more with Optimole', 'textdomain')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Optimize, store and deliver this image with 80% less size while looking just as great, using Optimole.', 'textdomain')), !showForm && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
      isPrimary: true,
      onClick: toggleForm,
      className: "cta"
    }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Get Started Free', 'textdomain')), showForm && form(), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", null, title)));
  }

  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
    onClick: dismissNotice,
    isLink: true,
    className: "om-notice-dismiss"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    className: "dashicons-no-alt dashicons"
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    className: "screen-reader-text"
  }, "Dismiss this notice.")), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "content"
  }, !noImage && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("img", {
    src: assets + '/optimole-logo.svg',
    alt: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Optimole logo', 'textdomain')
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, title), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    className: "description"
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Save your server space by storing images to Optimole and deliver them optimized from 400 locations around the globe. Unlimited images, Unlimited traffic.', 'textdomain')), !showForm && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "actions"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
    isPrimary: true,
    onClick: toggleForm
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Get Started Free', 'textdomain')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
    isLink: true,
    target: "_blank",
    href: "https://wordpress.org/plugins/optimole-wp"
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Learn more', 'textdomain'))), showForm && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "form-wrap"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", null, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Enter your email address to create & connect your account', 'textdomain')), form()))));
}

/***/ }),

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

/***/ "./assets/js/src/index.js":
/*!********************************!*\
  !*** ./assets/js/src/index.js ***!
  \********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _otter_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./otter.js */ "./assets/js/src/otter.js");
/* harmony import */ var _optimole_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./optimole.js */ "./assets/js/src/optimole.js");



/***/ }),

/***/ "./assets/js/src/optimole.js":
/*!***********************************!*\
  !*** ./assets/js/src/optimole.js ***!
  \***********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_plugins__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/plugins */ "@wordpress/plugins");
/* harmony import */ var _wordpress_plugins__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_plugins__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_edit_post__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/edit-post */ "@wordpress/edit-post");
/* harmony import */ var _wordpress_edit_post__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_edit_post__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _common_utils__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./common/utils */ "./assets/js/src/common/utils.js");
/* harmony import */ var _OptimoleNotice__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./OptimoleNotice */ "./assets/js/src/OptimoleNotice/index.js");







const {
  showPromotion,
  option
} = window.themeisleSDKPromotions;

const remove = () => {
  const div = document.querySelector('#ti-optml-notice-helper');

  if (!div) {
    return;
  }

  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.unmountComponentAtNode)(div);
};

const add = () => {
  const mount = document.querySelector('#ti-optml-notice-helper');

  if (option['om-attachment']) {
    return;
  }

  if (!mount) {
    return;
  }

  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.render)((0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "notice notice-info ti-sdk-om-notice",
    style: {
      margin: 0
    }
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_OptimoleNotice__WEBPACK_IMPORTED_MODULE_5__["default"], {
    noImage: true,
    type: "om-attachment",
    onDismiss: () => {
      remove();
      window.themeisleSDKPromotions.option['om-attachment'] = true;
    }
  })), mount);
};

if (showPromotion === 'om-attachment') {
  wp.media.view.Attachment.Details.prototype.on("ready", () => {
    setTimeout(() => {
      remove();
      add();
    }, 100);
  });
  wp.media.view.Modal.prototype.on("close", () => {
    setTimeout(remove, 100);
  });
}

if (showPromotion === 'om-media') {
  const root = document.getElementById('ti-optml-notice');

  if (root) {
    (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.render)((0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_OptimoleNotice__WEBPACK_IMPORTED_MODULE_5__["default"], {
      type: "om-attachment",
      onDismiss: () => {
        (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.unmountComponentAtNode)(root);
        root.parentNode.removeChild(root);
      }
    }), root);
  }
}

if (showPromotion === 'om-editor') {
  const TiSDKPromo = () => {
    const {
      getBlocks
    } = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_3__.useSelect)(select => {
      const {
        getBlocks
      } = select('core/block-editor');
      return {
        getBlocks
      };
    });
    const imageBlocksCount = (0,_common_utils__WEBPACK_IMPORTED_MODULE_4__.getBlocksByType)(getBlocks(), 'core/image').length;

    if (imageBlocksCount < 2) {
      return null;
    }

    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_edit_post__WEBPACK_IMPORTED_MODULE_2__.PluginPostPublishPanel, {
      className: "ti-sdk-optimole-post-publish"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_OptimoleNotice__WEBPACK_IMPORTED_MODULE_5__["default"], {
      stacked: true,
      type: "editor"
    }));
  };

  (0,_wordpress_plugins__WEBPACK_IMPORTED_MODULE_1__.registerPlugin)('post-publish-panel-test', {
    render: TiSDKPromo
  });
}

if (showPromotion === 'om-elementor' && window.elementor) {
  const runElementorActions = (panel, model, view) => {
    if (option['om-elementor']) {
      return;
    }

    const controlsWrap = document.querySelector('#elementor-panel__editor__help');
    const mountPoint = document.createElement('div');
    mountPoint.id = 'ti-optml-notice';

    if (controlsWrap) {
      // insert before the help button
      controlsWrap.parentNode.insertBefore(mountPoint, controlsWrap);
      (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.render)((0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_OptimoleNotice__WEBPACK_IMPORTED_MODULE_5__["default"], {
        stacked: true,
        type: "elementor",
        onDismiss: () => {
          window.themeisleSDKPromotions.option['om-elementor'] = true;
          (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.unmountComponentAtNode)(mountPoint);
        }
      }), mountPoint);
    }
  };

  elementor.hooks.addAction('panel/open_editor/widget/image', runElementorActions);
}

/***/ }),

/***/ "./assets/js/src/otter.js":
/*!********************************!*\
  !*** ./assets/js/src/otter.js ***!
  \********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

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
        await (0,_common_utils_js__WEBPACK_IMPORTED_MODULE_8__.activatePlugin)(window.themeisleSDKPromotions.otterActivationUrl);
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
        const option = window.themeisleSDKPromotions.option;
        option[window.themeisleSDKPromotions.showPromotion] = new Date().getTime() / 1000 | 0;
        updateOption('themeisle_sdk_promotions', JSON.stringify(option));
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
            key: key,
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

/***/ }),

/***/ "./assets/js/src/OptimoleNotice/style.scss":
/*!*************************************************!*\
  !*** ./assets/js/src/OptimoleNotice/style.scss ***!
  \*************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


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

/***/ "@wordpress/edit-post":
/*!**********************************!*\
  !*** external ["wp","editPost"] ***!
  \**********************************/
/***/ ((module) => {

module.exports = window["wp"]["editPost"];

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

/***/ }),

/***/ "@wordpress/plugins":
/*!*********************************!*\
  !*** external ["wp","plugins"] ***!
  \*********************************/
/***/ ((module) => {

module.exports = window["wp"]["plugins"];

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
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	(() => {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var chunkIds = deferred[i][0];
/******/ 				var fn = deferred[i][1];
/******/ 				var priority = deferred[i][2];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	})();
/******/ 	
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
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"index": 0,
/******/ 			"./style-index": 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			var chunkIds = data[0];
/******/ 			var moreModules = data[1];
/******/ 			var runtime = data[2];
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = self["webpackChunkthemeisle_sdk"] = self["webpackChunkthemeisle_sdk"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["./style-index"], () => (__webpack_require__("./assets/js/src/index.js")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;
//# sourceMappingURL=index.js.map