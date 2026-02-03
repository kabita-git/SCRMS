// User Management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    
    // Modal Elements
    const modal = document.getElementById('editUserModal');
    const modalClose = document.getElementById('modalClose');
    const cancelBtn = document.getElementById('cancelBtn');
    const editUserForm = document.getElementById('editUserForm');
    
    let currentEditId = null;

    // Sample user data
    let users = [
        {
            id: 1,
            name: 'Mark',
            email: 'mark@gmail.com',
            contact: '+977 123456789',
            username: 'mark1',
            password: '********',
            status: 'Active'
        },
        {
            id: 2,
            name: 'Jacob',
            email: 'jacob@gmail.com',
            contact: '+977 123456789',
            username: 'jacob02',
            password: '********',
            status: 'Inactive'
        },
        {
            id: 3,
            name: 'Larry',
            email: 'larry@gmail.com',
            contact: '+977 123456789',
            username: 'larry07',
            password: '********',
            status: 'Inactive'
        }
    ];

    // Render table with current data
    function renderTable() {
        const tableBody = document.getElementById('tableBody');
        tableBody.innerHTML = '';

        users.forEach((user) => {
            const statusClass = user.status === 'Active' ? 'status-active' : 'status-inactive';
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${user.id}</td>
                <td>${user.name}</td>
                <td>${user.email}</td>
                <td>${user.contact}</td>
                <td>${user.username}</td>
                <td>${user.password}</td>
                <td><span class="status-badge ${statusClass}">${user.status}</span></td>
                <td class="action-btns">
                    <button class="edit-btn" data-id="${user.id}" title="Edit">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </button>
                    <button class="delete-btn" data-id="${user.id}" title="Delete">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>
                </td>
            `;
            tableBody.appendChild(row);
        });

        attachActionListeners();
        updateEntriesInfo();
    }

    // Attach event listeners to action buttons
    function attachActionListeners() {
        // Edit buttons
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = parseInt(this.getAttribute('data-id'));
                openEditModal(id);
            });
        });

        // Delete buttons
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = parseInt(this.getAttribute('data-id'));
                deleteUser(id);
            });
        });
    }

    // Open edit modal with user data
    function openEditModal(id) {
        const user = users.find(u => u.id === id);
        if (user) {
            currentEditId = id;
            document.getElementById('userName').value = user.name;
            document.getElementById('userEmail').value = user.email;
            document.getElementById('userContact').value = user.contact;
            document.getElementById('userUsername').value = user.username;
            document.getElementById('userPassword').value = '';
            document.getElementById('userStatus').value = user.status;
            
            modal.classList.add('active');
        }
    }

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
        editUserForm.reset();
        currentEditId = null;
    }

    // Form submission handler
    editUserForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (currentEditId !== null) {
            const userIndex = users.findIndex(u => u.id === currentEditId);
            if (userIndex !== -1) {
                users[userIndex] = {
                    id: currentEditId,
                    name: document.getElementById('userName').value.trim(),
                    email: document.getElementById('userEmail').value.trim(),
                    contact: document.getElementById('userContact').value.trim(),
                    username: document.getElementById('userUsername').value.trim(),
                    password: '********', // Keep password masked
                    status: document.getElementById('userStatus').value
                };

                renderTable();
                closeModal();
                alert('User updated successfully!');
            }
        }
    });

    // Delete user
    function deleteUser(id) {
        if (confirm('Are you sure you want to delete this user?')) {
            users = users.filter(u => u.id !== id);
            renderTable();
            alert('User deleted successfully!');
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
        const total = users.length;
        const showing = visibleRows.length;
        
        document.querySelector('.entries-info').textContent = 
            `Showing 1 to ${showing} of ${total} entries`;
    }

    // Initialize table
    renderTable();
});