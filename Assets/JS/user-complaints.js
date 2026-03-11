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

    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('input', function () {
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