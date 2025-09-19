(function ($) {
    'use strict';

    var Dashboard = {
        activityTable: null,
        collectionsTable: null,
        collectionsSelection: {},
        collectionSavedViews: [],
        currentSavedViewId: null,
        isApplyingSavedView: false,
        schemasDataset: [],
        schemasDefaultSavedViews: [],
        schemasSavedViews: [],
        schemasCurrentViewId: null,
        schemasCurrentSelectionId: null,
        schemasSelectFirstAfterRender: false,
        schemasStorageKey: 'dashboard.schemas.savedViews',
        isApplyingSchemasView: false,
        collectionEntriesTable: null,
        collectionEntriesSelection: {},
        collectionEntriesSavedViews: [],
        collectionEntriesDefaultSavedViews: [],
        collectionEntriesCurrentViewId: null,
        isApplyingCollectionEntriesView: false,
        collectionEntriesCurrentNodeId: '',
        matrixInstances: [],
        matrixBlockUid: 0,
        relationsDataset: null,
        relationOptionsCache: {},
        relationSelectionKey: 'dashboard.relations.selection',
        relationSavedSelection: null,
        mediaAssetsDataset: null,
        mediaAssetsIndex: {},
        mediaLibraryFilters: null,
        mediaLibrarySelection: [],
        mediaLibraryViewMode: 'grid',
        taxonomyDataset: null,
        taxonomyCurrentHandle: '',
        taxonomySortableInstances: [],
        taxonomyCurrentIndex: {},
        taxonomySearchQuery: '',
        taxonomyStorageKey: 'dashboard.taxonomy.lastHandle',
        elementAutosaveContainer: null,
        elementAutosaveStatusElement: null,
        elementAutosaveTimer: null,
        elementAutosavePending: false,
        elementAutosaveDirty: false,
        elementAutosaveSaving: false,
        elementAutosaveRevision: 0,
        elementAutosaveCurrentPromise: null,
        elementAutosaveLastSaved: null,
        elementAutosaveStorageKey: 'dashboard.element.autosave',
        elementAutosaveDebounce: 2000,
        elementAutosaveMessagesCache: null,
        elementAutosaveReady: false,
        elementAutosaveStatus: 'clean',

        init: function () {
            this.initSelect2();
            this.initDataTables();
            this.initFlatpickr();
            this.initDropzone();
            this.initSortable();
            this.initMatrixRepeater();
            this.initCodeMirror();
            this.bindSelectAll();
            this.bindFiltering();
            this.bindBulkActions();
            this.bindCollectionsFilters();
            this.bindCollectionsBulkActions();
            this.initCollectionsSavedViews();
            this.initSchemasModule();
            this.bindCollectionsActionBar();
            this.initCollectionEntriesModule();
            this.initRelationsModule();
            this.initMediaLibraryModule();
            this.initTaxonomyTermsModule();
            this.initElementAutosaveModule();
        },

        initSelect2: function () {
            if (!$.fn.select2) {
                return;
            }

            $('.select2').each(function () {
                var $element = $(this);
                if ($element.data('skipGlobalInit')) {
                    return;
                }
                if ($element.data('select2')) {
                    return;
                }

                $element.select2({
                    width: '100%',
                    allowClear: true,
                    placeholder: $element.data('placeholder') || ''
                });
            });
        },

        initDataTables: function () {
            this.initActivityTable();
            this.initCollectionsTable();
            this.initCollectionEntriesTable();
        },

        initActivityTable: function () {
            var $table = $('#activity-table');
            if (!$table.length || !$.fn.DataTable) {
                return;
            }

            var filterFn = function (settings, data, dataIndex) {
                if (settings.nTable !== $table.get(0)) {
                    return true;
                }

                var type = $('#activity-type-filter').val();
                if (!type) {
                    return true;
                }

                var row = settings.aoData[dataIndex].nTr;
                return $(row).data('type') === type;
            };

            $.fn.dataTable.ext.search.push(filterFn);

            this.activityTable = $table.DataTable({
                paging: false,
                searching: false,
                ordering: false,
                info: false,
                autoWidth: false,
                language: {
                    emptyTable: 'Нет данных для отображения'
                }
            });

            var self = this;
            $table.on('click', 'tbody tr', function (event) {
                if ($(event.target).is('input, label, a, button, select')) {
                    return;
                }

                self.selectRow($(this));
            });

            var firstRow = $table.find('tbody tr').first();
            if (firstRow.length) {
                self.selectRow(firstRow);
            }
        },

        initFlatpickr: function () {
            if (typeof window.flatpickr === 'undefined') {
                return;
            }

            var selectors = '[data-role="filter-date-from"], [data-role="filter-date-to"]';

            $(selectors).each(function () {
                if (this._flatpickr) {
                    return;
                }

                window.flatpickr(this, {
                    dateFormat: 'd.m.Y',
                    altInput: true,
                    altFormat: 'd.m.Y',
                    allowInput: true
                });
            });
        },

        initDropzone: function () {
            if (typeof window.Dropzone === 'undefined') {
                return;
            }

            if (window.Dropzone.autoDiscover) {
                window.Dropzone.autoDiscover = false;
            }

            $('[data-role="media-dropzone"]').each(function () {
                var element = this;

                if (element.dropzone) {
                    return;
                }

                var options = {
                    url: $(element).data('upload-url') || '#',
                    autoProcessQueue: false,
                    addRemoveLinks: true,
                    dictDefaultMessage: 'Перетащите файлы сюда или нажмите для выбора.'
                };

                var dropzone = new window.Dropzone(element, options);
                $(element).data('dropzone', dropzone);
            });
        },

        initSortable: function () {
            if (typeof window.Sortable === 'undefined') {
                return;
            }

            $('[data-role="states-list"]').each(function () {
                var element = this;

                if (element._sortableInstance) {
                    return;
                }

                element._sortableInstance = window.Sortable.create(element, {
                    animation: 150,
                    ghostClass: 'workflow-state-ghost',
                    onEnd: function () {
                        $(element).trigger('states:reordered');
                    }
                });
            });
        },

        initMatrixRepeater: function () {
            var self = this;

            $('[data-role="matrix"]').each(function () {
                var $matrix = $(this);

                if ($matrix.data('matrixInitialized')) {
                    return;
                }

                $matrix.data('matrixInitialized', true);

                var instance = {
                    $matrix: $matrix,
                    $blocks: $matrix.find('[data-role="matrix-blocks"]').first(),
                    $storage: $matrix.find('[data-role="matrix-storage"]').first(),
                    $empty: $matrix.find('[data-role="matrix-empty"]').first(),
                    sortable: null
                };

                if (!instance.$blocks.length) {
                    instance.$blocks = $('<div data-role="matrix-blocks"></div>').appendTo($matrix);
                }

                self.matrixInstances.push(instance);

                $matrix.on('click', '[data-role="matrix-add"]', function (event) {
                    event.preventDefault();
                    var type = $(this).attr('data-block-type') || 'text';
                    self.addMatrixBlock(instance, type);
                });

                $matrix.on('click', '[data-role="matrix-remove"]', function (event) {
                    event.preventDefault();
                    var $block = $(this).closest('[data-role="matrix-block"]');
                    if (!$block.length) {
                        return;
                    }

                    self.removeMatrixBlock(instance, $block);
                });

                instance.$blocks.children('[data-role="matrix-block"]').each(function () {
                    self.initMatrixBlock(instance, $(this));
                });

                var initialBlocks = [];
                if (instance.$storage.length) {
                    var storedValue = instance.$storage.val();
                    if (storedValue) {
                        try {
                            initialBlocks = JSON.parse(storedValue) || [];
                        } catch (error) {
                            initialBlocks = [];
                            if (window.console && window.console.warn) {
                                window.console.warn('Невозможно прочитать данные матрицы', error);
                            }
                        }
                    }
                }

                if (initialBlocks.length) {
                    instance.$blocks.empty();
                    initialBlocks.forEach(function (blockData) {
                        self.addMatrixBlock(instance, blockData.type || 'text', blockData, true);
                    });
                }

                self.createMatrixSortable(instance);
                self.updateMatrixEmptyState(instance);
                self.syncMatrix(instance);
            });
        },

        addMatrixBlock: function (instance, type, data, skipSync) {
            if (!instance || !instance.$blocks || !instance.$blocks.length) {
                return null;
            }

            var template = this.getMatrixTemplate(instance.$matrix, type);
            if (!template) {
                return null;
            }

            var $block;

            if (template.content) {
                var fragment = document.importNode(template.content, true);
                var $wrapper = $('<div></div>').append(fragment);
                $block = $wrapper.children('[data-role="matrix-block"]').first();

                if (!$block.length) {
                    $block = $wrapper.children().first();
                }
            } else {
                var html = $(template).html() || '';
                $block = $(html.trim());

                if ($block.length > 1) {
                    $block = $block.filter('[data-role="matrix-block"]').first() || $block.first();
                }
            }

            if (!$block || !$block.length) {
                return null;
            }

            instance.$blocks.append($block);
            this.initMatrixBlock(instance, $block, data || {});
            this.updateMatrixEmptyState(instance);

            if (!skipSync) {
                this.syncMatrix(instance);
            }

            return $block;
        },

        initMatrixBlock: function (instance, $block, data) {
            if (!$block || !$block.length) {
                return;
            }

            if ($block.data('matrixInitialized')) {
                return;
            }

            $block.data('matrixInitialized', true);

            var self = this;
            var blockData = data || {};
            var type = blockData.type || $block.attr('data-block-type') || $block.data('blockType') || 'text';

            $block.attr('data-block-type', type);
            $block.data('blockType', type);
            $block.attr('data-matrix-uid', ++this.matrixBlockUid);

            var $typeInput = $block.find('[data-role="matrix-block-type"]').first();
            if ($typeInput.length) {
                $typeInput.val(type);
            }

            var $valueInput = $block.find('[data-role="matrix-block-value"]').first();
            if (!blockData.content && $valueInput.length && $valueInput.val()) {
                blockData.content = $valueInput.val();
            }

            var $editor = $block.find('[data-role="matrix-editor"]').first();
            if ($editor.length) {
                var quillOptions = $.extend(true, {}, self.getDefaultQuillOptions());
                var quillInstance = null;
                var changeHandler = null;

                if (typeof window.Quill !== 'undefined') {
                    quillInstance = new window.Quill($editor.get(0), quillOptions);

                    if (blockData.content) {
                        quillInstance.clipboard.dangerouslyPasteHTML(blockData.content);
                    }

                    changeHandler = function () {
                        self.syncMatrix(instance);
                    };

                    quillInstance.on('text-change', changeHandler);
                    $block.data('matrixQuill', quillInstance);
                    $block.data('matrixQuillChangeHandler', changeHandler);
                } else {
                    $editor.attr('contenteditable', 'true');

                    if (blockData.content) {
                        $editor.html(blockData.content);
                    }

                    var fallbackHandler = function () {
                        self.syncMatrix(instance);
                    };

                    $editor.on('input', fallbackHandler);
                    $block.data('matrixFallbackHandler', fallbackHandler);
                }
            }

            if ($valueInput.length && blockData.content) {
                $valueInput.val(blockData.content);
            }

            $block.trigger('matrix:block-initialized', [blockData, instance]);
        },

        removeMatrixBlock: function (instance, $block) {
            if (!$block || !$block.length) {
                return;
            }

            var quillInstance = $block.data('matrixQuill');
            var changeHandler = $block.data('matrixQuillChangeHandler');

            if (quillInstance && typeof quillInstance.off === 'function' && changeHandler) {
                quillInstance.off('text-change', changeHandler);
            }

            var fallbackHandler = $block.data('matrixFallbackHandler');
            var $editor = $block.find('[data-role="matrix-editor"]').first();
            if (fallbackHandler && $editor.length) {
                $editor.off('input', fallbackHandler);
            }

            $block.remove();
            this.updateMatrixEmptyState(instance);
            this.syncMatrix(instance);
        },

        updateMatrixEmptyState: function (instance) {
            if (!instance || !instance.$empty || !instance.$empty.length) {
                return;
            }

            var hasBlocks = instance.$blocks && instance.$blocks.children('[data-role="matrix-block"]').length > 0;
            instance.$empty.toggle(!hasBlocks);
        },

        syncMatrix: function (instance) {
            if (!instance || !instance.$blocks) {
                return;
            }

            var blocksData = [];

            instance.$blocks.children('[data-role="matrix-block"]').each(function (index) {
                var $block = $(this);
                var type = $block.data('blockType') || $block.attr('data-block-type') || 'text';
                var quillInstance = $block.data('matrixQuill');
                var content = '';

                if (quillInstance) {
                    content = quillInstance.root.innerHTML;
                } else {
                    var $valueInput = $block.find('[data-role="matrix-block-value"]').first();
                    if ($valueInput.length) {
                        content = $valueInput.val();
                    } else {
                        var $quillEditor = $block.find('.ql-editor').first();
                        if ($quillEditor.length) {
                            content = $quillEditor.html();
                        } else {
                            var $rawEditor = $block.find('[data-role="matrix-editor"]').first();
                            if ($rawEditor.length) {
                                content = $rawEditor.html();
                            }
                        }
                    }
                }

                blocksData.push({ type: type, content: content });

                var $typeInput = $block.find('[data-role="matrix-block-type"]').first();
                if ($typeInput.length) {
                    $typeInput.val(type).attr('name', 'matrix[' + index + '][type]');
                }

                var $valueInput = $block.find('[data-role="matrix-block-value"]').first();
                if ($valueInput.length) {
                    $valueInput.val(content).attr('name', 'matrix[' + index + '][content]');
                }
            });

            if (instance.$storage && instance.$storage.length) {
                var serialized = '';
                if (blocksData.length) {
                    try {
                        serialized = JSON.stringify(blocksData);
                    } catch (error) {
                        serialized = '';
                        if (window.console && window.console.warn) {
                            window.console.warn('Ошибка сериализации данных матрицы', error);
                        }
                    }
                }

                instance.$storage.val(serialized).trigger('change');
            }

            instance.$matrix.toggleClass('matrix--filled', blocksData.length > 0);
            instance.$matrix.trigger('matrix:change', [blocksData]);
        },

        createMatrixSortable: function (instance) {
            if (!instance || !instance.$blocks || !instance.$blocks.length) {
                return;
            }

            if (typeof window.Sortable === 'undefined') {
                return;
            }

            var element = instance.$blocks.get(0);
            if (!element) {
                return;
            }

            if (element._matrixSortableInstance) {
                return;
            }

            var self = this;
            element._matrixSortableInstance = window.Sortable.create(element, {
                animation: 150,
                handle: '[data-role="matrix-handle"]',
                ghostClass: 'matrix-block--ghost',
                dragClass: 'matrix-block--drag',
                onEnd: function () {
                    self.syncMatrix(instance);
                }
            });

            instance.sortable = element._matrixSortableInstance;
        },

        getMatrixTemplate: function ($matrix, type) {
            if (!$matrix || !$matrix.length) {
                return null;
            }

            var template = null;

            if (type) {
                template = $matrix.find('[data-role="matrix-template"][data-block-type="' + type + '"]').get(0);
            }

            if (!template) {
                template = $matrix.find('[data-role="matrix-template"]').get(0) || null;
            }

            return template;
        },

        getDefaultQuillOptions: function () {
            return {
                theme: 'snow',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ header: [1, 2, 3, false] }],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        ['blockquote', 'code-block'],
                        ['link'],
                        ['clean']
                    ]
                }
            };
        },

        initCodeMirror: function () {
            if (typeof window.CodeMirror === 'undefined') {
                return;
            }

            $('[data-role="code-editor"]').each(function () {
                var textarea = this;

                if ($(textarea).data('codemirrorInstance')) {
                    return;
                }

                var editor = window.CodeMirror.fromTextArea(textarea, {
                    mode: $(textarea).data('mode') || 'javascript',
                    lineNumbers: true,
                    readOnly: $(textarea).is('[readonly]') ? 'nocursor' : false
                });

                $(textarea).data('codemirrorInstance', editor);
            });
        },

        selectRow: function ($row) {
            var $table = $row.closest('table');
            $table.find('tbody tr').removeClass('dashboard-activity-selected');
            $row.addClass('dashboard-activity-selected');
            $row.find('input[type="checkbox"]').prop('checked', true);
            this.updatePreview($row);
        },

        updatePreview: function ($row) {
            var $preview = $('#activity-preview');
            if (!$preview.length) {
                return;
            }

            $preview.find('[data-preview-title]').text($row.data('title') || '—');
            $preview.find('[data-preview-description]').text($row.data('description') || '—');
            $preview.find('[data-preview-time]').text($row.data('timestamp') || '—');
        },

        initCollectionsTable: function () {
            var $table = $('#collections-table');
            if (!$table.length || !$.fn.DataTable) {
                return;
            }

            var endpoint = $table.data('endpoint');
            if (!endpoint) {
                return;
            }

            var self = this;
            this.collectionsTable = $table.DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                deferRender: true,
                ajax: {
                    url: endpoint,
                    data: function (params) {
                        var filters = self.getCollectionFilters();
                        params.status = filters.status;
                        params.structure = filters.structure;
                        params.search = filters.search;
                        params.view = filters.view;
                        params.selected = Object.keys(self.collectionsSelection);
                    }
                },
                dom: 't<"row"<"col-sm-6"l><"col-sm-6"p>>',
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                pageLength: 10,
                order: [[6, 'desc']],
                columns: [
                    { data: 'checkbox', orderable: false, searchable: false, width: '40px', className: 'text-center' },
                    { data: 'name', name: 'name' },
                    { data: 'handle', name: 'handle' },
                    { data: 'structure', name: 'structure' },
                    { data: 'entries', name: 'entries', className: 'text-right' },
                    { data: 'status', name: 'status' },
                    { data: 'updated', name: 'updated' }
                ],
                createdRow: function (row, data) {
                    $(row)
                        .attr('data-collection-id', data.id)
                        .attr('data-collection-handle', data.handle_raw || '')
                        .attr('data-collection-name', data.name_plain || data.name || '');

                    $(row)
                        .find('[data-role="collection-select"]')
                        .attr('data-id', data.id)
                        .attr('data-handle', data.handle_raw || '')
                        .attr('data-name', data.name_plain || data.name || '');
                },
                drawCallback: function () {
                    self.restoreCollectionsSelection($table);
                    self.updateCollectionsSelectionState();
                },
                language: {
                    processing: 'Загрузка…',
                    lengthMenu: 'Показать _MENU_ коллекций',
                    zeroRecords: 'Совпадений не найдено',
                    info: 'Показано _START_–_END_ из _TOTAL_ коллекций',
                    infoEmpty: 'Нет коллекций для отображения',
                    infoFiltered: '(отфильтровано из _MAX_)',
                    paginate: {
                        first: 'Первая',
                        previous: 'Назад',
                        next: 'Далее',
                        last: 'Последняя'
                    },
                    emptyTable: 'Нет коллекций для отображения'
                }
            });

            this.bindCollectionsTableEvents($table);
            this.updateCollectionsSelectionState();
        },

        bindCollectionsTableEvents: function ($table) {
            var self = this;

            $table.on('click', 'tbody tr', function (event) {
                if ($(event.target).is('input, label, a, button, select, option')) {
                    return;
                }

                var $checkbox = $(this).find('[data-role="collection-select"]');
                var checked = !$checkbox.prop('checked');
                $checkbox.prop('checked', checked).trigger('change');
            });

            $table.on('change', 'tbody [data-role="collection-select"]', function () {
                var $checkbox = $(this);
                var id = String($checkbox.attr('data-id') || '');
                if (!id) {
                    return;
                }

                if ($checkbox.prop('checked')) {
                    self.collectionsSelection[id] = {
                        id: id,
                        handle: $checkbox.attr('data-handle') || '',
                        name: $checkbox.attr('data-name') || ''
                    };
                } else {
                    delete self.collectionsSelection[id];
                }

                self.updateCollectionsSelectionState();
            });
        },

        restoreCollectionsSelection: function ($table) {
            var self = this;
            $table.find('tbody [data-role="collection-select"]').each(function () {
                var id = String($(this).attr('data-id') || '');
                if (id && self.collectionsSelection[id]) {
                    $(this).prop('checked', true);
                }
            });
        },

        updateCollectionsSelectionState: function () {
            var selected = this.getSelectedCollections();
            var count = selected.length;
            var $summary = $('[data-role="collections-selection-summary"]');
            if ($summary.length) {
                if (!count) {
                    $summary.text('Коллекции не выбраны');
                } else {
                    var names = selected.map(function (item) {
                        return item.name || ('#' + item.id);
                    });
                    var preview = names.slice(0, 3).join(', ');
                    if (names.length > 3) {
                        preview += ' и ещё ' + (names.length - 3);
                    }
                    $summary.text('Выбрано коллекций: ' + count + (preview ? ' (' + preview + ')' : ''));
                }
            }

            $('[data-requires-selection]').prop('disabled', count === 0);

            var $table = $('#collections-table');
            if ($table.length) {
                this.syncCollectionsSelectAllState($table);
            }

            var $feedback = $('[data-role="collections-bulk-feedback"]');
            if ($feedback.length && count === 0) {
                $feedback.text('').removeClass('text-danger text-success');
            }
        },

        syncCollectionsSelectAllState: function ($table) {
            var $selectAll = $table.find('thead [data-role="select-all"]');
            if (!$selectAll.length) {
                return;
            }

            var total = $table.find('tbody [data-role="collection-select"]').length;
            var selected = $table.find('tbody [data-role="collection-select"]:checked').length;

            if (!total) {
                $selectAll.prop('checked', false).prop('indeterminate', false);
                return;
            }

            if (selected === total) {
                $selectAll.prop('checked', true).prop('indeterminate', false);
            } else if (selected > 0) {
                $selectAll.prop('checked', false).prop('indeterminate', true);
            } else {
                $selectAll.prop('checked', false).prop('indeterminate', false);
            }
        },
        getCollectionFilters: function () {
            var $search = $('#collections-search');
            var $status = $('#collections-status');
            var $structure = $('#collections-structure');

            return {
                search: $search.length ? String($search.val() || '').trim() : '',
                status: $status.length ? String($status.val() || '') : '',
                structure: $structure.length ? String($structure.val() || '') : '',
                view: this.currentSavedViewId || ''
            };
        },

        bindCollectionsFilters: function () {
            var self = this;
            var $table = $('#collections-table');
            if (!$table.length) {
                return;
            }

            var $search = $('#collections-search');
            var $status = $('#collections-status');
            var $structure = $('#collections-structure');

            var debounceTimer = null;
            var reload = function () {
                self.reloadCollectionsTable(true);
            };

            if ($search.length) {
                $search.on('input', function () {
                    if (!self.isApplyingSavedView) {
                        self.clearCurrentSavedView();
                    }

                    window.clearTimeout(debounceTimer);
                    debounceTimer = window.setTimeout(reload, 250);
                });
            }

            var handleSelectChange = function () {
                if (!self.isApplyingSavedView) {
                    self.clearCurrentSavedView();
                }

                reload();
            };

            if ($status.length) {
                $status.on('change', handleSelectChange);
            }

            if ($structure.length) {
                $structure.on('change', handleSelectChange);
            }

            $("[data-action='reset-filters']").on('click', function (event) {
                event.preventDefault();

                if ($search.length) {
                    $search.val('');
                }

                if ($status.length) {
                    $status.val('');
                    if ($status.data('select2')) {
                        $status.trigger('change.select2');
                    }
                }

                if ($structure.length) {
                    $structure.val('');
                    if ($structure.data('select2')) {
                        $structure.trigger('change.select2');
                    }
                }

                self.clearCurrentSavedView();
                reload();
            });
        },

        reloadCollectionsTable: function (resetPaging, preserveSelection) {
            if (!preserveSelection) {
                this.collectionsSelection = {};
            }

            if (this.collectionsTable) {
                this.collectionsTable.ajax.reload(null, resetPaging !== false);
            }

            if (!preserveSelection) {
                this.updateCollectionsSelectionState();
            }
        },

        clearCurrentSavedView: function () {
            if (this.isApplyingSavedView) {
                return;
            }

            if (!this.currentSavedViewId) {
                return;
            }

            this.currentSavedViewId = null;
            var $select = $("[data-role='collections-saved-view']");
            if ($select.length) {
                $select.val('');
                if ($select.data('select2')) {
                    $select.trigger('change.select2');
                }
            }
        },

        initCollectionsSavedViews: function () {
            var $select = $("[data-role='collections-saved-view']");
            if (!$select.length) {
                return;
            }

            this.collectionSavedViews = this.loadCollectionsSavedViews();
            this.renderCollectionsSavedViews();

            var self = this;

            $select.on('change', function () {
                var value = $(this).val();
                if (!value) {
                    self.currentSavedViewId = null;
                    return;
                }

                var view = self.findCollectionsSavedView(String(value));
                if (!view) {
                    return;
                }

                self.currentSavedViewId = view.id;
                self.applyCollectionsSavedView(view);
            });

            $(document).on('click', "[data-action='save-current-view']", function (event) {
                event.preventDefault();
                self.createCollectionsSavedView();
            });
        },

        loadCollectionsSavedViews: function () {
            var storageKey = 'dashboard.collections.savedViews';
            if (!window.localStorage) {
                return [];
            }

            try {
                var raw = window.localStorage.getItem(storageKey);
                if (!raw) {
                    return [];
                }

                var parsed = JSON.parse(raw);
                if (!Array.isArray(parsed)) {
                    return [];
                }

                return parsed.filter(function (item) {
                    return item && typeof item.id === 'string' && typeof item.name === 'string' && item.filters;
                });
            } catch (error) {
                console.warn('Не удалось загрузить сохранённые виды коллекций', error);
                return [];
            }
        },

        renderCollectionsSavedViews: function () {
            var $select = $("[data-role='collections-saved-view']");
            if (!$select.length) {
                return;
            }

            var current = this.currentSavedViewId;
            var options = ["<option value=''>Текущий фильтр</option>"];
            for (var i = 0; i < this.collectionSavedViews.length; i++) {
                var view = this.collectionSavedViews[i];
                options.push('<option value="' + this.escapeHtml(view.id) + '">' + this.escapeHtml(view.name) + '</option>');
            }

            $select.html(options.join(''));
            if (current) {
                $select.val(current);
            } else {
                $select.val('');
            }

            if ($select.data('select2')) {
                $select.trigger('change.select2');
            }
        },

        persistCollectionsSavedViews: function () {
            if (!window.localStorage) {
                return;
            }

            try {
                window.localStorage.setItem('dashboard.collections.savedViews', JSON.stringify(this.collectionSavedViews));
            } catch (error) {
                console.warn('Не удалось сохранить Saved View', error);
            }
        },

        applyCollectionsSavedView: function (view) {
            if (!view || !view.filters) {
                return;
            }

            var filters = view.filters;
            var $search = $('#collections-search');
            var $status = $('#collections-status');
            var $structure = $('#collections-structure');

            this.isApplyingSavedView = true;

            if ($search.length) {
                $search.val(filters.search || '');
            }

            if ($status.length) {
                $status.val(filters.status || '');
                if ($status.data('select2')) {
                    $status.trigger('change.select2');
                }
            }

            if ($structure.length) {
                $structure.val(filters.structure || '');
                if ($structure.data('select2')) {
                    $structure.trigger('change.select2');
                }
            }

            this.isApplyingSavedView = false;
            this.reloadCollectionsTable(true);
            this.renderCollectionsSavedViews();
        },

        createCollectionsSavedView: function () {
            var name = window.prompt('Название сохранённого вида', 'Новый вид');
            if (!name) {
                return;
            }

            name = String(name).trim();
            if (!name) {
                return;
            }

            var filters = this.getCollectionFilters();
            delete filters.view;

            var view = {
                id: 'view-' + Date.now(),
                name: name,
                filters: filters
            };

            this.collectionSavedViews.push(view);
            this.currentSavedViewId = view.id;
            this.persistCollectionsSavedViews();
            this.renderCollectionsSavedViews();
            this.applyCollectionsSavedView(view);
        },

        findCollectionsSavedView: function (id) {
            for (var i = 0; i < this.collectionSavedViews.length; i++) {
                if (this.collectionSavedViews[i].id === id) {
                    return this.collectionSavedViews[i];
                }
            }

            return null;
        },

        initSchemasModule: function () {
            var $container = $('[data-role="schemas"]');
            if (!$container.length) {
                return;
            }

            this.schemasDataset = this.readSchemasDatasetFromDom();
            this.schemasDefaultSavedViews = this.readSchemasDefaultSavedViewsFromDom();
            this.schemasSavedViews = this.loadSchemasSavedViews();
            this.schemasCurrentViewId = null;
            this.schemasCurrentSelectionId = null;
            this.schemasSelectFirstAfterRender = false;
            this.isApplyingSchemasView = false;

            this.renderSchemasCollectionFilterOptions();
            this.bindSchemasTableEvents();
            this.bindSchemasFilters();
            this.initSchemasSavedViews();
            this.renderSchemasTable();
        },

        readSchemasDatasetFromDom: function () {
            var $script = $('[data-role="schemas-dataset"]');
            if (!$script.length) {
                return [];
            }

            try {
                var raw = $script.text() || '[]';
                var parsed = JSON.parse(raw);
                if (!Array.isArray(parsed)) {
                    return [];
                }

                return parsed
                    .filter(function (item) {
                        return item && typeof item.id === 'string' && typeof item.name === 'string';
                    })
                    .map(function (item) {
                        var copy = $.extend(true, {}, item);
                        if (!Array.isArray(copy.fields)) {
                            copy.fields = [];
                        }
                        return copy;
                    });
            } catch (error) {
                console.warn('Не удалось разобрать набор схем', error);
                return [];
            }
        },

        readSchemasDefaultSavedViewsFromDom: function () {
            var $script = $('[data-role="schemas-default-saved-views"]');
            if (!$script.length) {
                return [];
            }

            try {
                var raw = $script.text() || '[]';
                var parsed = JSON.parse(raw);
                if (!Array.isArray(parsed)) {
                    return [];
                }

                return parsed.filter(function (item) {
                    return item && typeof item.id === 'string' && typeof item.name === 'string' && item.filters;
                });
            } catch (error) {
                console.warn('Не удалось разобрать предустановленные Saved Views схем', error);
                return [];
            }
        },

        bindSchemasTableEvents: function () {
            var self = this;
            var $table = $('[data-role="schemas-table"]');
            if (!$table.length) {
                return;
            }

            $table.on('click', 'tbody tr[data-schema-id]', function (event) {
                if ($(event.target).is('a, button, input, label, select')) {
                    return;
                }

                var id = String($(this).attr('data-schema-id') || '');
                if (!id) {
                    return;
                }

                self.selectSchemaById(id);
            });
        },

        renderSchemasCollectionFilterOptions: function () {
            var $select = $('#schemas-collection');
            if (!$select.length) {
                return;
            }

            var previous = String($select.val() || '');
            var options = ['<option value="">Все коллекции</option>'];
            var seen = {};

            for (var i = 0; i < this.schemasDataset.length; i++) {
                var schema = this.schemasDataset[i];
                if (!schema) {
                    continue;
                }

                var collection = schema.collection || {};
                var handle = String(collection.handle || '');
                if (!handle || seen[handle]) {
                    continue;
                }

                seen[handle] = true;
                var label = collection.name || handle;
                options.push('<option value="' + this.escapeHtml(handle) + '">' + this.escapeHtml(label) + '</option>');
            }

            $select.html(options.join(''));

            if (previous && seen[previous]) {
                $select.val(previous);
            } else {
                $select.val('');
            }

            if ($select.data('select2')) {
                $select.trigger('change.select2');
            }
        },

        getSchemaFilters: function () {
            var $search = $('#schemas-search');
            var $collection = $('#schemas-collection');

            return {
                search: $search.length ? String($search.val() || '').trim() : '',
                collection: $collection.length ? String($collection.val() || '') : '',
                selected: this.schemasCurrentSelectionId || '',
                view: this.schemasCurrentViewId || ''
            };
        },

        filterSchemasDataset: function (filters) {
            var search = String(filters.search || '').toLowerCase();
            var collection = String(filters.collection || '');

            var items = [];

            for (var i = 0; i < this.schemasDataset.length; i++) {
                var schema = this.schemasDataset[i];
                if (!schema) {
                    continue;
                }

                if (collection) {
                    var handle = schema.collection && schema.collection.handle ? String(schema.collection.handle) : '';
                    if (handle !== collection) {
                        continue;
                    }
                }

                if (search) {
                    var haystackParts = [];
                    haystackParts.push(schema.name || '');
                    if (schema.collection && schema.collection.name) {
                        haystackParts.push(schema.collection.name);
                    }
                    if (Array.isArray(schema.tags)) {
                        haystackParts = haystackParts.concat(schema.tags);
                    }
                    if (Array.isArray(schema.fields)) {
                        for (var j = 0; j < schema.fields.length; j++) {
                            var field = schema.fields[j] || {};
                            if (field.name) {
                                haystackParts.push(field.name);
                            }
                            if (field.handle) {
                                haystackParts.push(field.handle);
                            }
                            if (field.type) {
                                haystackParts.push(field.type);
                            }
                        }
                    }

                    var haystack = haystackParts.join(' ').toLowerCase();
                    if (haystack.indexOf(search) === -1) {
                        continue;
                    }
                }

                items.push(schema);
            }

            items.sort(function (a, b) {
                var aTime = a && a.updatedIso ? Date.parse(a.updatedIso) : 0;
                var bTime = b && b.updatedIso ? Date.parse(b.updatedIso) : 0;

                if (isNaN(aTime)) {
                    aTime = 0;
                }
                if (isNaN(bTime)) {
                    bTime = 0;
                }

                if (aTime === bTime) {
                    return (a.name || '').localeCompare(b.name || '', 'ru');
                }

                return bTime - aTime;
            });

            return items;
        },

        renderSchemasTable: function () {
            var $table = $('[data-role="schemas-table"]');
            if (!$table.length) {
                return;
            }

            var filters = this.getSchemaFilters();
            var items = this.filterSchemasDataset(filters);
            var $tbody = $table.find('tbody');
            $tbody.empty();

            if (!items.length) {
                $tbody.append('<tr class="empty"><td colspan="3" class="text-center text-muted">Подходящих схем не найдено.</td></tr>');
            } else {
                for (var i = 0; i < items.length; i++) {
                    var schema = items[i];
                    var collectionName = schema.collection && schema.collection.name ? schema.collection.name : '—';
                    var updated = schema.updated || schema.updatedLabel || schema.updatedIso || '—';
                    var rowHtml = '' +
                        '<tr data-schema-id="' + this.escapeHtml(schema.id) + '">' +
                        '<td>' + this.escapeHtml(schema.name || '—') + '</td>' +
                        '<td class="hidden-xs">' + this.escapeHtml(collectionName) + '</td>' +
                        '<td class="hidden-xs">' + this.escapeHtml(updated) + '</td>' +
                        '</tr>';
                    $tbody.append(rowHtml);
                }
            }

            var selectionId = String(this.schemasCurrentSelectionId || '');
            var hasSelection = selectionId && items.some(function (schema) {
                return schema && schema.id === selectionId;
            });

            if (!hasSelection) {
                if (this.schemasSelectFirstAfterRender && items.length) {
                    this.schemasCurrentSelectionId = items[0].id;
                } else {
                    this.schemasCurrentSelectionId = '';
                }
            }

            if (!items.length) {
                this.schemasCurrentSelectionId = '';
            }

            this.schemasSelectFirstAfterRender = false;

            this.highlightSchemasSelection();
            this.updateSchemaPreview();
        },

        highlightSchemasSelection: function () {
            var $table = $('[data-role="schemas-table"]');
            if (!$table.length) {
                return;
            }

            var $rows = $table.find('tbody tr');
            $rows.removeClass('info');

            var selectionId = String(this.schemasCurrentSelectionId || '');
            if (!selectionId) {
                return;
            }

            var selector = '[data-schema-id="' + selectionId.replace(/"/g, '\\"') + '"]';
            $rows.filter(selector).addClass('info');
        },

        selectSchemaById: function (id) {
            var schema = this.findSchemaById(id);
            if (!schema) {
                return;
            }

            this.schemasCurrentSelectionId = schema.id;
            this.highlightSchemasSelection();
            this.updateSchemaPreview();
        },

        findSchemaById: function (id) {
            var needle = String(id || '');
            if (!needle) {
                return null;
            }

            for (var i = 0; i < this.schemasDataset.length; i++) {
                if (this.schemasDataset[i] && this.schemasDataset[i].id === needle) {
                    return this.schemasDataset[i];
                }
            }

            return null;
        },

        updateSchemaPreview: function () {
            var schema = this.findSchemaById(this.schemasCurrentSelectionId);
            var $placeholder = $('[data-role="schema-preview-placeholder"]');
            var $content = $('[data-role="schema-preview-content"]');
            var $name = $('[data-role="schema-preview-name"]');
            var $description = $('[data-role="schema-preview-description"]');
            var $collection = $('[data-role="schema-preview-collection"]');
            var $updated = $('[data-role="schema-preview-updated"]');
            var $fieldsCount = $('[data-role="schema-preview-fields-count"]');
            var $fields = $('[data-role="schema-fields"]');
            var $editButton = $('[data-role="edit-schema"]');

            if (!schema) {
                if ($placeholder.length) {
                    $placeholder.removeClass('hidden');
                }
                if ($content.length) {
                    $content.addClass('hidden');
                }
                if ($fields.length) {
                    $fields.empty().append('<li class="list-group-item text-muted">Поля будут показаны здесь.</li>');
                }
                if ($editButton.length) {
                    $editButton.addClass('disabled').attr('href', '#').attr('aria-disabled', 'true');
                }
                return;
            }

            if ($placeholder.length) {
                $placeholder.addClass('hidden');
            }
            if ($content.length) {
                $content.removeClass('hidden');
            }

            if ($name.length) {
                $name.text(schema.name || '—');
            }

            if ($description.length) {
                var description = schema.description || '';
                if (description) {
                    $description.text(description).removeClass('hidden');
                } else {
                    $description.text('').addClass('hidden');
                }
            }

            var collectionName = schema.collection && schema.collection.name ? schema.collection.name : '—';
            if ($collection.length) {
                $collection.text(collectionName);
            }

            var updatedLabel = schema.updated || schema.updatedLabel || schema.updatedIso || '—';
            if ($updated.length) {
                $updated.text(updatedLabel || '—');
            }

            if ($fieldsCount.length) {
                var count = Array.isArray(schema.fields) ? schema.fields.length : 0;
                $fieldsCount.text(count ? count : '—');
            }

            this.renderSchemaFields(schema, $fields);

            if ($editButton.length) {
                if (schema.editUrl) {
                    $editButton.removeClass('disabled').attr('href', schema.editUrl).removeAttr('aria-disabled');
                } else {
                    $editButton.addClass('disabled').attr('href', '#').attr('aria-disabled', 'true');
                }
                $editButton.attr('data-schema-id', schema.id || '');
            }
        },

        renderSchemaFields: function (schema, $container) {
            if (!$container || !$container.length) {
                return;
            }

            $container.empty();

            var fields = schema && Array.isArray(schema.fields) ? schema.fields : [];
            if (!fields.length) {
                $container.append('<li class="list-group-item text-muted">Поля будут показаны здесь.</li>');
                return;
            }

            for (var i = 0; i < fields.length; i++) {
                var field = fields[i] || {};
                var name = field.name || 'Поле';
                var handle = field.handle ? ' <span class="text-muted">@' + this.escapeHtml(String(field.handle)) + '</span>' : '';
                var badges = [];

                if (field.type) {
                    badges.push('<span class="label label-default">' + this.escapeHtml(String(field.type)) + '</span>');
                }
                if (field.required) {
                    badges.push('<span class="label label-warning">обязательное</span>');
                }
                if (field.localized) {
                    badges.push('<span class="label label-info">локализация</span>');
                }
                if (field.multiple) {
                    badges.push('<span class="label label-primary">множественное</span>');
                }

                var badgesHtml = badges.length ? '<span class="pull-right schema-field-badges">' + badges.join(' ') + '</span>' : '';
                var description = field.description ? '<div class="text-muted small">' + this.escapeHtml(String(field.description)) + '</div>' : '';

                var itemHtml = '<li class="list-group-item">' +
                    '<div class="schema-field-header">' +
                    '<strong>' + this.escapeHtml(String(name)) + '</strong>' +
                    handle +
                    badgesHtml +
                    '</div>' +
                    description +
                    '</li>';

                $container.append(itemHtml);
            }
        },

        bindSchemasFilters: function () {
            var self = this;
            var $search = $('#schemas-search');
            var $collection = $('#schemas-collection');

            var debounceTimer = null;

            if ($search.length) {
                $search.on('input', function () {
                    if (!self.isApplyingSchemasView) {
                        self.clearSchemasSavedView();
                    }

                    window.clearTimeout(debounceTimer);
                    debounceTimer = window.setTimeout(function () {
                        self.renderSchemasTable();
                    }, 250);
                });
            }

            if ($collection.length) {
                $collection.on('change', function () {
                    if (!self.isApplyingSchemasView) {
                        self.clearSchemasSavedView();
                    }

                    self.renderSchemasTable();
                });
            }

            $(document).on('click', '[data-action="schemas-reset-filters"]', function (event) {
                event.preventDefault();

                if ($search.length) {
                    $search.val('');
                }

                if ($collection.length) {
                    $collection.val('');
                    if ($collection.data('select2')) {
                        $collection.trigger('change.select2');
                    } else {
                        $collection.trigger('change');
                    }
                }

                self.clearSchemasSavedView();
                self.renderSchemasTable();
            });
        },

        clearSchemasSavedView: function () {
            if (this.isApplyingSchemasView) {
                return;
            }

            if (!this.schemasCurrentViewId) {
                return;
            }

            this.schemasCurrentViewId = null;
            var $select = $('[data-role="schemas-saved-view"]');
            if ($select.length) {
                $select.val('');
                if ($select.data('select2')) {
                    $select.trigger('change.select2');
                }
            }
        },

        initSchemasSavedViews: function () {
            var $select = $('[data-role="schemas-saved-view"]');
            if (!$select.length) {
                return;
            }

            this.renderSchemasSavedViews();

            var self = this;

            $select.on('change', function () {
                var id = String($(this).val() || '');
                if (!id) {
                    self.schemasCurrentViewId = null;
                    return;
                }

                var view = self.findSchemasSavedView(id);
                if (!view) {
                    return;
                }

                self.schemasCurrentViewId = view.id;
                self.applySchemasSavedView(view);
            });

            $(document).on('click', '[data-action="schemas-save-view"]', function (event) {
                event.preventDefault();
                self.createSchemasSavedView();
            });

            $(document).on('click', '[data-action="schemas-delete-view"]', function (event) {
                event.preventDefault();
                self.deleteSchemasSavedView();
            });
        },

        getAllSchemasSavedViews: function () {
            var result = [];
            var seen = {};

            var append = function (view) {
                if (!view || typeof view.id !== 'string') {
                    return;
                }
                if (seen[view.id]) {
                    return;
                }
                seen[view.id] = true;
                result.push(view);
            };

            for (var i = 0; i < this.schemasDefaultSavedViews.length; i++) {
                append(this.schemasDefaultSavedViews[i]);
            }

            for (var j = 0; j < this.schemasSavedViews.length; j++) {
                append(this.schemasSavedViews[j]);
            }

            return result;
        },

        renderSchemasSavedViews: function () {
            var $select = $('[data-role="schemas-saved-view"]');
            if (!$select.length) {
                return;
            }

            var current = this.schemasCurrentViewId;
            var options = ["<option value=''>Текущий фильтр</option>"];
            var views = this.getAllSchemasSavedViews();

            for (var i = 0; i < views.length; i++) {
                var view = views[i];
                options.push('<option value="' + this.escapeHtml(view.id) + '">' + this.escapeHtml(view.name) + '</option>');
            }

            $select.html(options.join(''));

            if (current) {
                $select.val(current);
            } else {
                $select.val('');
            }

            if ($select.data('select2')) {
                $select.trigger('change.select2');
            }
        },

        loadSchemasSavedViews: function () {
            if (!window.localStorage) {
                return [];
            }

            try {
                var raw = window.localStorage.getItem(this.schemasStorageKey);
                if (!raw) {
                    return [];
                }

                var parsed = JSON.parse(raw);
                if (!Array.isArray(parsed)) {
                    return [];
                }

                return parsed.filter(function (item) {
                    return item && typeof item.id === 'string' && typeof item.name === 'string' && item.filters;
                });
            } catch (error) {
                console.warn('Не удалось загрузить Saved Views схем', error);
                return [];
            }
        },

        persistSchemasSavedViews: function () {
            if (!window.localStorage) {
                return;
            }

            try {
                window.localStorage.setItem(this.schemasStorageKey, JSON.stringify(this.schemasSavedViews));
            } catch (error) {
                console.warn('Не удалось сохранить Saved Views схем', error);
            }

            this.syncSchemasSavedViews();
        },

        findSchemasSavedView: function (id) {
            var views = this.getAllSchemasSavedViews();
            for (var i = 0; i < views.length; i++) {
                if (views[i].id === id) {
                    return views[i];
                }
            }

            return null;
        },

        applySchemasSavedView: function (view) {
            if (!view || !view.filters) {
                return;
            }

            var filters = view.filters;
            var $search = $('#schemas-search');
            var $collection = $('#schemas-collection');

            this.isApplyingSchemasView = true;

            if ($search.length) {
                $search.val(filters.search || '');
            }

            if ($collection.length) {
                var value = filters.collection || '';
                $collection.val(value);
                if ($collection.data('select2')) {
                    $collection.trigger('change.select2');
                } else {
                    $collection.trigger('change');
                }
            }

            var selection = String(filters.selected || filters.schema || '');
            this.schemasCurrentSelectionId = selection;

            this.isApplyingSchemasView = false;

            this.schemasSelectFirstAfterRender = !selection;

            this.renderSchemasTable();
            this.renderSchemasSavedViews();
        },

        createSchemasSavedView: function () {
            var name = window.prompt('Название сохранённого вида', 'Новый вид');
            if (!name) {
                return;
            }

            name = String(name).trim();
            if (!name) {
                return;
            }

            var filters = this.getSchemaFilters();
            delete filters.view;
            filters.selected = this.schemasCurrentSelectionId || '';

            var view = {
                id: 'schema-view-' + Date.now(),
                name: name,
                filters: filters
            };

            this.schemasSavedViews.push(view);
            this.schemasCurrentViewId = view.id;
            this.persistSchemasSavedViews();
            this.renderSchemasSavedViews();
            this.applySchemasSavedView(view);
        },

        deleteSchemasSavedView: function () {
            if (!this.schemasCurrentViewId) {
                window.alert('Выберите сохранённый вид для удаления.');
                return;
            }

            var id = this.schemasCurrentViewId;
            var isDefault = this.schemasDefaultSavedViews.some(function (item) {
                return item && item.id === id;
            });

            if (isDefault) {
                window.alert('Нельзя удалить предустановленный Saved View.');
                return;
            }

            var initialLength = this.schemasSavedViews.length;
            this.schemasSavedViews = this.schemasSavedViews.filter(function (item) {
                return item && item.id !== id;
            });

            if (initialLength === this.schemasSavedViews.length) {
                window.alert('Сохранённый вид не найден.');
                return;
            }

            this.schemasCurrentViewId = null;
            this.persistSchemasSavedViews();
            this.renderSchemasSavedViews();
            this.renderSchemasTable();
        },

        syncSchemasSavedViews: function () {
            var endpoint = window.cmsSchemasSavedViewsEndpoint;
            if (!endpoint) {
                return;
            }

            $.ajax({
                url: endpoint,
                method: 'POST',
                dataType: 'json',
                contentType: 'application/json',
                data: JSON.stringify({ views: this.schemasSavedViews })
            }).fail(function (error) {
                console.warn('Не удалось синхронизировать Saved Views схем', error);
            });
        },

        getSelectedCollections: function () {
            var result = [];
            var keys = Object.keys(this.collectionsSelection);
            for (var i = 0; i < keys.length; i++) {
                result.push(this.collectionsSelection[keys[i]]);
            }

            return result;
        },

        getFirstSelectedCollection: function () {
            var selected = this.getSelectedCollections();
            return selected.length ? selected[0] : null;
        },

        bindCollectionsActionBar: function () {
            var self = this;

            $(document).on('click', "[data-action='collection-open']", function (event) {
                event.preventDefault();

                var selected = self.getFirstSelectedCollection();
                if (!selected || !selected.handle) {
                    return;
                }

                window.location.href = '/dashboard/collections/entries?handle=' + encodeURIComponent(selected.handle);
            });

            $(document).on('click', "[data-action='collection-edit']", function (event) {
                event.preventDefault();

                var selected = self.getFirstSelectedCollection();
                if (!selected || !selected.handle) {
                    return;
                }

                window.location.href = '/dashboard/collections/settings?handle=' + encodeURIComponent(selected.handle);
            });
        },

        bindCollectionsBulkActions: function () {
            var self = this;
            var $table = $('#collections-table');
            if (!$table.length) {
                return;
            }

            var $bulkSelect = $("[data-role='collections-bulk']");
            var $feedback = $("[data-role='collections-bulk-feedback']");

            $("[data-action='bulk-apply']").on('click', function (event) {
                event.preventDefault();

                if (!$bulkSelect.length) {
                    return;
                }

                var action = String($bulkSelect.val() || '');
                var selected = self.getSelectedCollections();

                if (!action) {
                    if ($feedback.length) {
                        $feedback
                            .removeClass('text-success')
                            .addClass('text-danger')
                            .text('Выберите массовое действие.');
                    }
                    return;
                }

                if (!selected.length) {
                    if ($feedback.length) {
                        $feedback
                            .removeClass('text-success')
                            .addClass('text-danger')
                            .text('Выберите хотя бы одну коллекцию для применения действия.');
                    }
                    return;
                }

                var names = selected.map(function (item) {
                    return item.name || ('#' + item.id);
                });
                var preview = names.slice(0, 3).join(', ');
                if (names.length > 3) {
                    preview += ' и ещё ' + (names.length - 3);
                }

                if ($feedback.length) {
                    $feedback
                        .removeClass('text-danger')
                        .addClass('text-success')
                        .text('Действие «' + action + '» будет применено к ' + selected.length + ' коллекциям: ' + preview + '.');
                }

                console.info('Collections bulk action', action, selected);
            });
        },

        escapeHtml: function (value) {
            if (value === null || typeof value === 'undefined') {
                return '';
            }

            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        },

        bindSelectAll: function () {
            $(document).on('change', '[data-role="select-all"]', function () {
                var checked = $(this).prop('checked');
                $(this)
                    .closest('table')
                    .find('tbody input[type="checkbox"]').each(function () {
                        $(this).prop('checked', checked).trigger('change');
                    });
            });
        },

        bindFiltering: function () {
            var self = this;
            var $filter = $('#activity-type-filter');
            if (!$filter.length) {
                return;
            }

            $filter.on('change', function () {
                if (self.activityTable) {
                    self.activityTable.draw();
                }
            });

            $('[data-action="reset-filter"]').on('click', function () {
                $filter.val(null).trigger('change');
                if (self.activityTable) {
                    self.activityTable.draw();
                }
            });
        },

        initElementAutosaveModule: function () {
            var $container = $('[data-role="element-form"]');
            if (!$container.length) {
                return;
            }

            if ($container.data('elementAutosaveInitialized')) {
                return;
            }

            $container.data('elementAutosaveInitialized', true);

            this.elementAutosaveContainer = $container;
            this.elementAutosaveStatusElement = null;
            this.elementAutosaveMessagesCache = null;
            this.elementAutosaveTimer = null;
            this.elementAutosavePending = false;
            this.elementAutosaveDirty = false;
            this.elementAutosaveSaving = false;
            this.elementAutosaveRevision = 0;
            this.elementAutosaveCurrentPromise = null;
            this.elementAutosaveLastSaved = null;
            this.elementAutosaveReady = false;
            this.elementAutosaveStatus = 'clean';

            var storageKey = String($container.attr('data-autosave-storage-key') || '');
            if (storageKey) {
                this.elementAutosaveStorageKey = storageKey;
            }

            var debounceAttr = parseInt($container.attr('data-autosave-debounce'), 10);
            if (!isNaN(debounceAttr) && debounceAttr > 0) {
                this.elementAutosaveDebounce = debounceAttr;
            }

            this.bindElementAutosaveActions($container);

            var self = this;
            var changeSelector = 'input, textarea, select, [contenteditable="true"]';

            $container.on('input change', changeSelector, function (event) {
                var $target = $(event.target);
                if ($target.is('[type="button"], [type="submit"], [type="reset"]')) {
                    return;
                }
                if ($target.is(':disabled')) {
                    return;
                }

                self.markElementFormDirty();
            });

            $container.on('matrix:change', '[data-role="matrix"]', function () {
                self.markElementFormDirty();
            });

            window.setTimeout(function () {
                self.elementAutosaveReady = true;
            }, 0);

            this.updateElementAutosaveStatus('clean');
        },

        bindElementAutosaveActions: function ($container) {
            if (!$container || !$container.length) {
                return;
            }

            var self = this;
            var selectors = '[data-action="save-draft"], [data-action="publish-element"]';

            $container.on('click', selectors, function () {
                self.performElementAutosave({ force: true });
            });
        },

        markElementFormDirty: function () {
            if (!this.elementAutosaveContainer || !this.elementAutosaveContainer.length) {
                return;
            }

            if (!this.elementAutosaveReady) {
                return;
            }

            this.elementAutosaveRevision += 1;
            this.elementAutosaveDirty = true;
            this.updateElementAutosaveStatus('dirty');
            this.scheduleElementAutosave();
        },

        scheduleElementAutosave: function () {
            if (!this.elementAutosaveDirty) {
                return;
            }

            if (this.elementAutosaveSaving) {
                this.elementAutosavePending = true;
                return;
            }

            var self = this;
            this.elementAutosavePending = false;
            window.clearTimeout(this.elementAutosaveTimer);
            this.elementAutosaveTimer = window.setTimeout(function () {
                self.performElementAutosave();
            }, this.elementAutosaveDebounce);
        },

        performElementAutosave: function (options) {
            options = options || {};
            var forced = !!options.force;

            if (!this.elementAutosaveContainer || !this.elementAutosaveContainer.length) {
                return $.Deferred().resolve().promise();
            }

            if (!forced && !this.elementAutosaveDirty) {
                return $.Deferred().resolve().promise();
            }

            if (this.elementAutosaveSaving) {
                if (forced) {
                    this.elementAutosavePending = true;
                }

                return this.elementAutosaveCurrentPromise || $.Deferred().resolve().promise();
            }

            this.elementAutosavePending = false;
            window.clearTimeout(this.elementAutosaveTimer);
            this.elementAutosaveTimer = null;

            var payload = this.collectElementFormData(this.elementAutosaveContainer);
            var revision = this.elementAutosaveRevision;

            this.elementAutosaveSaving = true;
            this.updateElementAutosaveStatus('saving');

            var self = this;
            var promise = this.saveElementDraft(payload);

            this.elementAutosaveCurrentPromise = promise;

            return promise.done(function (savedAt) {
                if (self.elementAutosaveRevision !== revision) {
                    self.elementAutosaveDirty = true;
                    self.elementAutosaveLastSaved = savedAt instanceof Date ? savedAt : new Date();
                    self.updateElementAutosaveStatus('dirty');
                    return;
                }

                self.elementAutosaveDirty = false;
                self.elementAutosaveLastSaved = savedAt instanceof Date ? savedAt : new Date();
                self.updateElementAutosaveStatus('saved', { savedAt: self.elementAutosaveLastSaved });
            }).fail(function () {
                self.elementAutosaveDirty = true;
                self.updateElementAutosaveStatus('error');
            }).always(function () {
                self.elementAutosaveSaving = false;
                self.elementAutosaveCurrentPromise = null;

                if (self.elementAutosaveDirty) {
                    self.scheduleElementAutosave();
                }

                self.elementAutosavePending = false;
            });
        },

        collectElementFormData: function ($container) {
            var data = {};
            if (!$container || !$container.length) {
                return data;
            }

            var $fields = $container.find('input, textarea, select').filter(function () {
                var $field = $(this);
                if ($field.is('[type="button"], [type="submit"], [type="reset"]')) {
                    return false;
                }
                if ($field.is(':disabled')) {
                    return false;
                }

                return true;
            });

            $fields.each(function (index) {
                var $field = $(this);
                var key = $field.attr('name') || $field.attr('id') || $field.data('autosaveKey') || '';
                if (!key) {
                    var role = $field.attr('data-role');
                    if (role) {
                        key = 'role:' + role;
                    }
                }
                if (!key) {
                    key = 'field-' + index;
                }

                var value;
                if ($field.is(':checkbox')) {
                    var checkboxValue = $field.val();
                    if (checkboxValue && checkboxValue !== 'on' && checkboxValue !== '1' && checkboxValue !== 'true') {
                        value = $field.prop('checked') ? checkboxValue : '';
                    } else {
                        value = $field.prop('checked');
                    }
                } else if ($field.is(':radio')) {
                    if (!$field.prop('checked')) {
                        return;
                    }
                    value = $field.val();
                } else if ($field.is('select') && $field.prop('multiple')) {
                    value = $field.val() || [];
                } else {
                    value = $field.val();
                }

                if (typeof value === 'undefined') {
                    value = '';
                }

                if (data.hasOwnProperty(key)) {
                    if (!$.isArray(data[key])) {
                        data[key] = [data[key]];
                    }
                    data[key].push(value);
                } else {
                    data[key] = value;
                }
            });

            var $editables = $container.find('[contenteditable="true"]').filter(function () {
                var $editable = $(this);
                if ($editable.is('[data-autosave-exclude="true"]')) {
                    return false;
                }

                return true;
            });

            $editables.each(function (index) {
                var $editable = $(this);
                var key = $editable.attr('data-autosave-key') || $editable.attr('id') || ('editable-' + index);

                if (!data.hasOwnProperty(key)) {
                    data[key] = $editable.html();
                }
            });

            return data;
        },

        saveElementDraft: function (payload) {
            var self = this;
            var savedAt = new Date();

            var persist = function () {
                self.persistElementDraft(payload, savedAt);
            };

            var $container = this.elementAutosaveContainer;
            if ($container && $container.length) {
                var endpoint = String($container.attr('data-autosave-url') || '');
                if (endpoint && endpoint !== '#') {
                    return $.ajax({
                        url: endpoint,
                        method: 'POST',
                        data: payload,
                        dataType: 'json'
                    }).then(function () {
                        persist();
                        return savedAt;
                    }, function (xhr) {
                        persist();
                        var deferred = $.Deferred();
                        deferred.reject(xhr);
                        return deferred.promise();
                    });
                }
            }

            return $.Deferred(function (defer) {
                window.setTimeout(function () {
                    try {
                        persist();
                        defer.resolve(savedAt);
                    } catch (error) {
                        defer.reject(error);
                    }
                }, 300);
            }).promise();
        },

        persistElementDraft: function (payload, savedAt) {
            if (!window.localStorage) {
                return;
            }

            try {
                var entry = {
                    savedAt: (savedAt && savedAt.toISOString) ? savedAt.toISOString() : new Date().toISOString(),
                    data: payload
                };
                window.localStorage.setItem(this.elementAutosaveStorageKey, JSON.stringify(entry));
            } catch (error) {
                // ignore storage errors
            }
        },

        getElementAutosaveStatusElement: function () {
            if (this.elementAutosaveStatusElement && this.elementAutosaveStatusElement.length) {
                return this.elementAutosaveStatusElement;
            }

            var $container = this.elementAutosaveContainer;
            if (!$container || !$container.length) {
                return $();
            }

            var $status = $container.find('[data-role="autosave-status"]').first();
            if (!$status.length) {
                $status = $('<div class="text-muted small" data-role="autosave-status" aria-live="polite"></div>');
                var $tools = $container.find('.box-header .box-tools').first();
                if ($tools.length) {
                    $tools.prepend($status);
                } else {
                    $status.prependTo($container);
                }
            }

            this.elementAutosaveStatusElement = $status;
            return $status;
        },

        getElementAutosaveMessages: function () {
            if (this.elementAutosaveMessagesCache) {
                return this.elementAutosaveMessagesCache;
            }

            var defaults = {
                clean: 'Все изменения сохранены',
                dirty: 'Есть несохранённые изменения',
                saving: 'Сохранение…',
                saved: 'Черновик сохранён в {time}',
                error: 'Не удалось сохранить черновик',
                beforeUnload: 'У вас есть несохранённые изменения. Вы уверены, что хотите покинуть страницу?'
            };

            var globalMessages = window.cmsElementEditorMessages || {};
            if (globalMessages.autosaveClean) {
                defaults.clean = globalMessages.autosaveClean;
            }
            if (globalMessages.autosaveDirty) {
                defaults.dirty = globalMessages.autosaveDirty;
            }
            if (globalMessages.autosaveSaving) {
                defaults.saving = globalMessages.autosaveSaving;
            }
            if (globalMessages.autosaveSaved) {
                defaults.saved = globalMessages.autosaveSaved;
            }
            if (globalMessages.autosaveError) {
                defaults.error = globalMessages.autosaveError;
            }
            if (globalMessages.beforeUnload) {
                defaults.beforeUnload = globalMessages.beforeUnload;
            }

            this.elementAutosaveMessagesCache = defaults;
            return defaults;
        },

        updateElementAutosaveStatus: function (status, options) {
            status = status || 'clean';
            this.elementAutosaveStatus = status;

            var messages = this.getElementAutosaveMessages();
            var message = messages[status] || messages.clean || '';

            var savedAt = options && options.savedAt;
            if (status === 'saved') {
                if (savedAt instanceof Date) {
                    var formatted = this.formatAutosaveTimestamp(savedAt);
                    if (message.indexOf('{time}') !== -1) {
                        message = message.replace('{time}', formatted);
                    } else if (formatted) {
                        message += ' ' + formatted;
                    }
                } else {
                    message = message.replace('{time}', '').trim();
                }
            }

            if (!message) {
                message = messages.clean || '';
            }

            var $status = this.getElementAutosaveStatusElement();
            if (!$status.length) {
                return;
            }

            $status
                .attr('data-status', status)
                .removeClass('text-muted text-warning text-info text-success text-danger');

            if (status === 'dirty') {
                $status.addClass('text-warning');
            } else if (status === 'saving') {
                $status.addClass('text-info');
            } else if (status === 'saved') {
                $status.addClass('text-success');
            } else if (status === 'error') {
                $status.addClass('text-danger');
            } else {
                $status.addClass('text-muted');
            }

            if (status === 'saved' && savedAt instanceof Date) {
                $status.attr('title', this.formatAutosaveTimestamp(savedAt, true));
            } else {
                $status.removeAttr('title');
            }

            $status.text(message);
        },

        formatAutosaveTimestamp: function (date, includeDate) {
            if (!(date instanceof Date)) {
                return '';
            }

            try {
                if (includeDate) {
                    return date.toLocaleString('ru-RU', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit'
                    });
                }

                return date.toLocaleTimeString('ru-RU', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
            } catch (error) {
                var pad = function (value) {
                    value = String(value);
                    return value.length < 2 ? '0' + value : value;
                };

                var hours = pad(date.getHours());
                var minutes = pad(date.getMinutes());
                var seconds = pad(date.getSeconds());

                if (includeDate) {
                    var day = pad(date.getDate());
                    var month = pad(date.getMonth() + 1);
                    var year = date.getFullYear();
                    return day + '.' + month + '.' + year + ' ' + hours + ':' + minutes + ':' + seconds;
                }

                return hours + ':' + minutes + ':' + seconds;
            }
        },

        isElementFormDirty: function () {
            if (!this.elementAutosaveContainer || !this.elementAutosaveContainer.length) {
                return false;
            }

            if (this.elementAutosaveDirty || this.elementAutosaveSaving) {
                return true;
            }

            return this.elementAutosaveStatus === 'error';
        },

        initCollectionEntriesModule: function () {
            var $container = $('[data-role="collection-entries"]');
            if (!$container.length) {
                return;
            }

            this.initCollectionEntriesSavedViews();
            this.bindCollectionEntriesFilters();
            this.bindCollectionEntriesBulkActions();
            this.bindCollectionEntriesTree();
            this.bindCollectionEntriesColumnToggles();

            var $table = $('#collection-entries-table');
            if ($table.length && this.collectionEntriesTable && !$table.data('collectionEntriesEventsBound')) {
                this.bindCollectionEntriesTableEvents($table);
                $table.data('collectionEntriesEventsBound', true);
            }

            this.updateCollectionEntriesSelectionState();
        },

        getCurrentCollectionHandle: function () {
            var $container = $('[data-role="collection-entries"]');
            if (!$container.length) {
                return '';
            }

            return String($container.data('collectionHandle') || '');
        },

        initCollectionEntriesTable: function () {
            var $table = $('#collection-entries-table');
            if (!$table.length || !$.fn.DataTable) {
                return;
            }

            if ($table.data('collectionEntriesInitialised')) {
                return;
            }

            var endpoint = String($table.data('endpoint') || '');
            if (!endpoint) {
                return;
            }

            var self = this;
            this.collectionEntriesTable = $table.DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                deferRender: true,
                ajax: {
                    url: endpoint,
                    data: function (params) {
                        var filters = self.getCollectionEntriesFilters();
                        params.search = params.search || {};
                        params.search.value = filters.search;
                        params.statuses = filters.statuses;
                        params.locales = filters.locales;
                        params.taxonomies = filters.taxonomies;
                        params.fields = filters.fields;
                        params.updated_from = filters.updatedFrom;
                        params.updated_to = filters.updatedTo;
                        params.parent = filters.parent;
                        params.view = filters.view;
                    }
                },
                dom: 't<"row"<"col-sm-6"l><"col-sm-6"p>>',
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                pageLength: 25,
                order: [[7, 'desc']],
                columns: [
                    { data: 'checkbox', orderable: false, searchable: false, width: '40px', className: 'text-center' },
                    { data: 'title', name: 'title' },
                    { data: 'slug', name: 'slug' },
                    { data: 'status', name: 'status', width: '120px' },
                    { data: 'locale', name: 'locale', width: '100px' },
                    { data: 'taxonomies', name: 'taxonomies' },
                    { data: 'author', name: 'author', width: '160px' },
                    { data: 'updated', name: 'updated', width: '160px' },
                    { data: 'published', name: 'published', width: '160px' }
                ],
                createdRow: function (row, data) {
                    $(row)
                        .attr('data-entry-id', data.id)
                        .attr('data-entry-parent', data.parent_id != null ? data.parent_id : '')
                        .attr('data-entry-depth', data.depth != null ? data.depth : '');
                },
                drawCallback: function () {
                    self.restoreCollectionEntriesSelection($table);
                    self.updateCollectionEntriesSelectionState();
                    self.syncCollectionEntriesSelectAllState($table);
                },
                language: {
                    processing: 'Загрузка…',
                    lengthMenu: 'Показать _MENU_ записей',
                    zeroRecords: 'Совпадений не найдено',
                    info: 'Показано _START_–_END_ из _TOTAL_ записей',
                    infoEmpty: 'Нет записей для отображения',
                    infoFiltered: '(отфильтровано из _MAX_)',
                    paginate: {
                        first: 'Первая',
                        previous: 'Назад',
                        next: 'Далее',
                        last: 'Последняя'
                    },
                    emptyTable: 'Нет записей для отображения'
                }
            });

            $table.data('collectionEntriesInitialised', true);
        },

        bindCollectionEntriesTableEvents: function ($table) {
            var self = this;

            $table.on('click', 'tbody tr', function (event) {
                if ($(event.target).is('input, label, a, button, select, option')) {
                    return;
                }

                var $checkbox = $(this).find('[data-role="collection-entry-select"]');
                if (!$checkbox.length) {
                    return;
                }

                var checked = !$checkbox.prop('checked');
                $checkbox.prop('checked', checked).trigger('change');
            });

            $table.on('change', 'tbody [data-role="collection-entry-select"]', function () {
                var $checkbox = $(this);
                var id = String($checkbox.attr('data-id') || '');
                if (!id) {
                    return;
                }

                if ($checkbox.prop('checked')) {
                    self.collectionEntriesSelection[id] = {
                        id: id,
                        title: $checkbox.attr('data-title') || '',
                        slug: $checkbox.attr('data-slug') || ''
                    };
                } else {
                    delete self.collectionEntriesSelection[id];
                }

                self.updateCollectionEntriesSelectionState();
            });

            $table.on('change', 'thead [data-role="entries-select-all"]', function () {
                var checked = $(this).prop('checked');
                $table.find('tbody [data-role="collection-entry-select"]').prop('checked', checked).trigger('change');
            });
        },

        restoreCollectionEntriesSelection: function ($table) {
            var self = this;
            $table.find('tbody [data-role="collection-entry-select"]').each(function () {
                var id = String($(this).attr('data-id') || '');
                if (id && self.collectionEntriesSelection[id]) {
                    $(this).prop('checked', true);
                }
            });
        },

        syncCollectionEntriesSelectAllState: function ($table) {
            var $selectAll = $table.find('thead [data-role="entries-select-all"]');
            if (!$selectAll.length) {
                return;
            }

            var total = $table.find('tbody [data-role="collection-entry-select"]').length;
            var selected = $table.find('tbody [data-role="collection-entry-select"]:checked').length;

            if (!total) {
                $selectAll.prop('checked', false).prop('indeterminate', false);
                return;
            }

            if (selected === total) {
                $selectAll.prop('checked', true).prop('indeterminate', false);
            } else if (selected > 0) {
                $selectAll.prop('checked', false).prop('indeterminate', true);
            } else {
                $selectAll.prop('checked', false).prop('indeterminate', false);
            }
        },

        updateCollectionEntriesSelectionState: function () {
            var selected = this.getSelectedEntries();
            var count = selected.length;
            var $summary = $('[data-role="collection-entries-selection-summary"]');
            if ($summary.length) {
                if (!count) {
                    $summary.text('Записи не выбраны');
                } else {
                    var names = selected.map(function (item) {
                        return item.title || ('#' + item.id);
                    });
                    var preview = names.slice(0, 3).join(', ');
                    if (names.length > 3) {
                        preview += ' и ещё ' + (names.length - 3);
                    }
                    $summary.text('Выбрано записей: ' + count + (preview ? ' (' + preview + ')' : ''));
                }
            }

            $('[data-requires-entries-selection]').prop('disabled', count === 0);

            var $table = $('#collection-entries-table');
            if ($table.length) {
                this.syncCollectionEntriesSelectAllState($table);
            }

            var $feedback = $('[data-role="collection-entries-bulk-feedback"]');
            if ($feedback.length && count === 0) {
                $feedback.text('').removeClass('text-danger text-success');
            }
        },

        getSelectedEntries: function () {
            var result = [];
            var keys = Object.keys(this.collectionEntriesSelection);
            for (var i = 0; i < keys.length; i++) {
                result.push(this.collectionEntriesSelection[keys[i]]);
            }

            return result;
        },

        getCollectionEntriesFilters: function () {
            var search = String($('#collection-entries-search').val() || '').trim();

            var statuses = $('#collection-entries-status').val() || [];
            if (!Array.isArray(statuses)) {
                statuses = statuses ? [statuses] : [];
            }
            statuses = statuses.filter(function (value) {
                return String(value || '').trim() !== '';
            });

            var locales = $('#collection-entries-locale').val() || [];
            if (!Array.isArray(locales)) {
                locales = locales ? [locales] : [];
            }
            locales = locales.filter(function (value) {
                return String(value || '').trim() !== '';
            });

            var taxonomies = {};
            $('[data-role="entries-filter-taxonomy"]').each(function () {
                var handle = String($(this).data('taxonomy') || '');
                if (!handle) {
                    return;
                }

                var value = $(this).val() || [];
                if (!Array.isArray(value)) {
                    value = value ? [value] : [];
                }

                var filtered = value.filter(function (item) {
                    return String(item || '').trim() !== '';
                });

                if (filtered.length) {
                    taxonomies[handle] = filtered.map(function (item) {
                        return String(item);
                    });
                }
            });

            var fields = {};
            $('[data-role="entries-filter-field"]').each(function () {
                var handle = String($(this).data('field') || '');
                if (!handle) {
                    return;
                }

                var value = $(this).val();
                if (Array.isArray(value)) {
                    if (!value.length) {
                        return;
                    }

                    fields[handle] = String(value[0]);
                    return;
                }

                value = String(value || '').trim();
                if (value !== '') {
                    fields[handle] = value;
                }
            });

            var updatedFrom = String($('#collection-entries-date-from').val() || '').trim();
            var updatedTo = String($('#collection-entries-date-to').val() || '').trim();

            return {
                search: search,
                statuses: statuses,
                locales: locales,
                taxonomies: taxonomies,
                fields: fields,
                updatedFrom: updatedFrom,
                updatedTo: updatedTo,
                parent: this.collectionEntriesCurrentNodeId || '',
                view: this.collectionEntriesCurrentViewId || ''
            };
        },

        reloadCollectionEntriesTable: function (resetPaging, preserveSelection) {
            if (!preserveSelection) {
                this.collectionEntriesSelection = {};
            }

            if (this.collectionEntriesTable) {
                this.collectionEntriesTable.ajax.reload(null, resetPaging !== false);
            }

            if (!preserveSelection) {
                this.updateCollectionEntriesSelectionState();
            }
        },

        initCollectionEntriesSavedViews: function () {
            var $container = $('[data-role="collection-entries"]');
            if (!$container.length) {
                return;
            }

            var handle = this.getCurrentCollectionHandle();
            this.collectionEntriesDefaultSavedViews = this.readCollectionEntriesSavedViewsFromDom();
            this.collectionEntriesSavedViews = this.loadCollectionEntriesSavedViews(handle);
            this.collectionEntriesCurrentViewId = null;
            this.renderCollectionEntriesSavedViews();

            var self = this;
            $('[data-role="collection-entries-saved-view"]').on('change', function () {
                var id = String($(this).val() || '');
                if (!id) {
                    self.collectionEntriesCurrentViewId = null;
                    return;
                }

                var view = self.findCollectionEntriesSavedView(id);
                if (!view) {
                    return;
                }

                self.collectionEntriesCurrentViewId = view.id;
                self.applyCollectionEntriesSavedView(view);
            });

            $(document).on('click', '[data-action="entries-save-view"]', function (event) {
                event.preventDefault();
                self.createCollectionEntriesSavedView();
            });

            $(document).on('click', '[data-action="entries-delete-view"]', function (event) {
                event.preventDefault();
                self.deleteCollectionEntriesSavedView();
            });
        },

        readCollectionEntriesSavedViewsFromDom: function () {
            var $script = $('[data-role="collection-entries-saved-views"]');
            if (!$script.length) {
                return [];
            }

            try {
                var raw = $script.text() || '[]';
                var parsed = JSON.parse(raw);
                if (!Array.isArray(parsed)) {
                    return [];
                }

                return parsed.filter(function (item) {
                    return item && typeof item.id === 'string' && typeof item.name === 'string' && item.filters;
                });
            } catch (error) {
                console.warn('Не удалось разобрать сохранённые виды записей', error);
                return [];
            }
        },

        getCollectionEntriesStorageKey: function () {
            var handle = this.getCurrentCollectionHandle();
            if (!handle) {
                return null;
            }

            return 'dashboard.collectionEntries.savedViews.' + handle;
        },

        loadCollectionEntriesSavedViews: function (handle) {
            if (!window.localStorage || !handle) {
                return [];
            }

            try {
                var raw = window.localStorage.getItem('dashboard.collectionEntries.savedViews.' + handle);
                if (!raw) {
                    return [];
                }

                var parsed = JSON.parse(raw);
                if (!Array.isArray(parsed)) {
                    return [];
                }

                return parsed.filter(function (item) {
                    return item && typeof item.id === 'string' && typeof item.name === 'string' && item.filters;
                });
            } catch (error) {
                console.warn('Не удалось загрузить сохранённые виды записей', error);
                return [];
            }
        },

        persistCollectionEntriesSavedViews: function () {
            if (!window.localStorage) {
                return;
            }

            var handle = this.getCurrentCollectionHandle();
            if (!handle) {
                return;
            }

            try {
                window.localStorage.setItem(
                    'dashboard.collectionEntries.savedViews.' + handle,
                    JSON.stringify(this.collectionEntriesSavedViews)
                );
            } catch (error) {
                console.warn('Не удалось сохранить Saved View записей', error);
            }
        },

        getAllCollectionEntriesSavedViews: function () {
            var seen = {};
            var result = [];

            var append = function (view) {
                if (!view || typeof view.id !== 'string') {
                    return;
                }

                if (seen[view.id]) {
                    return;
                }

                seen[view.id] = true;
                result.push(view);
            };

            for (var i = 0; i < this.collectionEntriesDefaultSavedViews.length; i++) {
                append(this.collectionEntriesDefaultSavedViews[i]);
            }

            for (var j = 0; j < this.collectionEntriesSavedViews.length; j++) {
                append(this.collectionEntriesSavedViews[j]);
            }

            return result;
        },

        renderCollectionEntriesSavedViews: function () {
            var $select = $('[data-role="collection-entries-saved-view"]');
            if (!$select.length) {
                return;
            }

            var current = this.collectionEntriesCurrentViewId;
            var options = ["<option value=''>Текущий фильтр</option>"];
            var views = this.getAllCollectionEntriesSavedViews();

            for (var i = 0; i < views.length; i++) {
                var view = views[i];
                options.push('<option value="' + this.escapeHtml(view.id) + '">' + this.escapeHtml(view.name) + '</option>');
            }

            $select.html(options.join(''));

            if (current) {
                $select.val(current);
            } else {
                $select.val('');
            }

            if ($select.data('select2')) {
                $select.trigger('change.select2');
            }
        },

        clearCollectionEntriesSavedView: function () {
            if (this.isApplyingCollectionEntriesView) {
                return;
            }

            if (!this.collectionEntriesCurrentViewId) {
                return;
            }

            this.collectionEntriesCurrentViewId = null;
            var $select = $('[data-role="collection-entries-saved-view"]');
            if ($select.length) {
                $select.val('');
                if ($select.data('select2')) {
                    $select.trigger('change.select2');
                }
            }
        },

        findCollectionEntriesSavedView: function (id) {
            var views = this.getAllCollectionEntriesSavedViews();
            for (var i = 0; i < views.length; i++) {
                if (views[i].id === id) {
                    return views[i];
                }
            }

            return null;
        },

        applyCollectionEntriesSavedView: function (view) {
            if (!view || !view.filters) {
                return;
            }

            var filters = view.filters;
            this.isApplyingCollectionEntriesView = true;

            var $search = $('#collection-entries-search');
            if ($search.length) {
                $search.val(filters.search || '');
            }

            var $status = $('#collection-entries-status');
            if ($status.length) {
                var statusValues = filters.statuses || filters.status || [];
                if (!Array.isArray(statusValues)) {
                    statusValues = statusValues ? [statusValues] : [];
                }
                $status.val(statusValues);
                if ($status.data('select2')) {
                    $status.trigger('change.select2');
                }
            }

            var $locale = $('#collection-entries-locale');
            if ($locale.length) {
                var localeValues = filters.locales || filters.locale || [];
                if (!Array.isArray(localeValues)) {
                    localeValues = localeValues ? [localeValues] : [];
                }
                $locale.val(localeValues);
                if ($locale.data('select2')) {
                    $locale.trigger('change.select2');
                }
            }

            var $dateFrom = $('#collection-entries-date-from');
            if ($dateFrom.length) {
                $dateFrom.val(filters.updatedFrom || filters.date_from || '');
            }

            var $dateTo = $('#collection-entries-date-to');
            if ($dateTo.length) {
                $dateTo.val(filters.updatedTo || filters.date_to || '');
            }

            $('[data-role="entries-filter-taxonomy"]').each(function () {
                var handle = String($(this).data('taxonomy') || '');
                var values = [];
                if (filters.taxonomies && filters.taxonomies[handle]) {
                    values = filters.taxonomies[handle];
                    if (!Array.isArray(values)) {
                        values = values ? [values] : [];
                    }
                }
                $(this).val(values);
                if ($(this).data('select2')) {
                    $(this).trigger('change.select2');
                }
            });

            $('[data-role="entries-filter-field"]').each(function () {
                var handle = String($(this).data('field') || '');
                var value = filters.fields && filters.fields[handle] ? String(filters.fields[handle]) : '';
                $(this).val(value);
                if ($(this).is('select') && $(this).data('select2')) {
                    $(this).trigger('change.select2');
                }
            });

            var parentId = filters.parent || '';
            this.setCollectionEntriesTreeNode(parentId, { silent: true });

            this.isApplyingCollectionEntriesView = false;
            this.renderCollectionEntriesSavedViews();
            this.reloadCollectionEntriesTable(true, true);
        },

        createCollectionEntriesSavedView: function () {
            var name = window.prompt('Название сохранённого вида', 'Новый вид');
            if (!name) {
                return;
            }

            name = String(name).trim();
            if (!name) {
                return;
            }

            var filters = this.getCollectionEntriesFilters();
            delete filters.view;

            var view = {
                id: 'entries-view-' + Date.now(),
                name: name,
                filters: filters
            };

            this.collectionEntriesSavedViews.push(view);
            this.collectionEntriesCurrentViewId = view.id;
            this.persistCollectionEntriesSavedViews();
            this.renderCollectionEntriesSavedViews();
            this.applyCollectionEntriesSavedView(view);
        },

        deleteCollectionEntriesSavedView: function () {
            if (!this.collectionEntriesCurrentViewId) {
                window.alert('Выберите сохранённый вид для удаления.');
                return;
            }

            var id = this.collectionEntriesCurrentViewId;
            var isDefault = this.collectionEntriesDefaultSavedViews.some(function (item) {
                return item && item.id === id;
            });

            if (isDefault) {
                window.alert('Нельзя удалить предустановленный вид.');
                return;
            }

            this.collectionEntriesSavedViews = this.collectionEntriesSavedViews.filter(function (item) {
                return item && item.id !== id;
            });

            this.collectionEntriesCurrentViewId = null;
            this.persistCollectionEntriesSavedViews();
            this.renderCollectionEntriesSavedViews();
            this.reloadCollectionEntriesTable(true);
        },

        bindCollectionEntriesFilters: function () {
            var self = this;
            var $search = $('#collection-entries-search');
            var $status = $('#collection-entries-status');
            var $locale = $('#collection-entries-locale');
            var $dateFrom = $('#collection-entries-date-from');
            var $dateTo = $('#collection-entries-date-to');
            var $taxonomies = $('[data-role="entries-filter-taxonomy"]');
            var $fields = $('[data-role="entries-filter-field"]');

            var debounceTimer = null;
            if ($search.length) {
                $search.on('input', function () {
                    if (!self.isApplyingCollectionEntriesView) {
                        self.clearCollectionEntriesSavedView();
                    }

                    window.clearTimeout(debounceTimer);
                    debounceTimer = window.setTimeout(function () {
                        self.reloadCollectionEntriesTable(true);
                    }, 250);
                });
            }

            var handleSelectChange = function () {
                if (!self.isApplyingCollectionEntriesView) {
                    self.clearCollectionEntriesSavedView();
                }
                self.reloadCollectionEntriesTable(true);
            };

            if ($status.length) {
                $status.on('change', handleSelectChange);
            }

            if ($locale.length) {
                $locale.on('change', handleSelectChange);
            }

            if ($dateFrom.length) {
                $dateFrom.on('change', handleSelectChange);
            }

            if ($dateTo.length) {
                $dateTo.on('change', handleSelectChange);
            }

            $taxonomies.on('change', handleSelectChange);

            $fields.on('change input', function () {
                if (!self.isApplyingCollectionEntriesView) {
                    self.clearCollectionEntriesSavedView();
                }
                self.reloadCollectionEntriesTable(true);
            });

            $(document).on('click', '[data-action="entries-reset-filters"]', function (event) {
                event.preventDefault();

                if ($search.length) {
                    $search.val('');
                }

                if ($status.length) {
                    $status.val('');
                    if ($status.data('select2')) {
                        $status.trigger('change.select2');
                    }
                }

                if ($locale.length) {
                    $locale.val('');
                    if ($locale.data('select2')) {
                        $locale.trigger('change.select2');
                    }
                }

                if ($dateFrom.length) {
                    $dateFrom.val('');
                }

                if ($dateTo.length) {
                    $dateTo.val('');
                }

                $taxonomies.each(function () {
                    $(this).val('');
                    if ($(this).data('select2')) {
                        $(this).trigger('change.select2');
                    }
                });

                $fields.each(function () {
                    $(this).val('');
                    if ($(this).is('select') && $(this).data('select2')) {
                        $(this).trigger('change.select2');
                    }
                });

                self.collectionEntriesCurrentNodeId = '';
                self.setCollectionEntriesTreeNode('', { silent: true });
                self.clearCollectionEntriesSavedView();
                self.reloadCollectionEntriesTable(true);
            });
        },

        bindCollectionEntriesBulkActions: function () {
            var self = this;
            var $bulkSelect = $('[data-role="collection-entries-bulk"]');
            var $feedback = $('[data-role="collection-entries-bulk-feedback"]');

            $(document).on('click', '[data-action="entries-bulk-apply"]', function (event) {
                event.preventDefault();

                if (!$bulkSelect.length) {
                    return;
                }

                var action = String($bulkSelect.val() || '');
                var selected = self.getSelectedEntries();

                if (!action) {
                    if ($feedback.length) {
                        $feedback.removeClass('text-success').addClass('text-danger').text('Выберите массовое действие.');
                    }
                    return;
                }

                if (!selected.length) {
                    if ($feedback.length) {
                        $feedback.removeClass('text-success').addClass('text-danger').text('Выберите хотя бы одну запись.');
                    }
                    return;
                }

                var titles = selected.map(function (item) {
                    return item.title || ('#' + item.id);
                });
                var preview = titles.slice(0, 3).join(', ');
                if (titles.length > 3) {
                    preview += ' и ещё ' + (titles.length - 3);
                }

                if ($feedback.length) {
                    $feedback.removeClass('text-danger').addClass('text-success').text('Действие «' + action + '» будет применено к ' + selected.length + ' записям: ' + preview + '.');
                }

                console.info('Entries bulk action', action, selected);
            });
        },

        bindCollectionEntriesTree: function () {
            var self = this;
            var $tree = $('[data-role="collection-entries-tree"]');
            if (!$tree.length) {
                return;
            }

            $tree.on('click', '[data-role="entries-tree-node"]', function (event) {
                event.preventDefault();
                var nodeId = String($(this).attr('data-node-id') || '');
                self.setCollectionEntriesTreeNode(nodeId);
            });

            if (typeof window.Sortable !== 'undefined') {
                $tree.each(function () {
                    if (this._collectionEntriesSortable) {
                        return;
                    }

                    this._collectionEntriesSortable = window.Sortable.create(this, {
                        group: 'collection-entries-tree',
                        animation: 150,
                        handle: '.entries-tree-node'
                    });
                });
            }
        },

        setCollectionEntriesTreeNode: function (nodeId, options) {
            options = options || {};
            var id = String(nodeId || '');
            if (!options.silent && !this.isApplyingCollectionEntriesView) {
                this.clearCollectionEntriesSavedView();
            }

            this.collectionEntriesCurrentNodeId = id;

            var $tree = $('[data-role="collection-entries-tree"]');
            if ($tree.length) {
                $tree.find('.entries-tree-node').removeClass('entries-tree-node--active');
                $tree.find('[data-role="entries-tree-node"]').each(function () {
                    if (String($(this).attr('data-node-id') || '') === id) {
                        $(this).addClass('entries-tree-node--active');
                    }
                });
            }

            if (!options.silent) {
                this.reloadCollectionEntriesTable(true);
            }
        },

        bindCollectionEntriesColumnToggles: function () {
            var self = this;
            $(document).on('change', '[data-role="collection-entries-column-toggle"]', function () {
                if (!self.collectionEntriesTable) {
                    return;
                }

                var columnIndex = parseInt($(this).attr('data-column'), 10);
                if (isNaN(columnIndex)) {
                    return;
                }

                var visible = $(this).prop('checked');
                self.collectionEntriesTable.column(columnIndex).visible(visible);
            });
        },

        initRelationsModule: function () {
            if (!$.fn.select2) {
                return;
            }

            var $modal = $('#relation-modal');
            if (!$modal.length) {
                return;
            }

            var $source = $modal.find('[data-role="relation-source"]');
            var $target = $modal.find('[data-role="relation-target"]');
            if (!$source.length || !$target.length) {
                return;
            }

            this.loadRelationSelection();

            var self = this;
            var configureSelect = function ($element, type) {
                if ($element.data('select2')) {
                    $element.select2('destroy');
                }

                var saved = self.getRelationSavedSelection(type);
                if (saved && saved.id) {
                    var option = new Option(saved.text || saved.id, saved.id, true, true);
                    $element.append(option);
                    self.relationOptionsCache[saved.id] = saved;
                }

                $element.select2({
                    width: '100%',
                    allowClear: true,
                    dropdownParent: $modal,
                    placeholder: $element.data('placeholder') || '',
                    ajax: {
                        transport: function (params, success, failure) {
                            params = params || {};
                            var query = '';
                            var page = 1;
                            if (params.data) {
                                query = params.data.q || '';
                                page = parseInt(params.data.page || 1, 10);
                                if (isNaN(page) || page < 1) {
                                    page = 1;
                                }
                            }

                            self.searchRelationElements(query, page)
                                .done(function (results) {
                                    success(results);
                                })
                                .fail(failure);
                        },
                        delay: 200,
                        processResults: function (data) {
                            return data;
                        }
                    },
                    templateResult: function (item) {
                        if (item.loading) {
                            return item.text;
                        }

                        var $result = $('<div class="relation-select-option"></div>');
                        var $title = $('<div class="relation-select-option__title"></div>').text(item.text || item.id || '');
                        $result.append($title);

                        var metaParts = [];
                        if (item.collectionName) {
                            metaParts.push(item.collectionName);
                        }
                        if (item.slug) {
                            metaParts.push('<code>' + $('<div>').text(item.slug).html() + '</code>');
                        }
                        if (item.status) {
                            metaParts.push(item.status);
                        }

                        if (metaParts.length) {
                            $result.append($('<div class="relation-select-option__meta"></div>').html(metaParts.join(' • ')));
                        }

                        return $result;
                    },
                    templateSelection: function (item) {
                        return item.text || item.id;
                    },
                    escapeMarkup: function (markup) {
                        return markup;
                    }
                });

                $element.on('change', function () {
                    self.saveRelationSelection(type, $element.select2('data'));
                });

                $element.on('select2:select', function (event) {
                    var data = event.params && event.params.data ? event.params.data : null;
                    if (data && data.id) {
                        self.relationOptionsCache[data.id] = $.extend({}, data);
                    }
                });

                $element.on('select2:clear', function () {
                    self.saveRelationSelection(type, []);
                });

                $element.trigger('change');
            };

            configureSelect($source, 'source');
            configureSelect($target, 'target');
        },

        getRelationsDataset: function () {
            var deferred = $.Deferred();
            if (this.relationsDataset) {
                deferred.resolve(this.relationsDataset);
                return deferred.promise();
            }

            var dataset = [
                {
                    id: 'articles',
                    name: 'Коллекция «Статьи»',
                    handle: 'articles',
                    elements: [
                        { id: 'articles:1001', elementId: 1001, title: '10 трендов медиа 2025', slug: 'media-trends-2025', type: 'entry', status: 'Опубликовано' },
                        { id: 'articles:1002', elementId: 1002, title: 'Как запустить подкаст за неделю', slug: 'launch-podcast-week', type: 'entry', status: 'Черновик' },
                        { id: 'articles:1003', elementId: 1003, title: 'Media trends digest', slug: 'media-trends-digest', type: 'entry', status: 'Опубликовано' },
                        { id: 'articles:1004', elementId: 1004, title: 'Редакторский стандарт 2.0', slug: 'editorial-standards', type: 'entry', status: 'На ревью' },
                        { id: 'articles:1005', elementId: 1005, title: 'Расписание публикаций на март', slug: 'march-schedule', type: 'entry', status: 'Запланировано' }
                    ]
                },
                {
                    id: 'interviews',
                    name: 'Коллекция «Интервью»',
                    handle: 'interviews',
                    elements: [
                        { id: 'interviews:2001', elementId: 2001, title: 'Product talks: выпуск 12', slug: 'product-talks-12', type: 'entry', status: 'Опубликовано' },
                        { id: 'interviews:2002', elementId: 2002, title: 'Как развивать редакцию в 2025', slug: 'grow-editorial-2025', type: 'entry', status: 'Черновик' },
                        { id: 'interviews:2003', elementId: 2003, title: 'Культура удалённой команды', slug: 'remote-culture', type: 'entry', status: 'Опубликовано' }
                    ]
                },
                {
                    id: 'authors',
                    name: 'Справочник «Авторы»',
                    handle: 'authors',
                    elements: [
                        { id: 'author:501', elementId: 501, title: 'Анна Иванова', slug: 'anna-ivanova', type: 'author', status: 'Активен' },
                        { id: 'author:502', elementId: 502, title: 'Борис Юрченко', slug: 'boris-yurchenko', type: 'author', status: 'Активен' },
                        { id: 'author:503', elementId: 503, title: 'Elena Petrova', slug: 'elena-petrova', type: 'author', status: 'Активен' },
                        { id: 'author:504', elementId: 504, title: 'Сергей Лебедев', slug: 'sergey-lebedev', type: 'author', status: 'Неактивен' }
                    ]
                }
            ];

            var self = this;
            window.setTimeout(function () {
                self.relationsDataset = dataset;
                deferred.resolve(dataset);
            }, 120);

            return deferred.promise();
        },

        filterRelationElements: function (dataset, query) {
            var normalized = $.trim(String(query || '')).toLowerCase();
            var items = [];

            $.each(dataset, function (_, group) {
                var elements = group.elements || [];
                var collectionName = group.name || '';
                var handle = group.handle || '';

                $.each(elements, function (_, element) {
                    var title = element.title || '';
                    var slug = element.slug || '';
                    var haystack = (title + ' ' + slug + ' ' + collectionName + ' ' + handle).toLowerCase();

                    if (!normalized || haystack.indexOf(normalized) !== -1) {
                        items.push({
                            id: element.id,
                            text: title,
                            slug: slug,
                            collectionId: group.id,
                            collectionName: collectionName,
                            collectionHandle: handle,
                            type: element.type || 'element',
                            status: element.status || ''
                        });
                    }
                });
            });

            items.sort(function (a, b) {
                return a.text.localeCompare(b.text, 'ru');
            });

            return items;
        },

        groupRelationResults: function (items) {
            var groups = {};

            $.each(items, function (_, item) {
                var key = String(item.collectionId || item.collectionName || 'default');
                if (!groups[key]) {
                    groups[key] = {
                        text: item.collectionName || 'Элементы',
                        id: key,
                        children: []
                    };
                }

                groups[key].children.push({
                    id: item.id,
                    text: item.text,
                    slug: item.slug,
                    collectionId: item.collectionId,
                    collectionName: item.collectionName,
                    collectionHandle: item.collectionHandle,
                    type: item.type,
                    status: item.status
                });
            });

            return Object.keys(groups).map(function (key) {
                return groups[key];
            });
        },

        searchRelationElements: function (query, page) {
            var self = this;
            var deferred = $.Deferred();

            this.getRelationsDataset().done(function (dataset) {
                var items = self.filterRelationElements(dataset, query);
                var perPage = 20;
                var offset = (page - 1) * perPage;
                if (offset < 0) {
                    offset = 0;
                }

                var paginated = items.slice(offset, offset + perPage);
                var grouped = self.groupRelationResults(paginated);

                $.each(grouped, function (_, group) {
                    $.each(group.children, function (_, child) {
                        self.relationOptionsCache[child.id] = child;
                    });
                });

                deferred.resolve({
                    results: grouped,
                    pagination: { more: offset + perPage < items.length }
                });
            }).fail(deferred.reject);

            return deferred.promise();
        },

        loadRelationSelection: function () {
            if (this.relationSavedSelection !== null) {
                return this.relationSavedSelection;
            }

            var defaults = { source: null, target: null };
            this.relationSavedSelection = defaults;

            if (!window.localStorage) {
                return defaults;
            }

            try {
                var raw = window.localStorage.getItem(this.relationSelectionKey);
                if (!raw) {
                    return defaults;
                }

                var parsed = JSON.parse(raw);
                if (parsed && typeof parsed === 'object') {
                    this.relationSavedSelection = {
                        source: parsed.source || null,
                        target: parsed.target || null
                    };
                }
            } catch (error) {
                this.relationSavedSelection = defaults;
            }

            var savedSource = this.relationSavedSelection.source;
            if (savedSource && savedSource.id) {
                this.relationOptionsCache[savedSource.id] = savedSource;
            }

            var savedTarget = this.relationSavedSelection.target;
            if (savedTarget && savedTarget.id) {
                this.relationOptionsCache[savedTarget.id] = savedTarget;
            }

            return this.relationSavedSelection;
        },

        getRelationSavedSelection: function (type) {
            var saved = this.loadRelationSelection();
            if (!saved || !saved[type]) {
                return null;
            }

            return saved[type];
        },

        saveRelationSelection: function (type, data) {
            var current = this.loadRelationSelection();
            var selection = null;

            if (data && data.length) {
                var item = data[0];
                var cached = this.relationOptionsCache[item.id] || {};

                selection = {
                    id: item.id,
                    text: item.text || cached.text || '',
                    collectionId: item.collectionId || cached.collectionId || null,
                    collectionName: item.collectionName || cached.collectionName || null,
                    collectionHandle: item.collectionHandle || cached.collectionHandle || null,
                    slug: item.slug || cached.slug || null,
                    type: item.type || cached.type || null,
                    status: item.status || cached.status || null
                };

                this.relationOptionsCache[item.id] = selection;
            }

            current[type] = selection;
            this.relationSavedSelection = current;

            if (!window.localStorage) {
                return;
            }

            try {
                window.localStorage.setItem(this.relationSelectionKey, JSON.stringify(current));
            } catch (error) {
                // ignore storage errors
            }
        },

        initMediaLibraryModule: function () {
            var $library = $('[data-role="media-library"]');
            if (!$library.length) {
                return;
            }

            var self = this;
            if (!$library.data('mediaLibraryInitialized')) {
                $library.data('mediaLibraryInitialized', true);
            }

            this.getMediaAssets().done(function (assets) {
                self.mediaLibraryFilters = {
                    search: '',
                    type: '',
                    collection: '',
                    tags: [],
                    period: '30'
                };
                self.mediaLibrarySelection = [];
                self.mediaLibraryViewMode = 'grid';

                self.populateMediaFilterOptions($library, assets);
                self.bindMediaLibraryEvents($library);
                self.renderMediaLibrary($library);
            });
        },

        getMediaAssets: function () {
            var deferred = $.Deferred();
            if (this.mediaAssetsDataset) {
                deferred.resolve(this.mediaAssetsDataset);
                return deferred.promise();
            }

            var assets = [
                {
                    id: 'asset-501',
                    title: 'Редакция в работе',
                    filename: 'team-working.jpg',
                    type: 'image',
                    size: 1245780,
                    width: 1920,
                    height: 1080,
                    preview: 'https://via.placeholder.com/600x400?text=Team',
                    thumb: 'https://via.placeholder.com/360x220?text=Team',
                    url: 'https://cdn.example.com/assets/team-working.jpg',
                    collection: 'articles',
                    collectionName: 'Статьи',
                    tags: ['editorial', 'workflow'],
                    createdAt: '2025-03-08T10:15:00+03:00'
                },
                {
                    id: 'asset-502',
                    title: 'Главный баннер весны',
                    filename: 'hero-banner.png',
                    type: 'image',
                    size: 2150042,
                    width: 2560,
                    height: 1440,
                    preview: 'https://via.placeholder.com/600x400?text=Banner',
                    thumb: 'https://via.placeholder.com/360x220?text=Banner',
                    url: 'https://cdn.example.com/assets/hero-banner.png',
                    collection: 'news',
                    collectionName: 'Новости',
                    tags: ['promo', 'homepage'],
                    createdAt: '2025-02-21T08:50:00+03:00'
                },
                {
                    id: 'asset-503',
                    title: 'Brand story 2025',
                    filename: 'brand-story.mp4',
                    type: 'video',
                    size: 18520480,
                    duration: 210,
                    preview: 'https://via.placeholder.com/600x400?text=Video',
                    thumb: 'https://via.placeholder.com/360x220?text=Video',
                    url: 'https://cdn.example.com/assets/brand-story.mp4',
                    collection: 'interviews',
                    collectionName: 'Интервью',
                    tags: ['branding', 'events'],
                    createdAt: '2025-03-02T12:00:00+03:00'
                },
                {
                    id: 'asset-504',
                    title: 'Редакторский гайд 2025',
                    filename: 'editorial-guide.pdf',
                    type: 'document',
                    size: 842312,
                    url: 'https://cdn.example.com/assets/editorial-guide.pdf',
                    collection: 'articles',
                    collectionName: 'Статьи',
                    tags: ['workflow', 'guideline'],
                    createdAt: '2025-01-16T14:35:00+03:00'
                },
                {
                    id: 'asset-505',
                    title: 'Podcast intro 2025',
                    filename: 'podcast-intro.mp3',
                    type: 'audio',
                    size: 5234400,
                    duration: 95,
                    url: 'https://cdn.example.com/assets/podcast-intro.mp3',
                    collection: 'articles',
                    collectionName: 'Статьи',
                    tags: ['podcast', 'audio'],
                    createdAt: '2025-02-10T18:05:00+03:00'
                },
                {
                    id: 'asset-506',
                    title: 'Обложка рассылки март',
                    filename: 'newsletter-cover.jpg',
                    type: 'image',
                    size: 612304,
                    width: 1280,
                    height: 720,
                    preview: 'https://via.placeholder.com/600x400?text=Newsletter',
                    thumb: 'https://via.placeholder.com/360x220?text=Newsletter',
                    url: 'https://cdn.example.com/assets/newsletter-cover.jpg',
                    collection: 'articles',
                    collectionName: 'Статьи',
                    tags: ['newsletter', 'promo'],
                    createdAt: '2025-03-06T09:40:00+03:00'
                },
                {
                    id: 'asset-507',
                    title: 'Команда редакции',
                    filename: 'culture-team.jpg',
                    type: 'image',
                    size: 1480230,
                    width: 2048,
                    height: 1365,
                    preview: 'https://via.placeholder.com/600x400?text=Culture',
                    thumb: 'https://via.placeholder.com/360x220?text=Culture',
                    url: 'https://cdn.example.com/assets/culture-team.jpg',
                    collection: 'interviews',
                    collectionName: 'Интервью',
                    tags: ['culture', 'people'],
                    createdAt: '2025-02-28T11:20:00+03:00'
                },
                {
                    id: 'asset-508',
                    title: 'Media kit 2025',
                    filename: 'media-kit.zip',
                    type: 'archive',
                    size: 12288000,
                    url: 'https://cdn.example.com/assets/media-kit.zip',
                    collection: 'news',
                    collectionName: 'Новости',
                    tags: ['press', 'kit'],
                    createdAt: '2024-12-18T16:10:00+03:00'
                },
                {
                    id: 'asset-509',
                    title: 'Интервью. Фрагмент видео',
                    filename: 'interview-snippet.mp4',
                    type: 'video',
                    size: 9520480,
                    duration: 135,
                    preview: 'https://via.placeholder.com/600x400?text=Interview',
                    thumb: 'https://via.placeholder.com/360x220?text=Interview',
                    url: 'https://cdn.example.com/assets/interview-snippet.mp4',
                    collection: 'interviews',
                    collectionName: 'Интервью',
                    tags: ['video', 'product'],
                    createdAt: '2025-03-03T15:25:00+03:00'
                },
                {
                    id: 'asset-510',
                    title: 'Инфографика. Метрики',
                    filename: 'infographic-metrics.png',
                    type: 'image',
                    size: 1765340,
                    width: 2000,
                    height: 1125,
                    preview: 'https://via.placeholder.com/600x400?text=Metrics',
                    thumb: 'https://via.placeholder.com/360x220?text=Metrics',
                    url: 'https://cdn.example.com/assets/infographic-metrics.png',
                    collection: 'news',
                    collectionName: 'Новости',
                    tags: ['analytics', 'report'],
                    createdAt: '2025-01-28T10:05:00+03:00'
                }
            ];

            var self = this;
            window.setTimeout(function () {
                self.mediaAssetsDataset = assets;
                self.mediaAssetsIndex = {};
                $.each(assets, function (_, asset) {
                    if (asset && asset.id) {
                        self.mediaAssetsIndex[String(asset.id)] = asset;
                    }
                });
                deferred.resolve(self.mediaAssetsDataset);
            }, 150);

            return deferred.promise();
        },

        populateMediaFilterOptions: function ($library, assets) {
            var self = this;
            var $type = $library.find('[data-role="media-filter-type"]');
            var $collection = $library.find('[data-role="media-filter-collection"]');
            var $tags = $library.find('[data-role="media-filter-tags"]');

            var typeOptions = {};
            var collectionOptions = {};
            var tags = {};

            $.each(assets, function (_, asset) {
                var type = asset.type || 'other';
                typeOptions[type] = self.getMediaTypeLabel(type);

                if (asset.collection) {
                    collectionOptions[asset.collection] = asset.collectionName || asset.collection;
                }

                $.each(asset.tags || [], function (_, tag) {
                    tags[tag] = true;
                });
            });

            $type.empty();
            $type.append($('<option></option>').attr('value', '').text('Все типы'));
            $.each(typeOptions, function (value, label) {
                $type.append($('<option></option>').attr('value', value).text(label));
            });

            $collection.empty();
            $collection.append($('<option></option>').attr('value', '').text('Все коллекции'));
            $.each(collectionOptions, function (value, label) {
                $collection.append($('<option></option>').attr('value', value).text(label));
            });

            $tags.empty();
            Object.keys(tags).sort().forEach(function (tag) {
                $tags.append($('<option></option>').attr('value', tag).text(tag));
            });

            $library.find('[data-role="media-filter-period"]').val('30').trigger('change.select2');
            $type.trigger('change.select2');
            $collection.trigger('change.select2');
            $tags.trigger('change.select2');
        },

        bindMediaLibraryEvents: function ($library) {
            var self = this;
            if ($library.data('mediaLibraryBound')) {
                return;
            }
            $library.data('mediaLibraryBound', true);

            var searchTimer = null;

            $library.on('input', '[data-role="media-search"]', function () {
                var value = $(this).val();
                window.clearTimeout(searchTimer);
                searchTimer = window.setTimeout(function () {
                    self.mediaLibraryFilters.search = String(value || '').trim();
                    self.renderMediaLibrary($library);
                }, 200);
            });

            $library.on('change', '[data-role="media-filter-type"]', function () {
                self.mediaLibraryFilters.type = String($(this).val() || '');
                self.renderMediaLibrary($library);
            });

            $library.on('change', '[data-role="media-filter-collection"]', function () {
                self.mediaLibraryFilters.collection = String($(this).val() || '');
                self.renderMediaLibrary($library);
            });

            $library.on('change', '[data-role="media-filter-tags"]', function () {
                var values = $(this).val() || [];
                self.mediaLibraryFilters.tags = $.isArray(values) ? values.slice() : [values];
                self.renderMediaLibrary($library);
            });

            $library.on('change', '[data-role="media-filter-period"]', function () {
                self.mediaLibraryFilters.period = String($(this).val() || 'all');
                self.renderMediaLibrary($library);
            });

            $library.on('click', '[data-role="media-view-mode"] button', function (event) {
                event.preventDefault();
                var mode = String($(this).attr('data-mode') || 'grid');
                self.setMediaLibraryViewMode(mode, $library);
            });

            $(document).on('click', '[data-action="toggle-media-filters"]', function (event) {
                event.preventDefault();
                var $panel = $library.find('[data-role="media-filters-panel"]');
                if ($panel.length) {
                    $panel.slideToggle(150);
                }
            });

            $library.on('click', '[data-role="media-item"]', function (event) {
                if ($(event.target).is('button, a, i')) {
                    return;
                }
                var id = $(this).attr('data-id');
                if (!id) {
                    return;
                }
                self.toggleMediaSelection(id);
                self.syncMediaSelectionUI($library);
            });

            $library.on('click', '[data-action="clear-selection"]', function (event) {
                event.preventDefault();
                self.clearMediaSelection($library);
            });

            $library.on('click', '[data-action="insert-selection"]', function (event) {
                event.preventDefault();
                self.outputMediaSelection($library);
            });

            $library.on('click', '[data-action="refresh-library"]', function (event) {
                event.preventDefault();
                var $loading = $library.find('[data-role="media-loading"]');
                $loading.stop(true, true).fadeIn(120);
                window.setTimeout(function () {
                    $loading.fadeOut(160);
                    self.renderMediaLibrary($library);
                }, 400);
            });
        },

        applyMediaFilters: function (assets) {
            var filters = this.mediaLibraryFilters || {};
            var search = (filters.search || '').toLowerCase();
            var typeFilter = filters.type || '';
            var collectionFilter = filters.collection || '';
            var tagsFilter = filters.tags || [];
            var period = filters.period || 'all';
            var now = new Date();

            return assets.filter(function (asset) {
                if (typeFilter && asset.type !== typeFilter) {
                    return false;
                }

                if (collectionFilter && asset.collection !== collectionFilter) {
                    return false;
                }

                if (tagsFilter && tagsFilter.length) {
                    var assetTags = asset.tags || [];
                    var hasTag = tagsFilter.some(function (tag) {
                        return assetTags.indexOf(tag) !== -1;
                    });
                    if (!hasTag) {
                        return false;
                    }
                }

                if (period && period !== 'all') {
                    var days = parseInt(period, 10);
                    if (!isNaN(days) && days > 0) {
                        var assetDate = new Date(asset.createdAt || asset.updatedAt || asset.uploadedAt || 0);
                        if (assetDate.toString() !== 'Invalid Date') {
                            var diff = now - assetDate;
                            if (diff > days * 86400000) {
                                return false;
                            }
                        }
                    }
                }

                if (search) {
                    var haystack = (asset.title + ' ' + asset.filename + ' ' + (asset.collectionName || '') + ' ' + (asset.tags || []).join(' ')).toLowerCase();
                    if (haystack.indexOf(search) === -1) {
                        return false;
                    }
                }

                return true;
            });
        },

        renderMediaLibrary: function ($library) {
            var assets = this.mediaAssetsDataset || [];
            var filtered = this.applyMediaFilters(assets);
            var $items = $library.find('[data-role="media-items"]');
            var $list = $library.find('[data-role="media-list"]');
            var $empty = $library.find('[data-role="media-empty"]');

            $items.empty();
            $list.empty();

            if (!filtered.length) {
                $empty.show();
                $items.hide();
                $list.hide();
                this.updateMediaSelectionSummary($library);
                return;
            }

            $empty.hide();

            if (this.mediaLibraryViewMode === 'list') {
                this.renderMediaList($list, filtered);
                $list.show();
                $items.hide();
            } else {
                this.renderMediaGrid($items, filtered);
                $items.show();
                $list.hide();
            }

            this.syncMediaSelectionUI($library);
        },

        renderMediaGrid: function ($container, assets) {
            var self = this;
            $container.empty();

            $.each(assets, function (_, asset) {
                var $col = $('<div class="col-sm-3 col-xs-6 media-library__col"></div>');
                var $card = $('<div class="media-library__item thumbnail" data-role="media-item"></div>');
                $card.attr('data-id', asset.id);
                $card.attr('data-type', asset.type);
                $card.attr('data-collection', asset.collection);

                var $preview = $('<div class="media-library__preview"></div>');
                if (asset.type === 'image' && asset.thumb) {
                    $preview.append($('<img>').attr('src', asset.thumb).attr('alt', asset.title));
                } else {
                    var icon = 'file-o';
                    if (asset.type === 'video') {
                        icon = 'film';
                    } else if (asset.type === 'audio') {
                        icon = 'music';
                    } else if (asset.type === 'document') {
                        icon = 'file-text-o';
                    } else if (asset.type === 'archive') {
                        icon = 'archive';
                    }
                    $preview.append('<div class="media-library__icon"><i class="fa fa-' + icon + '"></i></div>');
                }
                $card.append($preview);

                var $caption = $('<div class="media-library__caption"></div>');
                $caption.append($('<strong class="media-library__title"></strong>').text(asset.title));

                var meta = [];
                if (asset.filename) {
                    meta.push(asset.filename);
                }
                meta.push(self.getMediaTypeLabel(asset.type));
                if (asset.size) {
                    meta.push(self.formatFileSize(asset.size));
                }
                if (asset.collectionName) {
                    meta.push(asset.collectionName);
                }
                if (asset.createdAt) {
                    meta.push(self.formatDateLabel(asset.createdAt));
                }

                $caption.append($('<div class="media-library__meta text-muted"></div>').text(meta.join(' • ')));
                $card.append($caption);
                $col.append($card);
                $container.append($col);
            });
        },

        renderMediaList: function ($container, assets) {
            var self = this;
            $container.empty();

            $.each(assets, function (_, asset) {
                var $row = $('<div class="media-library__list-item media-library__item" data-role="media-item"></div>');
                $row.attr('data-id', asset.id);
                $row.attr('data-type', asset.type);
                $row.attr('data-collection', asset.collection);

                var $title = $('<div class="media-library__list-title"></div>').text(asset.title);
                var metaParts = [];
                metaParts.push(self.getMediaTypeLabel(asset.type));
                if (asset.filename) {
                    metaParts.push(asset.filename);
                }
                if (asset.collectionName) {
                    metaParts.push(asset.collectionName);
                }
                if (asset.size) {
                    metaParts.push(self.formatFileSize(asset.size));
                }
                if (asset.duration) {
                    var durationLabel = self.formatMediaDuration(asset.duration);
                    if (durationLabel) {
                        metaParts.push(durationLabel);
                    }
                }
                if (asset.createdAt) {
                    metaParts.push(self.formatDateLabel(asset.createdAt));
                }

                var $meta = $('<div class="media-library__list-meta text-muted"></div>').text(metaParts.join(' • '));
                $row.append($title).append($meta);
                $container.append($row);
            });
        },

        setMediaLibraryViewMode: function (mode, $library) {
            var nextMode = mode === 'list' ? 'list' : 'grid';
            this.mediaLibraryViewMode = nextMode;
            $library.find('[data-role="media-view-mode"] button').removeClass('active');
            $library.find('[data-role="media-view-mode"] button[data-mode="' + nextMode + '"]').addClass('active');
            this.renderMediaLibrary($library);
        },

        toggleMediaSelection: function (assetId) {
            var id = String(assetId);
            var index = this.mediaLibrarySelection.indexOf(id);
            if (index === -1) {
                this.mediaLibrarySelection.push(id);
            } else {
                this.mediaLibrarySelection.splice(index, 1);
            }
        },

        syncMediaSelectionUI: function ($library) {
            var selected = this.mediaLibrarySelection;
            $library.find('[data-role="media-item"]').each(function () {
                var $item = $(this);
                var id = String($item.attr('data-id') || '');
                if (selected.indexOf(id) !== -1) {
                    $item.addClass('media-library__item--selected');
                } else {
                    $item.removeClass('media-library__item--selected');
                }
            });
            this.updateMediaSelectionSummary($library);
        },

        updateMediaSelectionSummary: function ($library) {
            var count = this.mediaLibrarySelection.length;
            $library.find('[data-role="selected-count"]').text(count);
        },

        clearMediaSelection: function ($library) {
            this.mediaLibrarySelection = [];
            this.syncMediaSelectionUI($library);
            $library.find('[data-role="media-selection-input"]').val('');
            $library.find('[data-role="media-selection-output"]').val('');
            var $feedback = $library.find('[data-role="media-selection-feedback"]');
            if ($feedback.length) {
                $feedback.removeClass('text-danger text-success').text('');
            }
        },

        outputMediaSelection: function ($library) {
            var payload = this.getMediaSelectionPayload();
            var $feedback = $library.find('[data-role="media-selection-feedback"]');

            if (!payload.length) {
                if ($feedback.length) {
                    $feedback.removeClass('text-success').addClass('text-danger').text('Выберите хотя бы один ассет для вставки.');
                }
                return;
            }

            var json = JSON.stringify(payload, null, 2);
            $library.find('[data-role="media-selection-input"]').val(json);
            $library.find('[data-role="media-selection-output"]').val(json);

            if ($feedback.length) {
                var titles = payload.map(function (item) { return item.title; });
                var preview = titles.slice(0, 3).join(', ');
                if (titles.length > 3) {
                    preview += ' и ещё ' + (titles.length - 3);
                }
                $feedback.removeClass('text-danger').addClass('text-success').text('Добавлено ' + payload.length + ' ассетов: ' + preview + '.');
            }
        },

        getMediaSelectionPayload: function () {
            var self = this;
            return this.mediaLibrarySelection.map(function (id) {
                var asset = self.findMediaAsset(id);
                if (!asset) {
                    return null;
                }

                return {
                    id: asset.id,
                    title: asset.title,
                    filename: asset.filename,
                    type: asset.type,
                    typeLabel: self.getMediaTypeLabel(asset.type),
                    size: asset.size,
                    sizeHuman: self.formatFileSize(asset.size),
                    url: asset.url,
                    preview: asset.preview || asset.thumb || '',
                    collection: asset.collection,
                    collectionName: asset.collectionName,
                    tags: asset.tags || [],
                    createdAt: asset.createdAt
                };
            }).filter(function (item) { return item !== null; });
        },

        findMediaAsset: function (assetId) {
            var key = String(assetId || '');
            if (!key) {
                return null;
            }

            return this.mediaAssetsIndex[key] || null;
        },

        formatFileSize: function (bytes) {
            var size = parseInt(bytes, 10);
            if (isNaN(size) || size <= 0) {
                return '';
            }

            var units = ['Б', 'КБ', 'МБ', 'ГБ'];
            var index = 0;
            var value = size;

            while (value >= 1024 && index < units.length - 1) {
                value = value / 1024;
                index += 1;
            }

            var formatted = index === 0 ? Math.round(value).toString() : value.toFixed(1);
            return formatted + ' ' + units[index];
        },

        formatMediaDuration: function (value) {
            var seconds = parseInt(value, 10);
            if (isNaN(seconds) || seconds <= 0) {
                return '';
            }

            var minutes = Math.floor(seconds / 60);
            var rest = seconds % 60;
            return minutes + ':' + (rest < 10 ? '0' + rest : rest);
        },

        formatDateLabel: function (value) {
            if (!value) {
                return '';
            }

            var date = new Date(value);
            if (date.toString() === 'Invalid Date') {
                return value;
            }

            var day = date.getDate();
            var month = date.getMonth() + 1;
            var year = date.getFullYear();
            var dayLabel = day < 10 ? '0' + day : String(day);
            var monthLabel = month < 10 ? '0' + month : String(month);

            return dayLabel + '.' + monthLabel + '.' + year;
        },

        getMediaTypeLabel: function (type) {
            var map = {
                image: 'Изображение',
                video: 'Видео',
                audio: 'Аудио',
                document: 'Документ',
                archive: 'Архив',
                other: 'Файл'
            };

            var key = String(type || 'other');
            return map[key] || map.other;
        },

        initTaxonomyTermsModule: function () {
            var $container = $('[data-role="taxonomy-terms"]');
            if (!$container.length) {
                return;
            }

            var self = this;
            this.getTaxonomyDataset().done(function (dataset) {
                self.populateTaxonomyFilter($container, dataset);

                var storedHandle = self.loadTaxonomyHandle();
                if (storedHandle && self.findTaxonomyByHandle(storedHandle)) {
                    self.taxonomyCurrentHandle = storedHandle;
                } else if (dataset.length) {
                    self.taxonomyCurrentHandle = dataset[0].handle;
                } else {
                    self.taxonomyCurrentHandle = '';
                }

                var $filter = $container.find('[data-role="taxonomy-filter"]');
                if ($filter.length) {
                    $filter.val(self.taxonomyCurrentHandle).trigger('change.select2');
                }

                self.bindTaxonomyEvents($container);
                self.renderTaxonomyTree($container);
            });
        },

        getTaxonomyDataset: function () {
            var deferred = $.Deferred();
            if (this.taxonomyDataset) {
                deferred.resolve(this.taxonomyDataset);
                return deferred.promise();
            }

            var dataset = [
                {
                    handle: 'topics',
                    name: 'Темы',
                    terms: [
                        {
                            id: 'analytics',
                            slug: 'analytics',
                            name: 'Аналитика',
                            usage: 48,
                            description: 'Материалы с аналитикой, исследованиями и статистикой.',
                            children: [
                                { id: 'audience-research', slug: 'audience-research', name: 'Исследования аудитории', usage: 14, children: [] },
                                { id: 'market-trends', slug: 'market-trends', name: 'Рыночные тренды', usage: 9, children: [] }
                            ]
                        },
                        {
                            id: 'marketing',
                            slug: 'marketing',
                            name: 'Маркетинг',
                            usage: 36,
                            children: [
                                { id: 'campaigns', slug: 'campaigns', name: 'Кампании', usage: 12, children: [] },
                                { id: 'social-media', slug: 'social-media', name: 'Соцсети', usage: 8, children: [] }
                            ]
                        },
                        {
                            id: 'workflow',
                            slug: 'workflow',
                            name: 'Процессы',
                            usage: 22,
                            children: [
                                { id: 'guidelines', slug: 'guidelines', name: 'Гайды', usage: 11, children: [] }
                            ]
                        }
                    ]
                },
                {
                    handle: 'channels',
                    name: 'Каналы распространения',
                    terms: [
                        { id: 'site', slug: 'site', name: 'Сайт', usage: 120, children: [] },
                        { id: 'magazine', slug: 'magazine', name: 'Журнал', usage: 45, children: [] },
                        {
                            id: 'newsletter',
                            slug: 'newsletter',
                            name: 'Рассылка',
                            usage: 60,
                            children: [
                                { id: 'newsletter-weekly', slug: 'newsletter-weekly', name: 'Еженедельная', usage: 25, children: [] },
                                { id: 'newsletter-special', slug: 'newsletter-special', name: 'Спецвыпуски', usage: 14, children: [] }
                            ]
                        }
                    ]
                },
                {
                    handle: 'regions',
                    name: 'Регионы',
                    terms: [
                        { id: 'moscow', slug: 'moscow', name: 'Москва', usage: 30, children: [] },
                        { id: 'spb', slug: 'spb', name: 'Санкт-Петербург', usage: 21, children: [] },
                        {
                            id: 'global',
                            slug: 'global',
                            name: 'Мир',
                            usage: 56,
                            children: [
                                { id: 'europe', slug: 'europe', name: 'Европа', usage: 16, children: [] },
                                { id: 'asia', slug: 'asia', name: 'Азия', usage: 12, children: [] }
                            ]
                        }
                    ]
                }
            ];

            var self = this;
            window.setTimeout(function () {
                self.taxonomyDataset = dataset;
                deferred.resolve(dataset);
            }, 120);

            return deferred.promise();
        },

        populateTaxonomyFilter: function ($container, dataset) {
            var $filter = $container.find('[data-role="taxonomy-filter"]');
            if (!$filter.length) {
                return;
            }

            $filter.empty();
            $filter.append($('<option></option>').attr('value', '').text('Все таксономии'));

            $.each(dataset, function (_, taxonomy) {
                if (!taxonomy || !taxonomy.handle) {
                    return;
                }

                $filter.append($('<option></option>').attr('value', taxonomy.handle).text(taxonomy.name || taxonomy.handle));
            });

            $filter.trigger('change.select2');
        },

        bindTaxonomyEvents: function ($container) {
            if ($container.data('taxonomyModuleBound')) {
                return;
            }
            $container.data('taxonomyModuleBound', true);

            var self = this;
            var searchTimer = null;

            $container.on('change', '[data-role="taxonomy-filter"]', function () {
                var value = $(this).val();
                self.taxonomyCurrentHandle = String(value || '');
                self.persistTaxonomyHandle(self.taxonomyCurrentHandle);
                self.renderTaxonomyTree($container);
            });

            $container.on('input', '[data-role="term-search"]', function () {
                var value = $(this).val();
                window.clearTimeout(searchTimer);
                searchTimer = window.setTimeout(function () {
                    self.taxonomySearchQuery = String(value || '').trim().toLowerCase();
                    self.renderTaxonomyTree($container);
                }, 200);
            });
        },

        renderTaxonomyTree: function ($container) {
            var $tree = $container.find('[data-role="terms-tree"]');
            var $empty = $container.find('[data-role="terms-empty"]');
            if (!$tree.length) {
                return;
            }

            var dataset = this.taxonomyDataset || [];
            var handle = this.taxonomyCurrentHandle || '';
            var taxonomy = handle ? this.findTaxonomyByHandle(handle) : (dataset[0] || null);

            if (!taxonomy) {
                $tree.empty();
                $empty.show();
                this.renderTaxonomySummary($container, null, []);
                return;
            }

            this.taxonomyCurrentIndex = {};
            this.buildTaxonomyIndex(taxonomy.terms || [], this.taxonomyCurrentIndex);

            var filtered = this.filterTaxonomyTerms(taxonomy.terms || [], this.taxonomySearchQuery || '');

            $tree.empty();
            if (!filtered.length) {
                $empty.show();
            } else {
                $empty.hide();
                var self = this;
                $.each(filtered, function (_, term) {
                    $tree.append(self.createTaxonomyTermNode(term, 0));
                });
            }

            this.destroyTaxonomySortable();
            this.attachTaxonomySortable($container);
            this.renderTaxonomySummary($container, taxonomy, filtered);
        },

        filterTaxonomyTerms: function (terms, query) {
            var self = this;
            var normalized = $.trim(query || '').toLowerCase();
            var result = [];

            $.each(terms, function (_, term) {
                if (!term || !term.id) {
                    return;
                }

                var children = term.children || [];
                var filteredChildren = self.filterTaxonomyTerms(children, normalized);
                var match = false;
                if (!normalized) {
                    match = true;
                } else {
                    var haystack = (term.name + ' ' + term.slug).toLowerCase();
                    match = haystack.indexOf(normalized) !== -1;
                }

                if (match || filteredChildren.length) {
                    var clone = $.extend(true, {}, term);
                    clone.children = filteredChildren;
                    clone._match = match && normalized !== '';
                    result.push(clone);
                }
            });

            return result;
        },

        createTaxonomyTermNode: function (term, depth) {
            var self = this;
            var $item = $('<li class="taxonomy-term-item"></li>');
            $item.attr('data-term-id', term.id);
            $item.attr('data-depth', depth);

            var $card = $('<div class="taxonomy-term-card" data-role="term-node"></div>');
            if (term._match) {
                $card.addClass('taxonomy-term-card--match');
            }

            var $header = $('<div class="taxonomy-term-card__header"></div>');
            var $handle = $('<span class="taxonomy-term-card__handle" data-role="term-drag-handle"><i class="fa fa-bars"></i></span>');
            $header.append($handle);

            var $labels = $('<div class="taxonomy-term-card__labels"></div>');
            $labels.append($('<strong class="taxonomy-term-card__title"></strong>').text(term.name || term.slug || term.id));
            if (term.slug) {
                $labels.append($('<code class="taxonomy-term-card__slug"></code>').text(term.slug));
            }
            $header.append($labels);

            if (typeof term.usage !== 'undefined') {
                $header.append($('<span class="badge taxonomy-term-card__badge"></span>').text(term.usage));
            }

            $card.append($header);

            if (term.description) {
                $card.append($('<div class="taxonomy-term-card__description text-muted"></div>').text(term.description));
            }

            $item.append($card);

            var $childrenList = $('<ul class="taxonomy-term-children list-unstyled" data-role="term-children"></ul>');
            $.each(term.children || [], function (_, child) {
                $childrenList.append(self.createTaxonomyTermNode(child, depth + 1));
            });
            $item.append($childrenList);

            return $item;
        },

        attachTaxonomySortable: function ($container) {
            if (typeof window.Sortable === 'undefined') {
                return;
            }

            var self = this;
            var $lists = $container.find('[data-role="terms-tree"], [data-role="term-children"]');

            $lists.each(function () {
                var element = this;
                if (element._taxonomySortable) {
                    element._taxonomySortable.destroy();
                }

                element._taxonomySortable = window.Sortable.create(element, {
                    group: 'taxonomy-terms',
                    animation: 150,
                    handle: '[data-role="term-drag-handle"]',
                    ghostClass: 'taxonomy-term-card--ghost',
                    onEnd: function () {
                        self.handleTaxonomyReorder($container);
                    }
                });

                self.taxonomySortableInstances.push(element._taxonomySortable);
            });
        },

        destroyTaxonomySortable: function () {
            $.each(this.taxonomySortableInstances || [], function (_, sortable) {
                if (sortable && typeof sortable.destroy === 'function') {
                    sortable.destroy();
                }
            });

            this.taxonomySortableInstances = [];
        },

        handleTaxonomyReorder: function ($container) {
            this.applyTaxonomyDomOrder($container);
            var self = this;
            window.setTimeout(function () {
                self.renderTaxonomyTree($container);
                self.showTaxonomyFeedback($container, 'Порядок терминов обновлён.', 'success');
            }, 60);
        },

        applyTaxonomyDomOrder: function ($container) {
            var handle = this.taxonomyCurrentHandle;
            if (!handle) {
                return;
            }

            var taxonomy = this.findTaxonomyByHandle(handle);
            if (!taxonomy) {
                return;
            }

            var $root = $container.find('[data-role="terms-tree"]').first();
            if (!$root.length) {
                return;
            }

            var structure = this.parseTaxonomyDom($root.get(0));
            taxonomy.terms = this.buildTaxonomyTreeFromStructure(structure, this.taxonomyCurrentIndex || {});
        },

        parseTaxonomyDom: function (element) {
            var self = this;
            var result = [];

            $(element).children('[data-term-id]').each(function () {
                var $item = $(this);
                var id = String($item.attr('data-term-id') || '');
                if (!id) {
                    return;
                }

                var $childrenList = $item.children('[data-role="term-children"]');
                var children = $childrenList.length ? self.parseTaxonomyDom($childrenList.get(0)) : [];
                result.push({ id: id, children: children });
            });

            return result;
        },

        buildTaxonomyIndex: function (terms, index) {
            var self = this;
            $.each(terms, function (_, term) {
                if (!term || !term.id) {
                    return;
                }

                index[term.id] = $.extend(true, {}, term);
                self.buildTaxonomyIndex(term.children || [], index);
            });
        },

        buildTaxonomyTreeFromStructure: function (structure, index) {
            var self = this;
            var result = [];

            $.each(structure || [], function (_, item) {
                if (!item || !item.id) {
                    return;
                }

                var source = index[item.id];
                if (!source) {
                    return;
                }

                var clone = $.extend(true, {}, source);
                clone.children = self.buildTaxonomyTreeFromStructure(item.children || [], index);
                result.push(clone);
            });

            return result;
        },

        countTaxonomyTerms: function (terms) {
            var self = this;
            var count = 0;

            $.each(terms || [], function (_, term) {
                count += 1;
                if (term.children && term.children.length) {
                    count += self.countTaxonomyTerms(term.children);
                }
            });

            return count;
        },

        renderTaxonomySummary: function ($container, taxonomy, filtered) {
            var $summary = $container.find('[data-role="terms-summary"]');
            if (!$summary.length) {
                return;
            }

            if (!taxonomy) {
                $summary.text('Выберите таксономию для работы с терминами.');
                return;
            }

            var total = this.countTaxonomyTerms(taxonomy.terms || []);
            var visible = this.countTaxonomyTerms(filtered || []);
            var message = 'Таксономия «' + (taxonomy.name || taxonomy.handle) + '»: ' + total + ' терминов.';
            if (visible !== total) {
                message += ' Отфильтровано: ' + visible + '.';
            }

            $summary.text(message);
        },

        showTaxonomyFeedback: function ($container, message, type) {
            var $feedback = $container.find('[data-role="terms-feedback"]');
            if (!$feedback.length) {
                return;
            }

            $feedback.removeClass('text-success text-danger');
            if (type === 'success') {
                $feedback.addClass('text-success');
            } else if (type === 'error') {
                $feedback.addClass('text-danger');
            }

            $feedback.text(message || '');
        },

        findTaxonomyByHandle: function (handle) {
            var dataset = this.taxonomyDataset || [];
            var target = String(handle || '');
            if (!target) {
                return null;
            }

            var found = null;
            $.each(dataset, function (_, taxonomy) {
                if (taxonomy && String(taxonomy.handle || '') === target) {
                    found = taxonomy;
                    return false;
                }

                return true;
            });

            return found;
        },

        loadTaxonomyHandle: function () {
            if (!window.localStorage) {
                return '';
            }

            try {
                return window.localStorage.getItem(this.taxonomyStorageKey) || '';
            } catch (error) {
                return '';
            }
        },

        persistTaxonomyHandle: function (handle) {
            if (!window.localStorage) {
                return;
            }

            try {
                window.localStorage.setItem(this.taxonomyStorageKey, String(handle || ''));
            } catch (error) {
                // ignore storage errors
            }
        },

        bindBulkActions: function () {
            $(document).on('click', '[data-action="bulk-update"]', function (event) {
                event.preventDefault();

                var $table = $('#activity-table');
                var ids = [];

                $table.find('tbody input[type="checkbox"]:checked').each(function () {
                    ids.push($(this).val());
                });

                var $target = $('#bulk-action-result');
                if (!$target.length) {
                    return;
                }

                if (!ids.length) {
                    $target
                        .removeClass('hidden')
                        .text('Выберите хотя бы одну запись для выполнения действия.');

                    return;
                }

                var action = $('[data-role="bulk-action"]').val() || 'custom';
                $target
                    .removeClass('hidden')
                    .text('Массовое действие «' + action + '» будет применено к ' + ids.length + ' элемент(ам).');
            });
        }
    };

    window.CMSDashboard = Dashboard;

    $(function () {
        Dashboard.init();
    });
})(jQuery);
