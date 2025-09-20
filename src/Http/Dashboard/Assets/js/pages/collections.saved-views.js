import Dashboard from '../core/legacy.js';
import { App } from '../core/bootstrap.js';

App.Modules['collections.saved-views'] = {
    init() {
        Dashboard.initCollectionsSavedViews();
    },
};
