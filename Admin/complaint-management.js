// Complaint Management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    
    // Sample complaint data
    let complaints = [
        {
            sn: 1,
            category: 'Finance',
            description: 'Payment Issue.....',
            date: '01/01/2025',
            assignedTo: 'Finance_admin',
            status: 'Pending',
            remarks: ''
        },
        {
            sn: 2,
            category: 'Exam',
            description: 'Registration Issue...',
            date: '01/05/2025',
            assignedTo: 'Exam_admin',
            status: 'In Progress',
            remarks: 'Takes 3 days'
        },
        {
            sn: 3,
            category: 'IT',
            description: 'Account Locked',
            date: '21/12/2025',
            assignedTo: 'IT_admin',
            status: 'Solved',
            remarks: 'Done'
        }
    ];

    // Render table with current data
    function renderTable() {
        const tableBody = document.getElementById('tableBody');
        tableBody.innerHTML = '';

        complaints.forEach((complaint) => {
            const statusClass = getStatusClass(complaint.status);
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${complaint.sn}</td>
                <td>${complaint.category}</td>
                <td>${complaint.description}</td>
                <td>${complaint.date}</td>
                <td>${complaint.assignedTo}</td>
                <td><span class="status-badge ${statusClass}">${complaint.status}</span></td>
                <td>${complaint.remarks}</td>
                <td class="action-btns">
                    <button class="edit-btn" data-id="${complaint.sn}" title="Edit">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </button>
                    <button class="delete-btn" data-id="${complaint.sn}" title="Delete">
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

    // Get status class for badge styling
    function getStatusClass(status) {
        switch(status) {
            case 'Pending':
                return 'status-pending';
            case 'In Progress':
                return 'status-progress';
            case 'Solved':
                return 'status-solved';
            default:
                return '';
        }
    }

    // Attach event listeners to action buttons
    function attachActionListeners() {
        // Edit buttons
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = parseInt(this.getAttribute('data-id'));
                editComplaint(id);
            });
        });

        // Delete buttons
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = parseInt(this.getAttribute('data-id'));
                deleteComplaint(id);
            });
        });
    }

    // Edit complaint
    function editComplaint(id) {
        const complaint = complaints.find(c => c.sn === id);
        if (complaint) {
            alert(`Edit complaint functionality:\n\nID: ${id}\nCategory: ${complaint.category}\nStatus: ${complaint.status}`);
            // In a real app, this would open a modal with editable fields
        }
    }

    // Delete complaint
    function deleteComplaint(id) {
        if (confirm('Are you sure you want to delete this complaint?')) {
            complaints = complaints.filter(c => c.sn !== id);
            renderTable();
            alert('Complaint deleted successfully!');
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
        const total = complaints.length;
        const showing = visibleRows.length;
        
        document.querySelector('.entries-info').textContent = 
            `Show 1 to ${showing} of ${total} entries`;
    }

    // Initialize table
    renderTable();
});