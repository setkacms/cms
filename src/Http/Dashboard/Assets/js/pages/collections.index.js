import Dashboard from '../core/legacy.js';
import { App } from '../core/bootstrap.js';

App.Modules['collections.index'] = {
    init() {
        Dashboard.initCollectionsTable();
        Dashboard.bindCollectionsFilters();
        Dashboard.bindCollectionsBulkActions();
        Dashboard.initCollectionsSavedViews();
        Dashboard.bindCollectionsActionBar();
        Dashboard.initCollectionsExport();
    },
};
