(function ($) {
    'use strict';

    var Dashboard = {
        activityTable: null,

        init: function () {
            this.initSelect2();
            this.initDataTables();
            this.bindSelectAll();
            this.bindFiltering();
            this.bindBulkActions();
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

        bindSelectAll: function () {
            $(document).on('change', '[data-role="select-all"]', function () {
                var checked = $(this).prop('checked');
                $(this)
                    .closest('table')
                    .find('tbody input[type="checkbox"]').prop('checked', checked);
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
