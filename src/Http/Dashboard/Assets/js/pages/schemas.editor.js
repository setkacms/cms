import Dashboard from '../core/legacy.js';
import { App } from '../core/bootstrap.js';

App.Modules['schemas.editor'] = {
    init() {
        Dashboard.initSchemaBuilder();
    },
};
