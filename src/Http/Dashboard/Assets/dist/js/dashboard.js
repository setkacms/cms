(function ($) {
    'use strict';

    var Dashboard = {
        activityTable: null,
        collectionsTable: null,
        collectionsSelection: {},
        collectionSavedViews: [],
        currentSavedViewId: null,
        isApplyingSavedView: false,
        collectionEntriesTable: null,
        collectionEntriesSelection: {},
        collectionEntriesSavedViews: [],
        collectionEntriesDefaultSavedViews: [],
        collectionEntriesCurrentViewId: null,
        isApplyingCollectionEntriesView: false,
        collectionEntriesCurrentNodeId: '',

        init: function () {
            this.initSelect2();
            this.initDataTables();
            this.initFlatpickr();
            this.initDropzone();
            this.initSortable();
            this.initTinyMCE();
            this.initCodeMirror();
            this.bindSelectAll();
            this.bindFiltering();
            this.bindBulkActions();
            this.bindCollectionsFilters();
            this.bindCollectionsBulkActions();
            this.initCollectionsSavedViews();
            this.bindCollectionsActionBar();
            this.initCollectionEntriesModule();
        },

        initSelect2: function () {
            if (!$.fn.select2) {
                return;
            }

            $('.select2').each(function () {
                var $element = $(this);
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

        initTinyMCE: function () {
            if (typeof window.tinymce === 'undefined') {
                return;
            }

            var selector = '#element-content';
            var $textarea = $(selector);

            if (!$textarea.length) {
                return;
            }

            if (window.tinymce.get($textarea.attr('id'))) {
                return;
            }

            window.tinymce.init({
                selector: selector,
                height: 360,
                menubar: false,
                branding: false,
                plugins: 'link lists code',
                toolbar: 'undo redo | bold italic underline | bullist numlist | link | code',
                setup: function (editor) {
                    editor.on('change keyup', function () {
                        editor.save();
                    });
                }
            });
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

    $(function () {
        Dashboard.init();
    });
})(jQuery);
