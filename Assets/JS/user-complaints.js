// User Complaints JavaScript
document.addEventListener('DOMContentLoaded', function () {
    // Select elements
    const deleteModal = document.getElementById('deleteModal');
    const deleteCancel = document.getElementById('deleteCancel');
    const deleteConfirm = document.getElementById('deleteConfirm');
    const deleteForm = document.getElementById('deleteForm');

    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('tableBody');
    const viewModal = document.getElementById('viewModal');
    const viewCancel = document.getElementById('viewCancel');
    const viewClose = document.getElementById('viewClose');

    let complaintIdToDelete = null;

    // Modal Control Functions
    function openModal(modal) {
        if (!modal) return;
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modal) {
        if (!modal) return;
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    // Event Delegation for Table Actions
    if (tableBody) {
        tableBody.addEventListener('click', function (e) {
            // View Button
            const viewBtn = e.target.closest('.view-btn');
            if (viewBtn) {
                const title = viewBtn.getAttribute('data-title');
                const desc = viewBtn.getAttribute('data-desc');
                const cat = viewBtn.getAttribute('data-cat');
                const batch = viewBtn.getAttribute('data-batch');
                const date = viewBtn.getAttribute('data-date');
                const status = viewBtn.getAttribute('data-status');
                const message = viewBtn.getAttribute('data-message');
                const assigned = viewBtn.getAttribute('data-assigned');

                document.getElementById('viewTitle').textContent = title;
                document.getElementById('viewDesc').textContent = desc;
                document.getElementById('viewCat').textContent = cat;
                document.getElementById('viewBatch').textContent = batch;
                document.getElementById('viewDate').textContent = date;
                document.getElementById('viewMessage').textContent = message || 'No message yet';
                document.getElementById('viewAssigned').textContent = assigned;

                const viewStatus = document.getElementById('viewStatus');
                if (viewStatus) {
                    viewStatus.textContent = status;
                    viewStatus.className = 'status-badge';
                    const lowerStatus = status.toLowerCase();
                    if (lowerStatus.includes('progress')) {
                        viewStatus.classList.add('status-progress');
                    } else if (lowerStatus.includes('solved') || lowerStatus.includes('fixed')) {
                        viewStatus.classList.add('status-solved');
                    } else if (lowerStatus.includes('unresolved')) {
                        viewStatus.classList.add('status-unresolved');
                    } else {
                        viewStatus.classList.add('status-pending');
                    }
                }

                openModal(viewModal);
                return;
            }

            // Delete Button
            const deleteBtn = e.target.closest('.delete-btn');
            if (deleteBtn) {
                complaintIdToDelete = deleteBtn.getAttribute('data-id');
                openModal(deleteModal);
                return;
            }
        });
    }

    if (viewClose) viewClose.addEventListener('click', () => closeModal(viewModal));
    if (viewCancel) viewCancel.addEventListener('click', () => closeModal(viewModal));

    if (deleteCancel) deleteCancel.addEventListener('click', () => {
        complaintIdToDelete = null;
        closeModal(deleteModal);
    });

    if (deleteConfirm) {
        deleteConfirm.addEventListener('click', () => {
            if (complaintIdToDelete) {
                const hiddenDeleteInput = document.getElementById('deleteCompId');
                if (hiddenDeleteInput) {
                    hiddenDeleteInput.value = complaintIdToDelete;
                    if (deleteForm) deleteForm.submit();
                }
            }
        });
    }

    // Close on outside click
    window.addEventListener('click', (e) => {
        if (e.target === deleteModal) closeModal(deleteModal);
        if (e.target === viewModal) closeModal(viewModal);
    });

    // ── Search + Pagination ─────────────────────────────────────────────────
    const entriesSelectId = 'entriesSelect';
    const entriesInfoId = 'entriesInfo';
    const prevBtnId = 'prevBtn';
    const nextBtnId = 'nextBtn';

    const pager = initTablePagination({
        tableBodyId    : 'tableBody',
        entriesSelectId: entriesSelectId,
        entriesInfoId  : entriesInfoId,
        prevBtnId      : prevBtnId,
        nextBtnId      : nextBtnId,
    });

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const term = this.value.toLowerCase();
            if (tableBody) {
                tableBody.querySelectorAll('tr').forEach(row => {
                    if (row.querySelector('td[colspan]')) return;
                    const match = row.textContent.toLowerCase().includes(term);
                    row.dataset.hiddenBySearch = match ? 'false' : 'true';
                });
            }
            pager.refresh();
        });
    }

});