import Dashboard from '../core/legacy.js';

const App = window.App || (window.App = { Modules: {} });

App.Modules['widgets.taxonomy-tree'] = {
    init(root) {
        Dashboard.initTaxonomyTermsModule(root);
    },
};
