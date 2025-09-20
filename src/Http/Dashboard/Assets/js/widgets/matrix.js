import Dashboard from '../core/legacy.js';

const App = window.App || (window.App = { Modules: {} });

App.Modules['widgets.matrix'] = {
    init(root) {
        Dashboard.initMatrixRepeater(root);
    },
};
