// User Complaints JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Select elements
    const editModal = document.getElementById('editModal');
    const editCancel = document.getElementById('editCancel');
    
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

    // Edit Modal Logic
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const title = this.getAttribute('data-title');
            const desc = this.getAttribute('data-desc');
            const batch = this.getAttribute('data-batch');

            document.getElementById('editCompId').value = id;
            document.getElementById('editTitle').value = title;
            document.getElementById('editBatch').value = batch;
            document.getElementById('editDesc').value = desc;

            openModal(editModal);
        });
    });

    const editClose = document.getElementById('editClose');
    if (editClose) editClose.addEventListener('click', () => closeModal(editModal));
    if (editCancel) editCancel.addEventListener('click', () => closeModal(editModal));

    // Delete Modal Logic
    let complaintIdToDelete = null;
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
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
        if (e.target === editModal) closeModal(editModal);
        if (e.target === deleteModal) closeModal(deleteModal);
    });

    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = tableBody.querySelectorAll('tr');

            rows.forEach(row => {
                if (row.cells.length === 1) return; // Skip "No complaints found"
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }
});