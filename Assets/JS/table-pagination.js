/**
 * table-pagination.js
 * Shared client-side pagination utility for all table pages.
 *
 * Usage:
 *   const pager = initTablePagination({
 *     tableBodyId   : 'tableBody',          // <tbody id="...">
 *     entriesSelectId: 'entriesSelect',     // <select id="..."> for rows per page
 *     entriesInfoId  : 'entriesInfo',       // <div id="..."> showing "Showing X to Y of Z"
 *     prevBtnId      : 'prevBtn',           // optional <button id="...">
 *     nextBtnId      : 'nextBtn',           // optional <button id="...">
 *     skipColspan    : true,                // skip rows that have a colspan (empty-state rows)
 *   });
 *
 *   // Call pager.refresh() after any external filter (e.g. search) changes row visibility.
 */
function initTablePagination(options) {
    const {
        tableBodyId    = 'tableBody',
        entriesSelectId = 'entriesSelect',
        entriesInfoId  = 'entriesInfo',
        prevBtnId      = 'prevBtn',
        nextBtnId      = 'nextBtn',
        skipColspan    = true,
    } = options || {};

    const tbody        = document.getElementById(tableBodyId);
    const entriesSelect = document.getElementById(entriesSelectId);
    const entriesInfo  = document.getElementById(entriesInfoId);
    const prevBtn      = document.getElementById(prevBtnId);
    const nextBtn      = document.getElementById(nextBtnId);

    if (!tbody) return { refresh: () => {} };   // nothing to do

    let currentPage    = 1;
    let rowsPerPage    = entriesSelect ? parseInt(entriesSelect.value, 10) : 10;

    /** Return all data rows (excluding empty-state colspan rows if skipColspan). */
    function getDataRows() {
        return Array.from(tbody.querySelectorAll('tr')).filter(row => {
            if (skipColspan && row.querySelector('td[colspan]')) return false;
            return true;
        });
    }

    /** Return rows that are not hidden by the search filter. */
    function getVisibleRows() {
        return getDataRows().filter(row => row.dataset.hiddenBySearch !== 'true');
    }

    /** Apply pagination: show only the rows for the current page. */
    function applyPagination() {
        const rows      = getVisibleRows();
        const total     = rows.length;
        const totalPages = Math.max(1, Math.ceil(total / rowsPerPage));

        // Clamp currentPage
        if (currentPage < 1) currentPage = 1;
        if (currentPage > totalPages) currentPage = totalPages;

        const start = (currentPage - 1) * rowsPerPage;   // 0-based
        const end   = start + rowsPerPage;

        // First hide ALL data rows, then show only the current page slice
        getDataRows().forEach(row => {
            row.style.display = 'none';
        });
        rows.forEach((row, idx) => {
            row.style.display = (idx >= start && idx < end) ? '' : 'none';
        });

        // Update entries info label
        if (entriesInfo) {
            if (total === 0) {
                entriesInfo.textContent = 'Showing 0 to 0 of 0 entries';
            } else {
                const showing_from = start + 1;
                const showing_to   = Math.min(end, total);
                entriesInfo.textContent =
                    `Showing ${showing_from} to ${showing_to} of ${total} entries`;
            }
        }

        // Update button states
        if (prevBtn) prevBtn.disabled = (currentPage <= 1);
        if (nextBtn) nextBtn.disabled = (currentPage >= totalPages);

        // Show empty-state row if no visible rows
        const emptyRow = tbody.querySelector('tr td[colspan]')?.closest('tr');
        if (emptyRow) {
            emptyRow.style.display = (total === 0) ? '' : 'none';
        }
    }

    // Entries-per-page change
    if (entriesSelect) {
        entriesSelect.addEventListener('change', function () {
            rowsPerPage = parseInt(this.value, 10);
            currentPage = 1;
            applyPagination();
        });
    }

    // Prev / Next navigation
    if (prevBtn) {
        prevBtn.addEventListener('click', function () {
            if (currentPage > 1) {
                currentPage--;
                applyPagination();
            }
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function () {
            const total = getVisibleRows().length;
            const totalPages = Math.max(1, Math.ceil(total / rowsPerPage));
            if (currentPage < totalPages) {
                currentPage++;
                applyPagination();
            }
        });
    }

    // Run on init
    applyPagination();

    /**
     * Call this after any external filter (search) has toggled row visibility.
     * Mark rows hidden by search with a data attribute so pagination knows
     * to exclude them from its pool.
     */
    function refresh() {
        currentPage = 1;
        applyPagination();
    }

    return { refresh, applyPagination };
}
