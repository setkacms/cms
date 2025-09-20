import Dashboard from '../core/legacy.js';
import { App } from '../core/bootstrap.js';

App.Modules['elements.create'] = {
    init() {
        Dashboard.initElementAutosaveModule();
    },
};
