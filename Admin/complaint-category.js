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

    // Sample data for demonstration
    let categories = [
        { name: 'Finance', description: 'Payment Issue' },
        { name: 'Exam', description: 'Registration Issue' },
        { name: 'IT', description: 'Account Locked' }
    ];

    // Open modal for adding new category
    addBtn.addEventListener('click', function() {
        editMode = false;
        modalTitle.textContent = 'Add Category';
        categoryForm.reset();
        modal.classList.add('active');
    });

    // Close modal handlers
    modalClose.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);

    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });

    function closeModal() {
        modal.classList.remove('active');
        categoryForm.reset();
    }

    // Form submission handler
    categoryForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const categoryName = document.getElementById('categoryName').value.trim();
        const categoryDescription = document.getElementById('categoryDescription').value.trim();

        if (editMode) {
            // Update existing category
            categories[editRowIndex] = {
                name: categoryName,
                description: categoryDescription
            };
            alert('Category updated successfully!');
        } else {
            // Add new category
            categories.push({
                name: categoryName,
                description: categoryDescription
            });
            alert('Category added successfully!');
        }

        renderTable();
        closeModal();
    });

    // Render table with current data
    function renderTable() {
        const tableBody = document.getElementById('tableBody');
        tableBody.innerHTML = '';

        categories.forEach((category, index) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${category.name}</td>
                <td>${category.description}</td>
                <td class="action-btns">
                    <button class="edit-btn" data-index="${index}" title="Edit">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </button>
                    <button class="delete-btn" data-index="${index}" title="Delete">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>
                </td>
            `;
            tableBody.appendChild(row);
        });

        // Attach event listeners to new buttons
        attachActionListeners();
        updateEntriesInfo();
    }

    // Attach event listeners to edit and delete buttons
    function attachActionListeners() {
        // Edit buttons
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const index = parseInt(this.getAttribute('data-index'));
                editCategory(index);
            });
        });

        // Delete buttons
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const index = parseInt(this.getAttribute('data-index'));
                deleteCategory(index);
            });
        });
    }

    // Edit category
    function editCategory(index) {
        editMode = true;
        editRowIndex = index;
        modalTitle.textContent = 'Edit Category';
        
        const category = categories[index];
        document.getElementById('categoryName').value = category.name;
        document.getElementById('categoryDescription').value = category.description;
        
        modal.classList.add('active');
    }

    // Delete category
    function deleteCategory(index) {
        if (confirm('Are you sure you want to delete this category?')) {
            categories.splice(index, 1);
            renderTable();
            alert('Category deleted successfully!');
        }
    }

    // Search functionality
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#tableBody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
        
        updateEntriesInfo();
    });

    // Entries selector
    const entriesSelect = document.getElementById('entriesSelect');
    entriesSelect.addEventListener('change', function() {
        // In a real app, this would control pagination
        alert(`Show ${this.value} entries functionality will be implemented with pagination`);
    });

    // Pagination buttons
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    
    prevBtn.addEventListener('click', function() {
        alert('Previous page functionality will be implemented');
    });
    
    nextBtn.addEventListener('click', function() {
        alert('Next page functionality will be implemented');
    });

    // Update entries info
    function updateEntriesInfo() {
        const visibleRows = Array.from(document.querySelectorAll('#tableBody tr'))
            .filter(row => row.style.display !== 'none');
        const total = categories.length;
        const showing = visibleRows.length;
        
        document.querySelector('.entries-info').textContent = 
            `Showing 1 to ${showing} of ${total} entries`;
    }

    // Initialize table
    renderTable();
});