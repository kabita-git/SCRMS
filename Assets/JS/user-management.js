// User Management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    
    // Select elements
    const editModal = document.getElementById('editUserModal');
    const modalClose = document.getElementById('modalClose');
    const cancelBtn = document.getElementById('cancelBtn');
    const editUserForm = document.getElementById('editUserForm');
    
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

    // Edit User Modal Logic
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const first = this.getAttribute('data-first');
            const middle = this.getAttribute('data-middle');
            const last = this.getAttribute('data-last');
            const email = this.getAttribute('data-email');
            const contact = this.getAttribute('data-contact');
            const role = this.getAttribute('data-role');
            const status = this.getAttribute('data-status');

            document.getElementById('editUserId').value = id;
            document.getElementById('firstName').value = first;
            document.getElementById('middleName').value = middle;
            document.getElementById('lastName').value = last;
            document.getElementById('userEmail').value = email;
            document.getElementById('userContact').value = contact;
            document.getElementById('userRole').value = role;
            document.getElementById('userStatus').value = status;

            openModal(editModal);
        });
    });

    modalClose.addEventListener('click', () => closeModal(editModal));
    cancelBtn.addEventListener('click', () => closeModal(editModal));

    // Delete Modal Logic
    let userIdToDelete = null;

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            userIdToDelete = this.getAttribute('data-id');
            openModal(deleteModal);
        });
    });

    deleteCancel.addEventListener('click', () => {
        userIdToDelete = null;
        closeModal(deleteModal);
    });

    deleteConfirm.addEventListener('click', () => {
        if (userIdToDelete) {
            document.getElementById('deleteUserId').value = userIdToDelete;
            deleteForm.submit();
        }
    });

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
            let visibleCount = 0;

            rows.forEach(row => {
                if (row.cells.length === 1) return; // Skip "No users found" row
                
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

    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    if (prevBtn) prevBtn.addEventListener('click', () => console.log('Previous page clicked'));
    if (nextBtn) nextBtn.addEventListener('click', () => console.log('Next page clicked'));

});