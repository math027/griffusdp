/**
 * Reusable client-side pagination.
 *
 * Usage:
 *   const pager = new TablePaginator({
 *       tableId:    'tabelaFunc',       // id of the <table>
 *       containerId:'paginationBar',    // id of the pagination bar <div>
 *       sizes:      [10, 20, 50, 0],   // 0 = Todos
 *       defaultSize: 10
 *   });
 *
 *   // Call after every filter change:
 *   pager.reset();
 */
class TablePaginator {
    constructor({ tableId, containerId, sizes = [10, 20, 50, 0], defaultSize = 10 }) {
        this.table     = document.getElementById(tableId);
        this.container = document.getElementById(containerId);
        this.sizes     = sizes;
        this.pageSize  = defaultSize;
        this.currentPage = 1;

        if (!this.table || !this.container) return;
        this._buildUI();
        this.reset();
    }

    /** Build pagination bar HTML */
    _buildUI() {
        this.container.innerHTML = '';
        this.container.className = 'pagination-bar';

        // Info
        this.infoEl = document.createElement('div');
        this.infoEl.className = 'pg-info';
        this.container.appendChild(this.infoEl);

        // Controls wrapper
        const controls = document.createElement('div');
        controls.className = 'pg-controls';

        // Per-page buttons
        const perPage = document.createElement('div');
        perPage.className = 'pg-per-page';

        const label = document.createElement('span');
        label.textContent = 'Exibir:';
        perPage.appendChild(label);

        this.sizeButtons = [];
        this.sizes.forEach(size => {
            const btn = document.createElement('button');
            btn.className = 'pg-size-btn' + (size === this.pageSize ? ' active' : '');
            btn.textContent = size === 0 ? 'Todos' : size;
            btn.type = 'button';
            btn.addEventListener('click', () => this._setSize(size));
            perPage.appendChild(btn);
            this.sizeButtons.push({ btn, size });
        });

        controls.appendChild(perPage);

        // Nav buttons
        const nav = document.createElement('div');
        nav.className = 'pg-nav';

        this.prevBtn = document.createElement('button');
        this.prevBtn.className = 'pg-nav-btn';
        this.prevBtn.type = 'button';
        this.prevBtn.innerHTML = '<i class="fa-solid fa-chevron-left"></i>';
        this.prevBtn.addEventListener('click', () => this._goPage(this.currentPage - 1));
        nav.appendChild(this.prevBtn);

        this.pageNumEl = document.createElement('span');
        this.pageNumEl.className = 'pg-page-num';
        nav.appendChild(this.pageNumEl);

        this.nextBtn = document.createElement('button');
        this.nextBtn.className = 'pg-nav-btn';
        this.nextBtn.type = 'button';
        this.nextBtn.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';
        this.nextBtn.addEventListener('click', () => this._goPage(this.currentPage + 1));
        nav.appendChild(this.nextBtn);

        controls.appendChild(nav);
        this.container.appendChild(controls);
    }

    /** Get visible (not filtered-out) rows */
    _visibleRows() {
        const tbody = this.table.querySelector('tbody');
        if (!tbody) return [];
        return Array.from(tbody.querySelectorAll('tr')).filter(
            tr => tr.style.display !== 'none' && !tr.dataset._pgHidden
        );
    }

    /** Change page size */
    _setSize(size) {
        this.pageSize = size;
        this.currentPage = 1;
        this.sizeButtons.forEach(({ btn, size: s }) => {
            btn.classList.toggle('active', s === size);
        });
        this.apply();
    }

    /** Go to page */
    _goPage(p) {
        this.currentPage = p;
        this.apply();
    }

    /** Call this after filtering to reset to page 1 */
    reset() {
        // First, un-hide any pagination-hidden rows so filter can re-evaluate
        const tbody = this.table.querySelector('tbody');
        if (tbody) {
            tbody.querySelectorAll('tr[data-_pg-hidden="1"]').forEach(tr => {
                tr.style.display = '';
                delete tr.dataset._pgHidden;
            });
        }
        this.currentPage = 1;
    }

    /** Apply pagination after filters have run */
    apply() {
        // Collect rows that passed filtering (display !== 'none')
        const tbody = this.table.querySelector('tbody');
        if (!tbody) return;

        // Un-hide pagination-hidden rows first
        tbody.querySelectorAll('tr[data-_pg-hidden="1"]').forEach(tr => {
            tr.style.display = '';
            delete tr.dataset._pgHidden;
        });

        // Now get filtered-visible rows
        const allRows = Array.from(tbody.querySelectorAll('tr'));
        const visible = allRows.filter(tr => tr.style.display !== 'none');
        const total = visible.length;

        if (this.pageSize === 0 || total === 0) {
            // Show all
            this._updateInfo(total, total);
            this._updateNav(1, 1);
            return;
        }

        const totalPages = Math.ceil(total / this.pageSize);
        if (this.currentPage > totalPages) this.currentPage = totalPages;
        if (this.currentPage < 1) this.currentPage = 1;

        const start = (this.currentPage - 1) * this.pageSize;
        const end = start + this.pageSize;

        visible.forEach((tr, i) => {
            if (i < start || i >= end) {
                tr.style.display = 'none';
                tr.dataset._pgHidden = '1';
            }
        });

        this._updateInfo(Math.min(end, total) - start, total, start + 1, Math.min(end, total));
        this._updateNav(this.currentPage, totalPages);
    }

    _updateInfo(showing, total, from, to) {
        if (this.pageSize === 0 || total === 0) {
            this.infoEl.textContent = total + ' registro' + (total !== 1 ? 's' : '');
        } else {
            this.infoEl.textContent = from + '–' + to + ' de ' + total;
        }
    }

    _updateNav(current, totalPages) {
        this.prevBtn.disabled = current <= 1;
        this.nextBtn.disabled = current >= totalPages;
        this.pageNumEl.textContent = totalPages <= 1 ? '' : current + ' / ' + totalPages;
    }
}
