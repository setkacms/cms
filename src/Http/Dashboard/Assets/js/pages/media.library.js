import Dashboard from '../core/legacy.js';
import { App } from '../core/bootstrap.js';

const $ = window.jQuery;

App.Modules['media.library'] = {
    init(root) {
        Dashboard.initMediaLibraryModule();

        if (!$ || !root) {
            return;
        }

        const $root = $(root);
        const $library = $root.find('[data-role="media-library"]').first();
        const $modal = $root.find('[data-role="media-bulk-modal"]').first();

        if (!$library.length || !$modal.length) {
            return;
        }

        Dashboard.getMediaAssets().done(() => {
            setupBulkModal($library, $modal);
        });
    },
};

function setupBulkModal($library, $modal) {
    const $feedback = $modal.find('[data-role="media-bulk-feedback"]');
    const $result = $modal.find('[data-role="media-bulk-result"]');
    const $resultList = $modal.find('[data-role="media-bulk-result-list"]');

    function parseTags(value) {
        if (!value) {
            return [];
        }

        return String(value)
            .split(/[\n,]/)
            .map((tag) => tag.trim())
            .filter((tag) => tag.length > 0)
            .filter((tag, index, array) => array.indexOf(tag) === index);
    }

    function clearFeedback() {
        if ($feedback.length) {
            $feedback.removeClass('alert-success alert-info alert-warning alert-danger').hide().text('');
        }
        if ($result.length) {
            $result.hide();
        }
        if ($resultList.length) {
            $resultList.empty();
        }
    }

    function showFeedback(message, type = 'info') {
        if (!$feedback.length) {
            return;
        }

        const normalized = type === 'error' ? 'danger' : ['success', 'info', 'warning', 'danger'].includes(type) ? type : 'info';
        $feedback
            .removeClass('alert-success alert-info alert-warning alert-danger')
            .addClass(`alert-${normalized}`)
            .text(message)
            .show();
    }

    function renderResultList(items) {
        if (!$result.length || !$resultList.length) {
            return;
        }

        if (!items.length) {
            $result.hide();
            $resultList.empty();
            return;
        }

        const limit = 8;
        $resultList.empty();
        items.slice(0, limit).forEach((asset) => {
            if (!asset) {
                return;
            }
            const title = asset.title || asset.filename || asset.id;
            const collection = asset.collectionName ? ` — ${asset.collectionName}` : '';
            $resultList.append($('<li></li>').text(title + collection));
        });

        if (items.length > limit) {
            $resultList.append($('<li class="text-muted"></li>').text(`… и ещё ${items.length - limit}`));
        }

        $result.show();
    }

    function showResultSummary(result, message, type = 'success') {
        const changed = (result && Array.isArray(result.changed)) ? result.changed.filter(Boolean) : [];
        const total = result && typeof result.total === 'number' ? result.total : changed.length;
        const summaryMessage = changed.length ? `${message} (${changed.length} из ${total}).` : message;

        showFeedback(summaryMessage, type);
        renderResultList(changed);
        updateSelectionSummary();
    }

    function updateSelectionSummary() {
        const selected = Dashboard.getSelectedMediaAssets();
        const count = selected.length;

        const $summary = $modal.find('[data-role="media-bulk-selection-summary"]');
        if ($summary.length) {
            if (!count) {
                $summary.text('Нет выбранных файлов. Выберите элементы в медиатеке, чтобы применить операции.');
            } else {
                const titles = selected.map((asset) => asset.title || asset.filename || asset.id);
                const preview = titles.slice(0, 3).join(', ');
                const rest = titles.length > 3 ? ` и ещё ${titles.length - 3}` : '';
                $summary.text(`Выбрано ${count}: ${preview}${rest}`);
            }
        }

        const $empty = $modal.find('[data-role="media-bulk-empty"]');
        if ($empty.length) {
            $empty.toggle(count === 0);
        }
    }

    function ensureSelection() {
        const selected = Dashboard.getSelectedMediaAssets();
        if (!selected.length) {
            updateSelectionSummary();
            showFeedback('Сначала выберите хотя бы один файл в библиотеке.', 'danger');
            return null;
        }

        return selected;
    }

    function resetForms() {
        $modal.find('[data-role="bulk-tags-add"]').val('');
        $modal.find('[data-role="bulk-tags-remove"]').val('');
        $modal.find('[data-role="bulk-tags-replace"]').prop('checked', false);

        const $collectionSelect = $modal.find('[data-role="bulk-collection-select"]');
        $collectionSelect.val('');
        if ($collectionSelect.data('select2')) {
            $collectionSelect.trigger('change.select2');
        }

        $modal.find('[name="media-bulk-delete-mode"][value="delete"]').prop('checked', true);
    }

    function populateCollectionOptions() {
        const $select = $modal.find('[data-role="bulk-collection-select"]');
        if (!$select.length) {
            return;
        }

        const current = $select.val();
        const options = Dashboard.getMediaCollectionsCatalog()
            .sort((a, b) => a.label.localeCompare(b.label));

        $select.find('option').not('[value=""]').remove();
        options.forEach((item) => {
            const option = $('<option></option>')
                .attr('value', item.value)
                .attr('data-label', item.label)
                .text(item.label);
            $select.append(option);
        });

        if (current && $select.find(`option[value="${current}"]`).length) {
            $select.val(current);
        } else {
            $select.val('');
        }

        if ($select.data('select2')) {
            $select.trigger('change.select2');
        }
    }

    updateSelectionSummary();
    populateCollectionOptions();
    clearFeedback();

    $(document).on('mediaLibrary:selection-changed.mediaBulk', updateSelectionSummary);

    $modal.on('show.bs.modal', () => {
        populateCollectionOptions();
        resetForms();
        clearFeedback();
        updateSelectionSummary();
    });

    $modal.on('hidden.bs.modal', () => {
        clearFeedback();
    });

    $modal.on('submit', '[data-role="bulk-tags-form"]', (event) => {
        event.preventDefault();
        clearFeedback();

        const selected = ensureSelection();
        if (!selected) {
            return;
        }

        const add = parseTags($modal.find('[data-role="bulk-tags-add"]').val());
        const remove = parseTags($modal.find('[data-role="bulk-tags-remove"]').val());
        const replace = $modal.find('[data-role="bulk-tags-replace"]').is(':checked');

        if (!add.length && !remove.length && !replace) {
            showFeedback('Укажите теги для добавления, удаления или включите перезапись.', 'warning');
            return;
        }

        const result = Dashboard.applyMediaBulkTags($library, { add, remove, replace });

        if (!result.changed.length) {
            showFeedback('Выбранные ассеты уже содержат указанные теги.', 'info');
            updateSelectionSummary();
            return;
        }

        showResultSummary(result, 'Теги обновлены.', 'success');
    });

    $modal.on('submit', '[data-role="bulk-collection-form"]', (event) => {
        event.preventDefault();
        clearFeedback();

        const selected = ensureSelection();
        if (!selected) {
            return;
        }

        const $select = $modal.find('[data-role="bulk-collection-select"]');
        const value = ($select.val() || '').trim();
        if (!value) {
            showFeedback('Выберите коллекцию для перемещения.', 'warning');
            return;
        }

        const $selectedOption = $select.find('option:selected').first();
        const label = ($selectedOption.data('label') || $selectedOption.text() || value).trim();

        const result = Dashboard.applyMediaBulkCollection($library, {
            collection: value,
            collectionName: label,
        });

        if (!result.changed.length) {
            showFeedback('Ассеты уже находятся в выбранной коллекции.', 'info');
            updateSelectionSummary();
            return;
        }

        populateCollectionOptions();
        showResultSummary(result, 'Коллекция обновлена.', 'success');
    });

    $modal.on('submit', '[data-role="bulk-delete-form"]', (event) => {
        event.preventDefault();
        clearFeedback();

        const selected = ensureSelection();
        if (!selected) {
            return;
        }

        const mode = $modal.find('[name="media-bulk-delete-mode"]:checked').val() || 'delete';
        const result = Dashboard.applyMediaBulkDeletion($library, { mode });

        if (!result.changed.length) {
            const message = mode === 'delete'
                ? 'Выбранные ассеты уже помечены для удаления.'
                : 'Ассеты не были помечены для удаления.';
            showFeedback(message, 'info');
            updateSelectionSummary();
            return;
        }

        const message = mode === 'delete'
            ? 'Ассеты помечены для удаления.'
            : 'Пометка на удаление снята.';
        const tone = mode === 'delete' ? 'warning' : 'success';
        showResultSummary(result, message, tone);
    });
}
