import Dashboard from '../core/legacy.js';

const App = window.App || (window.App = { Modules: {} });

App.Modules['widgets.media-browser'] = {
    init(root) {
        Dashboard.initMediaLibraryModule(root);
    },
};
