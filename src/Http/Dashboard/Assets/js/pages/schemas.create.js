import Dashboard from '../core/legacy.js';
import { App } from '../core/bootstrap.js';

App.Modules['schemas.create'] = {
    init() {
        Dashboard.initSchemaBuilder();
    },
};
