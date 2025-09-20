import Dashboard from './legacy.js';
import '../widgets/matrix.js';
import '../widgets/relation-picker.js';
import '../widgets/media-browser.js';
import '../widgets/taxonomy-tree.js';
import '../widgets/autosave.js';

const App = (window.App = window.App || { Modules: {} });

function runGlobalInitializers(root) {
    if (!window.jQuery) {
        return;
    }

    const $root = root ? window.jQuery(root) : window.jQuery(document);

    // Общие элементы интерфейса
    Dashboard.initSelect2($root);
    Dashboard.initFlatpickr($root);
    Dashboard.initDropzone($root);
    Dashboard.initSortable($root);
    Dashboard.initMatrixRepeater($root);
    Dashboard.initCodeMirror($root);
    Dashboard.bindSelectAll($root);
    Dashboard.bindFiltering($root);
    Dashboard.bindBulkActions($root);
}

function bootstrap() {
    const root = document.querySelector('[data-page]');
    if (!root) {
        return;
    }

    runGlobalInitializers(root);

    const page = root.getAttribute('data-page');
    if (!page) {
        return;
    }

    const module = App.Modules[page];
    if (!module || typeof module.init !== 'function') {
        return;
    }

    module.init(root, { Dashboard });
}

export default bootstrap;
export { App, runGlobalInitializers };
