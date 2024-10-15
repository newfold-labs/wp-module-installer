/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/Installer/constants.js":
/*!************************************!*\
  !*** ./src/Installer/constants.js ***!
  \************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   INSTALLER_DIV: () => (/* binding */ INSTALLER_DIV),
/* harmony export */   installerAPI: () => (/* binding */ installerAPI),
/* harmony export */   installerRestRoute: () => (/* binding */ installerRestRoute),
/* harmony export */   pluginInstallHash: () => (/* binding */ pluginInstallHash),
/* harmony export */   wpRestURL: () => (/* binding */ wpRestURL)
/* harmony export */ });
const INSTALLER_DIV = 'nfd-installer';
const wpRestURL = window.nfdInstaller?.restUrl;
const installerRestRoute = 'newfold-installer/v1';
const pluginInstallHash = window.nfdInstaller?.pluginInstallHash;
const installerAPI = `${wpRestURL}/${installerRestRoute}/plugins/install`;

/***/ }),

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
/* harmony import */ var _Installer_constants__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../Installer/constants */ "./src/Installer/constants.js");
// External Imports


// Internal Imports

_wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_0___default()(() => {
  function renderModal(pluginName, pluginSlug, pluginProvider, pluginURL, activate) {
    // create the installer div
    document.getElementById(_Installer_constants__WEBPACK_IMPORTED_MODULE_1__.INSTALLER_DIV).style.display = 'block';
    document.getElementById(_Installer_constants__WEBPACK_IMPORTED_MODULE_1__.INSTALLER_DIV).setAttribute('nfd-installer-app__plugin--name', pluginName);
    document.getElementById(_Installer_constants__WEBPACK_IMPORTED_MODULE_1__.INSTALLER_DIV).setAttribute('nfd-installer-app__plugin--slug', pluginSlug);
    document.getElementById(_Installer_constants__WEBPACK_IMPORTED_MODULE_1__.INSTALLER_DIV).setAttribute('nfd-installer-app__plugin--provider', pluginProvider);
    document.getElementById(_Installer_constants__WEBPACK_IMPORTED_MODULE_1__.INSTALLER_DIV).setAttribute('nfd-installer-app__plugin--url', pluginURL);
    document.getElementById(_Installer_constants__WEBPACK_IMPORTED_MODULE_1__.INSTALLER_DIV).setAttribute('nfd-installer-ap__plugin--activate', activate === 'true' ? true : false);
    window.dispatchEvent(new Event('installerParamsSet'));
  }
  const domObserver = new window.MutationObserver(mutationList => {
    for (const mutation of mutationList) {
      if (mutation.type === 'childList') {
        for (const addedNode of mutation.addedNodes) {
          if (typeof addedNode === 'object' && typeof addedNode.querySelectorAll === 'function') {
            addedNode.querySelectorAll('[data-nfd-installer-plugin-provider]').forEach(ele => {
              ele.addEventListener('click', function (e) {
                if (e.target.getAttribute('data-nfd-installer-plugin-slug') !== null) {
                  renderModal(this.getAttribute('data-nfd-installer-plugin-name'), this.getAttribute('data-nfd-installer-plugin-slug'), this.getAttribute('data-nfd-installer-plugin-provider'), this.getAttribute('data-nfd-installer-plugin-url'), this.getAttribute('data-nfd-installer-plugin-activate'));
                }
              });
            });
          }
        }
      }
    }
  });
  domObserver.observe(document.body, {
    childList: true,
    subtree: true
  });
});
})();

((window.newfold = window.newfold || {}).Installer = window.newfold.Installer || {}).dataAttrListener = __webpack_exports__;
/******/ })()
;
//# sourceMappingURL=dataAttrListener.js.map