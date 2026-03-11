// Complaint Category JavaScript
document.addEventListener('DOMContentLoaded', function() {
    
    // Modal Elements
    const modal = document.getElementById('categoryModal');
    const addBtn = document.getElementById('addCategoryBtn');
    const modalClose = document.getElementById('modalClose');
    const cancelBtn = document.getElementById('cancelBtn');
    const categoryForm = document.getElementById('categoryForm');
    const modalTitle = document.getElementById('modalTitle');
    
    let editMode = false;
    let editRowIndex = -1;

    // Open modal for adding new category
    if (addBtn) {
        addBtn.addEventListener('click', function() {
            if (modalTitle) modalTitle.textContent = 'Add Category';
            // Set form specifically to add
            if (categoryForm) {
                categoryForm.reset();
                // Ensure the hidden input for action exists and is 'add'
                let actionInput = categoryForm.querySelector('input[name="action"]');
                if (actionInput) actionInput.value = 'add';
            }
            if (modal) modal.classList.add('active');
        });
    }

    // Close modal handlers
    function closeModal() {
        if (modal) modal.classList.remove('active');
        if (categoryForm) categoryForm.reset();
    }

    if (modalClose) modalClose.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

    // Close modal when clicking outside
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });
    }

    // Modal Elements for Deletion
    const deleteModal = document.getElementById('deleteModal');
    const deleteCancel = document.getElementById('deleteCancel');
    const deleteConfirm = document.getElementById('deleteConfirm');
    const deleteMessage = document.getElementById('deleteMessage');
    const deleteCategoryId = document.getElementById('deleteCategoryId');
    const deleteForm = document.getElementById('deleteForm');

    // Attach event listeners to edit and delete buttons
    const tableBody = document.getElementById('tableBody');
    if (tableBody) {
        tableBody.addEventListener('click', function(e) {
            // Find closest button
            const btn = e.target.closest('button');
            if (!btn) return;

            if (btn.classList.contains('edit-btn')) {
                const id = btn.getAttribute('data-id');
                const name = btn.getAttribute('data-name');
                const desc = btn.getAttribute('data-desc');
                
                if (modalTitle) modalTitle.textContent = 'Edit Category';
                if (categoryForm) {
                    categoryForm.reset();
                    // Setup form for edit
                    const actionInput = categoryForm.querySelector('input[name="action"]');
                    const idInput = categoryForm.querySelector('input[name="categoryId"]');
                    const nameInput = document.getElementById('categoryName');
                    const descInput = document.getElementById('categoryDescription');
                    const headInput = document.getElementById('deptHeadId');
                    const headId = btn.getAttribute('data-head-id');
                    
                    if (actionInput) actionInput.value = 'edit';
                    if (idInput) idInput.value = id;
                    if (nameInput) nameInput.value = name;
                    if (descInput) descInput.value = desc;
                    if (headInput) headInput.value = headId || "";
                }
                if (modal) modal.classList.add('active');
            } 
            else if (btn.classList.contains('delete-btn')) {
                const id = btn.getAttribute('data-id');
                const categoryName = btn.closest('tr').cells[0].textContent;
                
                if (deleteMessage) deleteMessage.textContent = `Are you sure you want to delete the category "${categoryName}"?`;
                if (deleteCategoryId) deleteCategoryId.value = id;
                if (deleteModal) deleteModal.classList.add('active');
            }
        });
    }

    // Handle Delete Modal Actions
    if (deleteCancel && deleteModal) {
        deleteCancel.addEventListener('click', function() {
            deleteModal.classList.remove('active');
        });
    }

    if (deleteConfirm && deleteForm) {
        deleteConfirm.addEventListener('click', function() {
            if (deleteMessage) deleteMessage.textContent = 'Deleting... Please wait.';
            deleteForm.submit();
        });
    }

    // ── Search + Pagination ─────────────────────────────────────────────────
    const pager = initTablePagination({
        tableBodyId    : 'tableBody',
        entriesSelectId: 'entriesSelect',
        entriesInfoId  : null,          // category page uses .entries-info class
        prevBtnId      : 'prevBtn',
        nextBtnId      : 'nextBtn',
    });

    // Wire up the .entries-info element manually (no id on this page)
    function updateCategoryEntriesInfo() {
        const all = Array.from(document.querySelectorAll('#tableBody tr'))
            .filter(row => !row.querySelector('td[colspan]'));
        const visible = all.filter(row => row.dataset.hiddenBySearch !== 'true');
        const el = document.querySelector('.entries-info');
        if (el) el.textContent = `Showing 1 to ${visible.length} of ${all.length} entries`;
    }
    updateCategoryEntriesInfo();

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const term = this.value.toLowerCase();
            document.querySelectorAll('#tableBody tr').forEach(row => {
                if (row.querySelector('td[colspan]')) return;
                const match = row.textContent.toLowerCase().includes(term);
                row.dataset.hiddenBySearch = match ? 'false' : 'true';
            });
            pager.refresh();
            updateCategoryEntriesInfo();
        });
    }

});