// Menu toggle functionality
const menuToggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');

menuToggle.addEventListener('click', function() {
    sidebar.classList.toggle('active');
});

// Logout functionality
document.querySelector('.logout-btn').addEventListener('click', function() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = 'login.html';
    }
});

// Delete button functionality
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const complaintId = this.getAttribute('data-id');
        if(confirm('Are you sure you want to delete this complaint?')) {
            this.closest('tr').remove();
            updatePaginationInfo();
            console.log('Deleted complaint ID:', complaintId);
        }
    });
});

// Edit button functionality
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const complaintId = this.getAttribute('data-id');
        console.log('Editing complaint ID:', complaintId);
        window.location.href = 'user-submit-complaint.html?edit=' + complaintId;
    });
});

// Search functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
    const searchValue = this.value.toLowerCase();
    const table = document.getElementById('complaintsTable');
    const rows = table.getElementsByTagName('tr');
    
    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        const cells = row.getElementsByTagName('td');
        let found = false;
        
        for (let j = 0; j < cells.length; j++) {
            const cell = cells[j];
            if (cell.textContent.toLowerCase().indexOf(searchValue) > -1) {
                found = true;
                break;
            }
        }
        
        row.style.display = found ? '' : 'none';
    }
    
    updatePaginationInfo();
});

// Entries per page functionality
document.getElementById('entriesPerPage').addEventListener('change', function() {
    console.log('Entries per page changed to:', this.value);
    // Here you would implement pagination logic
    updatePaginationInfo();
});

// Update pagination info
function updatePaginationInfo() {
    const table = document.getElementById('complaintsTable');
    const rows = table.getElementsByTagName('tr');
    let visibleRows = 0;
    
    for (let i = 1; i < rows.length; i++) {
        if (rows[i].style.display !== 'none') {
            visibleRows++;
        }
    }
    
    document.getElementById('showingStart').textContent = visibleRows > 0 ? '1' : '0';
    document.getElementById('showingEnd').textContent = visibleRows;
    document.getElementById('totalEntries').textContent = visibleRows;
}

// Pagination buttons
document.getElementById('prevBtn').addEventListener('click', function() {
    console.log('Previous page');
});

document.getElementById('nextBtn').addEventListener('click', function() {
    console.log('Next page');
});

// Initialize
updatePaginationInfo();