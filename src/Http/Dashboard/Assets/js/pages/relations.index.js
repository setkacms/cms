import Dashboard from '../core/legacy.js';
import { App } from '../core/bootstrap.js';

App.Modules['relations.index'] = {
    init() {
        Dashboard.initRelationsModule();
    },
};
