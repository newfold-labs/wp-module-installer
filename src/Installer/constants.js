export const INSTALLER_DIV = 'nfd-installer';
export const wpRestURL = window.nfdInstaller?.restUrl;
export const installerRestRoute = 'newfold-installer/v1';
export const pluginInstallHash = window.nfdInstaller?.pluginInstallHash;
export const installerAPI = `${ wpRestURL }/${ installerRestRoute }/plugins/install`;
