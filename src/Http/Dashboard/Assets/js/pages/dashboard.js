import Dashboard from '../core/legacy.js';
import { App } from '../core/bootstrap.js';

App.Modules['dashboard'] = {
    init(root) {
        Dashboard.initActivityTable(root);
        Dashboard.bindFiltering(root);
        Dashboard.bindBulkActions(root);
    },
};
