import { App } from '../core/bootstrap.js';

const CYRILLIC_TO_LATIN = {
    а: 'a',
    б: 'b',
    в: 'v',
    г: 'g',
    д: 'd',
    е: 'e',
    ё: 'e',
    ж: 'zh',
    з: 'z',
    и: 'i',
    й: 'y',
    к: 'k',
    л: 'l',
    м: 'm',
    н: 'n',
    о: 'o',
    п: 'p',
    р: 'r',
    с: 's',
    т: 't',
    у: 'u',
    ф: 'f',
    х: 'h',
    ц: 'ts',
    ч: 'ch',
    ш: 'sh',
    щ: 'sch',
    ъ: '',
    ы: 'y',
    ь: '',
    э: 'e',
    ю: 'yu',
    я: 'ya',
};

function slugify(value, fallback = '') {
    const lower = String(value ?? '')
        .trim()
        .toLowerCase();

    if (lower === '') {
        return fallback !== '' ? fallback : '';
    }

    let transliterated = '';

    for (const char of lower) {
        if (Object.prototype.hasOwnProperty.call(CYRILLIC_TO_LATIN, char)) {
            transliterated += CYRILLIC_TO_LATIN[char];
            continue;
        }

        transliterated += char;
    }

    const cleaned = transliterated
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');

    if (cleaned !== '') {
        return cleaned;
    }

    if (fallback !== '') {
        return fallback;
    }

    return `taxonomy-${Date.now()}`;
}

function formatDateTime(date) {
    const pad = (value) => String(value).padStart(2, '0');

    return `${pad(date.getDate())}.${pad(date.getMonth() + 1)}.${date.getFullYear()}, ${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

function parseInitialData(container) {
    const script = container.querySelector('script[data-role="taxonomies-data"]');
    if (!script) {
        return [];
    }

    try {
        const payload = script.textContent.trim();
        if (payload === '') {
            return [];
        }

        const data = JSON.parse(payload);

        return Array.isArray(data) ? data : [];
    } catch (error) {
        console.warn('Не удалось распарсить данные таксономий для предпросмотра.', error);

        return [];
    }
}

function normalizeTaxonomy(raw) {
    const id = Number(raw.id ?? 0);

    return {
        id: Number.isFinite(id) && id > 0 ? id : Date.now(),
        name: String(raw.name ?? ''),
        slug: String(raw.slug ?? ''),
        hierarchical: Boolean(raw.hierarchical ?? false),
        collectionsCount: Number(raw.collectionsCount ?? 0),
        updatedAt: String(raw.updatedAt ?? ''),
    };
}

function buildTermsUrl(baseUrl, taxonomyId) {
    if (!baseUrl) {
        return '#';
    }

    const separator = baseUrl.includes('?') ? '&' : '?';

    return `${baseUrl}${separator}taxonomy=${encodeURIComponent(String(taxonomyId))}`;
}

function createEmptyRow(colspan) {
    const row = document.createElement('tr');
    row.className = 'empty';

    const cell = document.createElement('td');
    cell.colSpan = colspan;
    cell.className = 'text-center text-muted';
    cell.textContent = 'Таксономии будут отображены после создания.';

    row.appendChild(cell);

    return row;
}

function buildTaxonomyRow(taxonomy, baseTermsUrl) {
    const row = document.createElement('tr');
    row.setAttribute('data-role', 'taxonomy-row');
    row.dataset.taxonomyId = String(taxonomy.id);

    const nameCell = document.createElement('td');
    const title = document.createElement('strong');
    title.textContent = taxonomy.name;
    nameCell.appendChild(title);

    const mobileMeta = document.createElement('div');
    mobileMeta.className = 'visible-xs text-muted small';

    const slugLabel = document.createElement('span');
    slugLabel.className = 'label label-default';
    slugLabel.textContent = taxonomy.slug;

    const metaSpan = document.createElement('span');
    metaSpan.className = 'taxonomy-meta';
    metaSpan.textContent = `${taxonomy.collectionsCount} коллекций · ${taxonomy.hierarchical ? 'Иерархическая' : 'Плоская'}`;

    mobileMeta.appendChild(slugLabel);
    mobileMeta.appendChild(document.createTextNode(' '));
    mobileMeta.appendChild(metaSpan);
    nameCell.appendChild(mobileMeta);

    const slugCell = document.createElement('td');
    slugCell.className = 'hidden-xs';
    slugCell.textContent = taxonomy.slug;

    const countCell = document.createElement('td');
    countCell.className = 'hidden-xs';
    countCell.textContent = String(taxonomy.collectionsCount);

    const updatedCell = document.createElement('td');
    updatedCell.className = 'hidden-xs';
    updatedCell.textContent = taxonomy.updatedAt;

    const actionsCell = document.createElement('td');
    actionsCell.className = 'text-right';

    const actionsGroup = document.createElement('div');
    actionsGroup.className = 'btn-group btn-group-xs';

    const viewLink = document.createElement('a');
    viewLink.className = 'btn btn-default';
    viewLink.setAttribute('data-pjax', '0');
    viewLink.setAttribute('title', 'Просмотр терминов');
    viewLink.setAttribute('href', buildTermsUrl(baseTermsUrl, taxonomy.id));
    viewLink.innerHTML = '<i class="fa fa-eye"></i>';

    const editButton = document.createElement('button');
    editButton.type = 'button';
    editButton.className = 'btn btn-primary';
    editButton.setAttribute('data-action', 'edit-taxonomy');
    editButton.setAttribute('data-taxonomy-id', String(taxonomy.id));
    editButton.setAttribute('title', 'Редактировать таксономию');
    editButton.innerHTML = '<i class="fa fa-pencil"></i>';

    actionsGroup.appendChild(viewLink);
    actionsGroup.appendChild(editButton);
    actionsCell.appendChild(actionsGroup);

    row.appendChild(nameCell);
    row.appendChild(slugCell);
    row.appendChild(countCell);
    row.appendChild(updatedCell);
    row.appendChild(actionsCell);

    return row;
}

function renderTaxonomies(tbody, taxonomies, baseTermsUrl) {
    if (!tbody) {
        return;
    }

    const fragment = document.createDocumentFragment();

    if (taxonomies.length === 0) {
        fragment.appendChild(createEmptyRow(5));
    } else {
        taxonomies.forEach((taxonomy) => {
            fragment.appendChild(buildTaxonomyRow(taxonomy, baseTermsUrl));
        });
    }

    tbody.innerHTML = '';
    tbody.appendChild(fragment);
}

const FEEDBACK_MODIFIERS = ['text-muted', 'text-success', 'text-danger'];

function setFeedbackMessage(element, message, state = 'muted') {
    if (!element) {
        return;
    }

    FEEDBACK_MODIFIERS.forEach((modifier) => {
        element.classList.remove(modifier);
    });

    element.textContent = message;

    if (message === '') {
        return;
    }

    if (state === 'success') {
        element.classList.add('text-success');
        return;
    }

    if (state === 'error') {
        element.classList.add('text-danger');
        return;
    }

    element.classList.add('text-muted');
}

function readFormData(form) {
    const nameInput = form.querySelector('[name="name"]');
    const slugInput = form.querySelector('[name="slug"]');
    const hierarchicalInput = form.querySelector('[name="hierarchical"]');

    return {
        name: nameInput ? nameInput.value.trim() : '',
        slug: slugInput ? slugInput.value.trim() : '',
        hierarchical: hierarchicalInput ? hierarchicalInput.checked : false,
    };
}

function fillForm(form, taxonomy) {
    const nameInput = form.querySelector('[name="name"]');
    const slugInput = form.querySelector('[name="slug"]');
    const hierarchicalInput = form.querySelector('[name="hierarchical"]');

    if (nameInput) {
        nameInput.value = taxonomy.name;
    }

    if (slugInput) {
        slugInput.value = taxonomy.slug;
    }

    if (hierarchicalInput) {
        hierarchicalInput.checked = Boolean(taxonomy.hierarchical);
    }
}

App.Modules['taxonomies.index'] = {
    init(root) {
        const container = root.querySelector('[data-role="taxonomies"]');
        if (!container) {
            return;
        }

        const $ = window.jQuery;
        if (!$) {
            return;
        }

        const table = container.querySelector('[data-role="taxonomies-table"]');
        const tbody = table ? table.querySelector('[data-role="taxonomies-list"]') : null;
        const feedback = container.querySelector('[data-role="taxonomies-feedback"]');
        const baseTermsUrl = container.getAttribute('data-terms-url') ?? '';

        if (!tbody) {
            return;
        }

        let taxonomies = parseInitialData(container).map(normalizeTaxonomy);
        let nextId = taxonomies.reduce((max, item) => (item.id > max ? item.id : max), 0) + 1;

        const createModal = document.getElementById('taxonomy-create');
        const editModal = document.getElementById('taxonomy-edit');
        const createForm = createModal ? createModal.querySelector('form[data-role="taxonomy-create-form"]') : null;
        const editForm = editModal ? editModal.querySelector('form[data-role="taxonomy-edit-form"]') : null;

        const updateTable = () => {
            renderTaxonomies(tbody, taxonomies, baseTermsUrl);
        };

        const resetCreateForm = () => {
            if (!createForm) {
                return;
            }

            createForm.reset();

            const slugInput = createForm.querySelector('[name="slug"]');
            if (slugInput) {
                slugInput.value = '';
            }
        };

        if (createForm) {
            createForm.addEventListener('submit', (event) => {
                event.preventDefault();
            });
        }

        if (editForm) {
            editForm.addEventListener('submit', (event) => {
                event.preventDefault();
            });
        }

        const createTrigger = container.querySelector('[data-action="create-taxonomy"]');
        if (createTrigger && createModal && createForm) {
            createTrigger.addEventListener('click', (event) => {
                event.preventDefault();
                resetCreateForm();
                setFeedbackMessage(feedback, '');
                $(createModal).modal('show');
            });
        }

        if (createModal && createForm) {
            const saveButton = createModal.querySelector('[data-action="save-taxonomy"]');
            if (saveButton) {
                saveButton.addEventListener('click', () => {
                    const data = readFormData(createForm);

                    if (data.name === '') {
                        setFeedbackMessage(feedback, 'Введите название таксономии.', 'error');
                        return;
                    }

                    if (data.slug === '') {
                        data.slug = slugify(data.name, `taxonomy-${nextId}`);

                        const slugInput = createForm.querySelector('[name="slug"]');
                        if (slugInput) {
                            slugInput.value = data.slug;
                        }
                    }

                    const taxonomy = {
                        id: nextId++,
                        name: data.name,
                        slug: data.slug,
                        hierarchical: data.hierarchical,
                        collectionsCount: 0,
                        updatedAt: formatDateTime(new Date()),
                    };

                    taxonomies.push(taxonomy);
                    updateTable();

                    $(createModal).modal('hide');
                    setFeedbackMessage(feedback, `Таксономия «${data.name}» успешно создана.`, 'success');
                    resetCreateForm();
                });
            }
        }

        if (table) {
            table.addEventListener('click', (event) => {
                const target = event.target instanceof Element ? event.target : null;
                if (!target) {
                    return;
                }

                const editButton = target.closest('[data-action="edit-taxonomy"]');
                if (!editButton) {
                    return;
                }

                event.preventDefault();

                const id = Number(editButton.getAttribute('data-taxonomy-id'));
                const taxonomy = taxonomies.find((item) => item.id === id);

                if (!taxonomy || !editModal || !editForm) {
                    return;
                }

                editForm.setAttribute('data-taxonomy-id', String(id));
                fillForm(editForm, taxonomy);
                setFeedbackMessage(feedback, '');
                $(editModal).modal('show');
            });
        }

        if (editModal && editForm) {
            const updateButton = editModal.querySelector('[data-action="update-taxonomy"]');
            if (updateButton) {
                updateButton.addEventListener('click', () => {
                    const id = Number(editForm.getAttribute('data-taxonomy-id'));
                    const taxonomy = taxonomies.find((item) => item.id === id);

                    if (!taxonomy) {
                        return;
                    }

                    const data = readFormData(editForm);

                    if (data.name === '') {
                        setFeedbackMessage(feedback, 'Название не может быть пустым.', 'error');
                        return;
                    }

                    if (data.slug === '') {
                        data.slug = slugify(data.name, `taxonomy-${taxonomy.id}`);

                        const slugInput = editForm.querySelector('[name="slug"]');
                        if (slugInput) {
                            slugInput.value = data.slug;
                        }
                    }

                    taxonomy.name = data.name;
                    taxonomy.slug = data.slug;
                    taxonomy.hierarchical = data.hierarchical;
                    taxonomy.updatedAt = formatDateTime(new Date());

                    updateTable();

                    $(editModal).modal('hide');
                    editForm.removeAttribute('data-taxonomy-id');
                    setFeedbackMessage(feedback, `Таксономия «${data.name}» обновлена.`, 'success');
                });
            }
        }

        updateTable();
    },
};
