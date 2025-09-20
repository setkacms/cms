import Dashboard from '../core/legacy.js';
import { App } from '../core/bootstrap.js';

App.Modules['workflow.transitions'] = {
    init() {
        Dashboard.initWorkflowModule();
    },
};
