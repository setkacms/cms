import Dashboard from '../core/legacy.js';
import { App } from '../core/bootstrap.js';

App.Modules['collections.entries'] = {
    init() {
        Dashboard.initCollectionEntriesModule();
    },
};
