import Dashboard from '../core/legacy.js';
import { App } from '../core/bootstrap.js';

App.Modules['taxonomies.terms'] = {
    init() {
        Dashboard.initTaxonomyTermsModule();
    },
};
