// Reports JavaScript
document.addEventListener('DOMContentLoaded', function() {
    
    // Select elements
    const deleteModal = document.getElementById('deleteModal');
    const deleteCancel = document.getElementById('deleteCancel');
    const deleteConfirm = document.getElementById('deleteConfirm');
    const deleteForm = document.getElementById('deleteForm');

    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('tableBody');
    const entriesInfo = document.getElementById('entriesInfo');

    // Modal Control Functions
    function openModal(modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    // Delete Modal Logic
    let reportIdToDelete = null;

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            reportIdToDelete = this.getAttribute('data-id');
            openModal(deleteModal);
        });
    });

    if (deleteCancel) {
        deleteCancel.addEventListener('click', () => {
            reportIdToDelete = null;
            closeModal(deleteModal);
        });
    }

    if (deleteConfirm) {
        deleteConfirm.addEventListener('click', () => {
            if (reportIdToDelete) {
                document.getElementById('deleteCompId').value = reportIdToDelete;
                deleteForm.submit();
            }
        });
    }

    // Close on outside click
    window.addEventListener('click', (e) => {
        if (e.target === deleteModal) closeModal(deleteModal);
    });

    // ── Search + Pagination ─────────────────────────────────────────────────
    const pager = initTablePagination({
        tableBodyId    : 'tableBody',
        entriesSelectId: 'entriesSelect',
        entriesInfoId  : 'entriesInfo',
        prevBtnId      : 'prevBtn',
        nextBtnId      : 'nextBtn',
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
