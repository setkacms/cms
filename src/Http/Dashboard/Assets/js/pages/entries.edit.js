import Dashboard from '../core/legacy.js';
import { App } from '../core/bootstrap.js';

App.Modules['entries.edit'] = {
    init() {
        Dashboard.initElementAutosaveModule();
    },
};
