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

    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = tableBody.querySelectorAll('tr');
            let visibleCount = 0;

            rows.forEach(row => {
                if (row.cells.length === 1) return; // Skip "No reports found" row
                
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (entriesInfo) {
                const total = rows.length;
                entriesInfo.textContent = `Showing ${visibleCount} entries (filtered from ${total})`;
            }
        });
    }

    // Pagination/Entries mock-up
    const entriesSelect = document.getElementById('entriesSelect');
    if (entriesSelect) {
        entriesSelect.addEventListener('change', function() {
            console.log(`Show ${this.value} entries selected`);
        });
    }
});
