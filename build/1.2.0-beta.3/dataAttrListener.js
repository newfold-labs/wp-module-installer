(()=>{"use strict";var t={n:e=>{var n=e&&e.__esModule?()=>e.default:()=>e;return t.d(n,{a:n}),n},d:(e,n)=>{for(var l in n)t.o(n,l)&&!t.o(e,l)&&Object.defineProperty(e,l,{enumerable:!0,get:n[l]})},o:(t,e)=>Object.prototype.hasOwnProperty.call(t,e)};const e=window.wp.domReady;t.n(e)()((()=>{function t(t){window.dispatchEvent(new CustomEvent("installerParamsSet",{detail:t}))}document.body.addEventListener("click",(e=>{const n=e.target;if(n.hasAttribute("data-nfd-installer-plugin-name")){e.preventDefault();const l=n.getAttribute("href")||n.getAttribute("data-nfd-installer-plugin-url");if(n.hasAttribute("data-nfd-installer-download-url"))return t({action:"installFreePlugin",pluginName:n.getAttribute("data-nfd-installer-plugin-name"),pluginDownloadUrl:n.getAttribute("data-nfd-installer-download-url"),redirectUrl:l}),!1;if(n.hasAttribute("data-nfd-installer-pls-slug")&&n.hasAttribute("data-nfd-installer-pls-provider"))return t({action:"installPremiumPlugin",pluginName:n.getAttribute("data-nfd-installer-plugin-name"),pluginSlug:n.getAttribute("data-nfd-installer-pls-slug"),pluginProvider:n.getAttribute("data-nfd-installer-pls-provider"),redirectUrl:l}),!1;l&&(window.location.href=l)}}))})),((window.newfold=window.newfold||{}).Installer=window.newfold.Installer||{}).dataAttrListener={}})();