// User Complaints JavaScript
document.addEventListener('DOMContentLoaded', function () {
    // Select elements
    const deleteModal = document.getElementById('deleteModal');
    const deleteCancel = document.getElementById('deleteCancel');
    const deleteConfirm = document.getElementById('deleteConfirm');
    const deleteForm = document.getElementById('deleteForm');

    const searchInput = document.getElementById('searchInput');
    const tableBody = document.querySelector('#complaintsTable tbody');

    // Modal Control Functions
    function openModal(modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    // View Modal Logic
    const viewModal = document.getElementById('viewModal');
    const viewCancel = document.getElementById('viewCancel');
    const viewClose = document.getElementById('viewClose');

    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const title = this.getAttribute('data-title');
            const desc = this.getAttribute('data-desc');
            const cat = this.getAttribute('data-cat');
            const batch = this.getAttribute('data-batch');
            const date = this.getAttribute('data-date');
            const status = this.getAttribute('data-status');
            const message = this.getAttribute('data-message');
            const assigned = this.getAttribute('data-assigned');

            document.getElementById('viewTitle').textContent = title;
            document.getElementById('viewDesc').textContent = desc;
            document.getElementById('viewCat').textContent = cat;
            document.getElementById('viewBatch').textContent = batch;
            document.getElementById('viewDate').textContent = date;
            document.getElementById('viewMessage').textContent = message;
            document.getElementById('viewAssigned').textContent = assigned;

            const viewStatus = document.getElementById('viewStatus');
            viewStatus.textContent = status;

            // Set status color
            viewStatus.className = 'status-badge';
            if (status.toLowerCase().includes('progress')) {
                viewStatus.classList.add('status-progress');
            } else if (status.toLowerCase().includes('solved') || status.toLowerCase().includes('fixed')) {
                viewStatus.classList.add('status-solved');
            } else {
                viewStatus.classList.add('status-pending');
            }

            openModal(viewModal);
        });
    });

    if (viewClose) viewClose.addEventListener('click', () => closeModal(viewModal));
    if (viewCancel) viewCancel.addEventListener('click', () => closeModal(viewModal));

    // Delete Modal Logic
    let complaintIdToDelete = null;
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            complaintIdToDelete = this.getAttribute('data-id');
            openModal(deleteModal);
        });
    });

    if (deleteCancel) deleteCancel.addEventListener('click', () => {
        complaintIdToDelete = null;
        closeModal(deleteModal);
    });

    if (deleteConfirm) {
        deleteConfirm.addEventListener('click', () => {
            if (complaintIdToDelete) {
                document.getElementById('deleteCompId').value = complaintIdToDelete;
                deleteForm.submit();
            }
        });
    }

    // Close on outside click
    window.addEventListener('click', (e) => {
        if (e.target === deleteModal) closeModal(deleteModal);
        if (e.target === viewModal) closeModal(viewModal);
    });

    // ── Search + Pagination ─────────────────────────────────────────────────
    const complaintsTableBody = document.querySelector('#complaintsTable tbody');

    const pager = initTablePagination({
        tableBodyId    : null,          // we pass the element directly below
        entriesSelectId: 'entriesPerPage',
        entriesInfoId  : null,          // info is inside .pagination-info
        prevBtnId      : null,
        nextBtnId      : null,
    });

    // Build a local pagination that works with complaintsTable tbody
    (function () {
        const tbody        = document.querySelector('#complaintsTable tbody');
        const entriesSel   = document.getElementById('entriesPerPage');
        const infoEl       = document.querySelector('.pagination-info');
        const prevBtnEl    = document.getElementById('prevComplaints');
        const nextBtnEl    = document.getElementById('nextComplaints');
        let currentPage    = 1;
        let rowsPerPage    = entriesSel ? parseInt(entriesSel.value, 10) : 10;

        function getDataRows() {
            return Array.from(tbody.querySelectorAll('tr')).filter(r => !r.querySelector('td[colspan]'));
        }
        function getVisible() {
            return getDataRows().filter(r => r.dataset.hiddenBySearch !== 'true');
        }
        function render() {
            const rows = getVisible();
            const total = rows.length;
            const totalPages = Math.max(1, Math.ceil(total / rowsPerPage));
            if (currentPage < 1) currentPage = 1;
            if (currentPage > totalPages) currentPage = totalPages;
            const start = (currentPage - 1) * rowsPerPage;
            const end   = start + rowsPerPage;

            getDataRows().forEach(r => r.style.display = 'none');
            rows.forEach((r, i) => { r.style.display = (i >= start && i < end) ? '' : 'none'; });

            if (infoEl) {
                if (total === 0) {
                    infoEl.textContent = 'Showing 0 to 0 of 0 entries';
                } else {
                    infoEl.textContent = `Showing ${start + 1} to ${Math.min(end, total)} of ${total} entries`;
                }
            }
            const emptyRow = tbody.querySelector('tr td[colspan]')?.closest('tr');
            if (emptyRow) emptyRow.style.display = total === 0 ? '' : 'none';
        }

        window._complaintsTableRefresh = function () { currentPage = 1; render(); };

        if (entriesSel) {
            entriesSel.addEventListener('change', function () {
                rowsPerPage = parseInt(this.value, 10);
                currentPage = 1;
                render();
            });
        }

        render();
    })();

    if (searchInput && tableBody) {
        searchInput.addEventListener('input', function () {
            const term = this.value.toLowerCase();
            tableBody.querySelectorAll('tr').forEach(row => {
                if (row.querySelector('td[colspan]')) return;
                const match = row.textContent.toLowerCase().includes(term);
                row.dataset.hiddenBySearch = match ? 'false' : 'true';
            });
            if (window._complaintsTableRefresh) window._complaintsTableRefresh();
        });
    }

});