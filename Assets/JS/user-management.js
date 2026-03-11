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
            const category = this.getAttribute('data-category');

            document.getElementById('editUserId').value = id;
            document.getElementById('firstName').value = first;
            document.getElementById('middleName').value = middle;
            document.getElementById('lastName').value = last;
            document.getElementById('userEmail').value = email;
            document.getElementById('userContact').value = contact;
            document.getElementById('userRole').value = role;
            document.getElementById('userStatus').value = status;

            // Handle category visibility
            const categoryGroup = document.getElementById('categoryAssignmentGroup');
            const categorySelect = document.getElementById('assignedCategory');
            
            if (role === 'DeptAdmin') {
                categoryGroup.style.display = 'block';
                categorySelect.value = category || "";
                categorySelect.required = true;
            } else {
                categoryGroup.style.display = 'none';
                categorySelect.value = "";
                categorySelect.required = false;
            }

            openModal(editModal);
        });
    });

    // Handle role change in modal
    const userRoleSelect = document.getElementById('userRole');
    if (userRoleSelect) {
        userRoleSelect.addEventListener('change', function() {
            const categoryGroup = document.getElementById('categoryAssignmentGroup');
            const categorySelect = document.getElementById('assignedCategory');
            if (this.value === 'DeptAdmin') {
                categoryGroup.style.display = 'block';
                categorySelect.required = true;
            } else {
                categoryGroup.style.display = 'none';
                categorySelect.required = false;
            }
        });
    }

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