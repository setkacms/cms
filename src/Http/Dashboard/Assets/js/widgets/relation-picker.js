import Dashboard from '../core/legacy.js';

const App = window.App || (window.App = { Modules: {} });

App.Modules['widgets.relation-picker'] = {
    init(root) {
        Dashboard.initRelationsModule(root);
    },
};
