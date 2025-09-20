import Dashboard from '../core/legacy.js';
import { App } from '../core/bootstrap.js';

App.Modules['media.view'] = {
    init(root) {
        Dashboard.initMediaViewModule(root);
    },
};
