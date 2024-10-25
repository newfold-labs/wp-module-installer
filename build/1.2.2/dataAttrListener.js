/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "@wordpress/dom-ready":
/*!**********************************!*\
  !*** external ["wp","domReady"] ***!
  \**********************************/
/***/ ((module) => {

module.exports = window["wp"]["domReady"];

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
/*!*****************************************!*\
  !*** ./src/Scripts/dataAttrListener.js ***!
  \*****************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/dom-ready */ "@wordpress/dom-ready");
/* harmony import */ var _wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_0__);
// External Imports

_wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_0___default()(() => {
  function dispatchEvent(detail) {
    window.dispatchEvent(new CustomEvent('installerParamsSet', {
      detail
    }));
  }
  document.body.addEventListener('click', e => {
    const el = e.target;
    if (el.hasAttribute('data-nfd-installer-plugin-name')) {
      // Don't follow the existing link
      e.preventDefault();

      // URL to redirect to after install
      const redirectUrl = el.getAttribute('href') || el.getAttribute('data-nfd-installer-plugin-url');

      // Is free plugin
      if (el.hasAttribute('data-nfd-installer-download-url')) {
        dispatchEvent({
          action: 'installFreePlugin',
          pluginName: el.getAttribute('data-nfd-installer-plugin-name'),
          pluginDownloadUrl: el.getAttribute('data-nfd-installer-download-url'),
          pluginProvider: el.getAttribute('data-nfd-installer-pls-provider'),
          redirectUrl
        });
        return false;
      }

      // Is premium plugin
      if (el.hasAttribute('data-nfd-installer-pls-slug') && el.hasAttribute('data-nfd-installer-pls-provider')) {
        dispatchEvent({
          action: 'installPremiumPlugin',
          pluginName: el.getAttribute('data-nfd-installer-plugin-name'),
          pluginSlug: el.getAttribute('data-nfd-installer-pls-slug'),
          pluginProvider: el.getAttribute('data-nfd-installer-pls-provider'),
          redirectUrl
        });
        return false;
      }

      // TODO: Handle use cases for theme installs

      // Redirect to the URL provided by the data attribute
      if (redirectUrl) {
        window.location.href = redirectUrl;
      }
    }
  });
});
})();

((window.newfold = window.newfold || {}).Installer = window.newfold.Installer || {}).dataAttrListener = __webpack_exports__;
/******/ })()
;
//# sourceMappingURL=dataAttrListener.js.map