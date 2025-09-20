import Dashboard from '../core/legacy.js';

const App = window.App || (window.App = { Modules: {} });

App.Modules['widgets.autosave'] = {
    init(root) {
        Dashboard.initElementAutosaveModule(root);
    },
};
