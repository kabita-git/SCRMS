// Reports JavaScript
document.addEventListener('DOMContentLoaded', function() {
    
    // Sample reports data
    let reports = [
        {
            id: 1,
            name: 'Mark',
            email: 'mark@gmail.com',
            batch: '2024',
            category: 'Finance',
            description: 'Payment Issue.....',
            date: '01/01/2025',
            file: 'abc.pdf'
        },
        {
            id: 2,
            name: 'Jacob',
            email: 'jacob@gmail.com',
            batch: '2024',
            category: 'Exam',
            description: 'Registration Issue...',
            date: '01/05/2025',
            file: ''
        },
        {
            id: 3,
            name: 'Larry',
            email: 'larry@gmail.com',
            batch: '2023',
            category: 'IT',
            description: 'Account Locked',
            date: '21/12/2025',
            file: 'Photo.jpg'
        }
    ];

    // Render table with current data
    function renderTable() {
        const tableBody = document.getElementById('tableBody');
        tableBody.innerHTML = '';

        reports.forEach((report) => {
            const row = document.createElement('tr');
            const fileCell = report.file 
                ? `<a href="#" class="file-link">${report.file}</a>` 
                : '';
            
            row.innerHTML = `
                <td>${report.id}</td>
                <td>${report.name}</td>
                <td>${report.email}</td>
                <td>${report.batch}</td>
                <td>${report.category}</td>
                <td>${report.description}</td>
                <td>${report.date}</td>
                <td>${fileCell}</td>
                <td class="action-btns">
                    <button class="edit-btn" data-id="${report.id}" title="Edit">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </button>
                    <button class="delete-btn" data-id="${report.id}" title="Delete">
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
        attachFileListeners();
        updateEntriesInfo();
    }

    // Attach event listeners to action buttons
    function attachActionListeners() {
        // Edit buttons
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = parseInt(this.getAttribute('data-id'));
                editReport(id);
            });
        });

        // Delete buttons
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = parseInt(this.getAttribute('data-id'));
                deleteReport(id);
            });
        });
    }

    // Attach event listeners to file links
    function attachFileListeners() {
        document.querySelectorAll('.file-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const fileName = this.textContent;
                alert(`Opening file: ${fileName}\n\nIn a real application, this would download or open the file.`);
            });
        });
    }

    // Edit report
    function editReport(id) {
        const report = reports.find(r => r.id === id);
        if (report) {
            alert(`Edit report functionality:\n\nID: ${id}\nName: ${report.name}\nCategory: ${report.category}`);
            // In a real app, this would open a modal with editable fields
        }
    }

    // Delete report
    function deleteReport(id) {
        if (confirm('Are you sure you want to delete this report?')) {
            reports = reports.filter(r => r.id !== id);
            renderTable();
            alert('Report deleted successfully!');
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
        const total = reports.length;
        const showing = visibleRows.length;
        
        document.querySelector('.entries-info').textContent = 
            `Showing 1 to ${showing} of ${total} entries`;
    }

    // Initialize table
    renderTable();
});