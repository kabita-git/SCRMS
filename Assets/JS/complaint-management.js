// Complaint Management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    
    // Select elements
    const statusModal = document.getElementById('statusModal');
    const statusModalClose = document.getElementById('statusModalClose');
    const statusCancelBtn = document.getElementById('statusCancelBtn');
    const statusForm = document.getElementById('statusForm');
    
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

    // Status Modal Logic
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const statusId = this.getAttribute('data-status');
            const title = this.getAttribute('data-title');
            const remarks = this.getAttribute('data-remarks');

            document.getElementById('statusComplaintId').value = id;
            document.getElementById('displayTitle').textContent = title;
            document.getElementById('statusId').value = statusId;

            openModal(statusModal);
        });
    });

    statusModalClose.addEventListener('click', () => closeModal(statusModal));
    statusCancelBtn.addEventListener('click', () => closeModal(statusModal));

    // Delete Modal Logic
    let complaintIdToDelete = null;

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            complaintIdToDelete = this.getAttribute('data-id');
            openModal(deleteModal);
        });
    });

    deleteCancel.addEventListener('click', () => {
        complaintIdToDelete = null;
        closeModal(deleteModal);
    });

    deleteConfirm.addEventListener('click', () => {
        if (complaintIdToDelete) {
            document.getElementById('deleteComplaintId').value = complaintIdToDelete;
            deleteForm.submit();
        }
    });

    // Close on outside click
    window.addEventListener('click', (e) => {
        if (e.target === statusModal) closeModal(statusModal);
        if (e.target === deleteModal) closeModal(deleteModal);
    });

    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = tableBody.querySelectorAll('tr');
            let visibleCount = 0;

            rows.forEach(row => {
                if (row.cells.length === 1) return; // Skip "No complaints found" row
                
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
            // In a real paginated app, this would trigger an AJAX or page reload
            // For now, we just log it as the user hasn't asked for full pagination logic yet
            console.log(`Show ${this.value} entries selected`);
        });
    }

    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    if (prevBtn) prevBtn.addEventListener('click', () => console.log('Previous page clicked'));
    if (nextBtn) nextBtn.addEventListener('click', () => console.log('Next page clicked'));

});