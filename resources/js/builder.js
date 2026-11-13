/**
 * Visual page builder state machine.
 *
 * Blocks are a tree of { id, type, attrs, hidden, children }. The canvas renders
 * server-side HTML for each block (shipped in `config.templates`) and Alpine
 * rewrites only the bound text/media attributes, so typing in the inspector
 * never triggers a network request. The tree is posted back on save.
 */
export function pageBuilder(config) {
    return {
        page: config.page,
        blocks: config.blocks ?? [],
        library: config.library ?? {},
        templates: config.templates ?? {},
        media: config.media ?? [],
        products: config.products ?? [],
        csrf: config.csrf,
        endpoints: {
            save: config.endpoint,
            publish: config.publishEndpoint,
        },

        selectedId: null,
        editingId: null,
        addPanelOpen: false,
        saving: false,
        publishing: false,
        dirty: false,
        lastSavedAt: null,
        error: null,
        notice: null,

        history: [],
        future: [],
        autosaveTimer: null,

        init() {
            this.selectedId = this.blocks[0]?.id ?? null;

            window.addEventListener('beforeunload', (event) => {
                if (!this.dirty) return;
                event.preventDefault();
                event.returnValue = '';
            });
        },

        /* ------------------------------------------------------------- tree */

        get selected() {
            return this.findBlock(this.selectedId);
        },

        get selectedBlock() {
            return this.selected;
        },

        findBlock(id, list = this.blocks) {
            for (const block of list) {
                if (block.id === id) return block;
                const child = this.findBlock(id, block.children ?? []);
                if (child) return child;
            }
            return null;
        },

        findParent(id, list = this.blocks) {
            for (const block of list) {
                if ((block.children ?? []).some((child) => child.id === id)) return block;
                const parent = this.findParent(id, block.children ?? []);
                if (parent) return parent;
            }
            return null;
        },

        removeFromTree(id, list = this.blocks) {
            const index = list.findIndex((block) => block.id === id);
            if (index >= 0) return list.splice(index, 1)[0];

            for (const block of list) {
                const removed = this.removeFromTree(id, block.children ?? []);
                if (removed) return removed;
            }
            return null;
        },

        newId() {
            return 'blk_' + Math.random().toString(36).slice(2, 12);
        },

        defaultsFor(type) {
            const definition = Object.values(this.library)
                .flatMap((group) => Object.entries(group))
                .find(([key]) => key === type);

            if (!definition) return {};

            return Object.fromEntries(
                Object.entries(definition[1].attrs ?? {}).map(([name, meta]) => [name, meta.default ?? null]),
            );
        },

        /* ---------------------------------------------------------- history */

        commit() {
            this.history.push(JSON.stringify(this.blocks));
            if (this.history.length > 50) this.history.shift();
            this.future = [];
            this.markDirty();
        },

        undo() {
            if (this.history.length === 0) return;
            this.future.push(JSON.stringify(this.blocks));
            this.blocks = JSON.parse(this.history.pop());
            this.scheduleAutosave();
        },

        redo() {
            if (this.future.length === 0) return;
            this.history.push(JSON.stringify(this.blocks));
            this.blocks = JSON.parse(this.future.pop());
            this.scheduleAutosave();
        },

        markDirty() {
            this.dirty = true;
            this.scheduleAutosave();
        },

        /* --------------------------------------------------------- mutation */

        addBlock(type, parentId = null) {
            this.commit();

            const block = {
                id: this.newId(),
                type,
                attrs: this.defaultsFor(type),
                hidden: false,
                children: [],
            };

            if (parentId) {
                const parent = this.findBlock(parentId);
                if (parent) {
                    parent.children = parent.children ?? [];
                    parent.children.push(block);
                } else {
                    this.blocks.push(block);
                }
            } else {
                const index = this.blocks.findIndex((candidate) => candidate.id === this.selectedId);
                if (index >= 0) this.blocks.splice(index + 1, 0, block);
                else this.blocks.push(block);
            }

            this.selectedId = block.id;
            this.addPanelOpen = false;

            return block;
        },

        duplicateBlock(id) {
            const source = this.findBlock(id);
            if (!source) return;

            this.commit();

            const clone = (block) => ({
                ...structuredClone(block),
                id: this.newId(),
                children: (block.children ?? []).map(clone),
            });

            const copy = clone(source);
            copy.id = this.newId();

            const siblings = this.siblingsOf(id);
            const index = siblings.findIndex((block) => block.id === id);
            siblings.splice(index + 1, 0, copy);
            this.selectedId = copy.id;
        },

        removeBlock(id) {
            this.commit();
            this.removeFromTree(id);

            if (this.selectedId === id) this.selectedId = this.blocks[0]?.id ?? null;
        },

        toggleHidden(id) {
            const block = this.findBlock(id);
            if (!block) return;

            this.commit();
            block.hidden = !block.hidden;
        },

        siblingsOf(id) {
            const parent = this.findParent(id);
            return parent ? (parent.children ??= []) : this.blocks;
        },

        moveUp(id) {
            const siblings = this.siblingsOf(id);
            const index = siblings.findIndex((block) => block.id === id);
            if (index <= 0) return;

            this.commit();
            [siblings[index - 1], siblings[index]] = [siblings[index], siblings[index - 1]];
        },

        moveDown(id) {
            const siblings = this.siblingsOf(id);
            const index = siblings.findIndex((block) => block.id === id);
            if (index < 0 || index >= siblings.length - 1) return;

            this.commit();
            [siblings[index + 1], siblings[index]] = [siblings[index], siblings[index + 1]];
        },

        /** Replace a block's attrs wholesale (bulk edits such as a repeater). */
        replaceAttrs(id, attrs) {
            const block = this.findBlock(id);
            if (!block) return;

            this.commit();
            block.attrs = { ...block.attrs, ...attrs };
        },

        /* --------------------------------------------------------- inspector */

        setAttr(name, value) {
            if (!this.selected) return;

            if (this.selected.attrs?.[name] === value) return;

            /* First keystroke records history; later ones do not flood it. */
            if (this.editingId !== this.selected.id + ':' + name) {
                this.commit();
                this.editingId = this.selected.id + ':' + name;
            }

            this.selected.attrs = { ...this.selected.attrs, [name]: value };
            this.markDirty();
        },

        endEdit() {
            this.editingId = null;
        },

        /* ------------------------------------------------------------ canvas */

        /** Server-rendered default markup for a type, used when nothing is cached. */
        templateFor(type) {
            return this.templates[type] ?? '';
        },

        /** Progressive enhancement: replace placeholders with the live attrs. */
        renderPreview(block) {
            const markup = this.templateFor(block.type);
            if (!markup) return '';

            const doc = new DOMParser().parseFromString(markup, 'text/html');

            doc.querySelectorAll('[data-bind]').forEach((node) => {
                const key = node.getAttribute('data-bind');
                let value = block.attrs?.[key];

                if (value === null || value === undefined || value === '') {
                    node.textContent = '';
                    return;
                }

                if (node.tagName === 'IMG') {
                    const asset = this.media.find((item) => String(item.id) === String(value));
                    node.setAttribute('src', asset?.url ?? '');
                } else if (node.tagName === 'A') {
                    node.setAttribute('href', value);
                } else {
                    node.textContent = value;
                }
            });

            doc.querySelectorAll('[data-href]').forEach((node) => {
                const value = block.attrs?.[node.getAttribute('data-href')];
                if (value) node.setAttribute('href', value);
            });

            return doc.body.innerHTML;
        },

        blockLabel(block) {
            for (const group of Object.values(this.library)) {
                const entry = group[block.type];
                if (entry) return entry.label;
            }
            return 'Block';
        },

        blockDescription(block) {
            for (const group of Object.values(this.library)) {
                const entry = group[block.type];
                if (entry) return entry.description;
            }
            return '';
        },

        attrDefinition(key) {
            for (const group of Object.values(this.library)) {
                for (const entry of Object.values(group)) {
                    if (entry.attrs && entry.attrs[key]) return entry.attrs[key];
                }
            }
            return null;
        },

        attrsFor(block) {
            for (const group of Object.values(this.library)) {
                const entry = group[block.type];
                if (entry) return entry.attrs ?? {};
            }
            return {};
        },

        /* ------------------------------------------------------------- saving */

        scheduleAutosave() {
            if (!this.endpoints.save) return;

            clearTimeout(this.autosaveTimer);
            this.autosaveTimer = setTimeout(() => this.save({ autosave: true }), 2500);
        },

        async save({ autosave = false } = {}) {
            if (this.saving) return;
            if (!this.endpoints.save) return;

            this.saving = true;
            this.error = null;

            try {
                const response = await fetch(this.endpoints.save, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                    },
                    body: JSON.stringify({ content: this.blocks, autosave }),
                });

                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(payload.message ?? 'Save failed.');
                }

                this.lastSavedAt = payload.saved_at ?? new Date().toISOString();
                this.dirty = false;
                this.notice = autosave ? 'Draft autosaved' : 'Draft saved';
                setTimeout(() => (this.notice = null), 2500);
            } catch (err) {
                this.error = err.message ?? 'Could not save.';
            } finally {
                this.saving = false;
            }
        },

        async publish() {
            if (!this.endpoints.publish) return;

            this.publishing = true;
            this.error = null;

            try {
                await this.save({ autosave: false });

                const response = await fetch(this.endpoints.publish, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                    },
                });

                const payload = await response.json().catch(() => ({}));

                if (!response.ok) throw new Error(payload.message ?? 'Publish failed.');

                this.page.status = 'published';
                this.page.has_unpublished_changes = false;
                this.notice = 'Published — the live site is updated';
            } catch (err) {
                this.error = err.message ?? 'Could not publish.';
            } finally {
                this.publishing = false;
            }
        },

        formatTime(value) {
            if (!value) return 'never';
            return new Date(value).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        },
    };
}
