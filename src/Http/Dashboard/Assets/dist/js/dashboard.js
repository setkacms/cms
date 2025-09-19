(function ($) {
    'use strict';

    var Dashboard = {
        activityTable: null,
        collectionsTable: null,
        collectionsSelection: {},
        collectionSavedViews: [],
        currentSavedViewId: null,
        isApplyingSavedView: false,

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
