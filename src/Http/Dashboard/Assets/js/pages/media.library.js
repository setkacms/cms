import Dashboard from '../core/legacy.js';
import { App } from '../core/bootstrap.js';

App.Modules['media.library'] = {
    init() {
        Dashboard.initMediaLibraryModule();
    },
};
