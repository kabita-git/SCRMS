// Reports JavaScript
document.addEventListener('DOMContentLoaded', function() {
    console.log('Reports JS Loaded');
    
    // Select elements
    const viewModal = document.getElementById('viewModal');
    const deleteModal = document.getElementById('deleteModal');
    const deleteForm = document.getElementById('deleteForm');

    // Modal Control Functions
    function openModal(modal) {
        if (!modal) {
            console.error('Modal element not found');
            return;
        }
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modal) {
        if (!modal) return;
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    // Event Delegation for Button Clicks
    document.addEventListener('click', function(e) {
        // View Button
        const viewBtn = e.target.closest('.view-btn');
        if (viewBtn) {
            console.log('View button clicked, ID:', viewBtn.getAttribute('data-id'));
            handleViewDetails(viewBtn);
            return;
        }

        // Delete Button
        const deleteBtn = e.target.closest('.delete-btn');
        if (deleteBtn) {
            const reportId = deleteBtn.getAttribute('data-id');
            console.log('Delete button clicked, ID:', reportId);
            document.getElementById('deleteCompId').value = reportId;
            openModal(deleteModal);
            return;
        }

        // Modal Close Buttons
        if (e.target.closest('.modal-close') || e.target.closest('#viewCancelBtn') || e.target.closest('.modal-btn-cancel')) {
            closeModal(viewModal);
            closeModal(deleteModal);
        }

        // Outside click to close
        if (e.target === viewModal) closeModal(viewModal);
        if (e.target === deleteModal) closeModal(deleteModal);
        if (e.target.id === 'deleteConfirm') {
            if (deleteForm) deleteForm.submit();
        }
    });

    function handleViewDetails(btn) {
        const id = btn.getAttribute('data-id');
        const category = btn.getAttribute('data-category');
        const title = btn.getAttribute('data-title');
        const desc = btn.getAttribute('data-desc');
        const date = btn.getAttribute('data-date');
        const updated = btn.getAttribute('data-updated');
        const complainant = btn.getAttribute('data-complainant');
        const email = btn.getAttribute('data-email');
        const batch = btn.getAttribute('data-batch');
        const incidentDate = btn.getAttribute('data-incident-date');
        const assignedName = btn.getAttribute('data-assigned-name');
        const statusLabel = btn.getAttribute('data-status-label');
        const message = btn.getAttribute('data-message');

        // Basic Info
        if(document.getElementById('viewId')) document.getElementById('viewId').textContent = '#' + id;
        if(document.getElementById('viewCategory')) document.getElementById('viewCategory').textContent = category;
        if(document.getElementById('viewTitle')) document.getElementById('viewTitle').textContent = title;
        if(document.getElementById('viewDesc')) document.getElementById('viewDesc').textContent = desc;
        if(document.getElementById('viewDate')) document.getElementById('viewDate').textContent = date;
        if(document.getElementById('viewUpdated')) document.getElementById('viewUpdated').textContent = updated || 'Never';
        if(document.getElementById('viewComplainant')) document.getElementById('viewComplainant').textContent = complainant;
        if(document.getElementById('viewEmail')) document.getElementById('viewEmail').textContent = email;
        if(document.getElementById('viewBatch')) document.getElementById('viewBatch').textContent = batch || '---';
        if(document.getElementById('viewIncidentDate')) document.getElementById('viewIncidentDate').textContent = incidentDate;
        if(document.getElementById('viewAssignedName')) document.getElementById('viewAssignedName').textContent = assignedName;
        if(document.getElementById('viewClosureMessage')) document.getElementById('viewClosureMessage').textContent = message || 'No closure message yet.';

        // Status Badge
        const statusContainer = document.getElementById('viewStatus');
        if (statusContainer) {
            let statusClass = 'status-pending';
            if (statusLabel === 'Solved') statusClass = 'status-solved';
            else if (statusLabel === 'In Progress') statusClass = 'status-progress';
            else if (statusLabel === 'Unresolved') statusClass = 'status-unresolved';
            statusContainer.innerHTML = `<span class="status-badge ${statusClass}">${statusLabel}</span>`;
        }

        // Load Attachments
        const attachmentsContainer = document.getElementById('viewAttachments');
        if (attachmentsContainer) {
            attachmentsContainer.innerHTML = '<p class="no-attachments">Loading attachments...</p>';
            fetch(`reports.php?action=get_attachments&complaintId=${id}`)
                .then(response => response.json())
                .then(data => {
                    attachmentsContainer.innerHTML = '';
                    if (data.length === 0) {
                        attachmentsContainer.innerHTML = '<p class="no-attachments">No evidence files attached.</p>';
                    } else {
                        data.forEach(file => {
                            const card = document.createElement('div');
                            card.className = 'attachment-item';
                            let icon = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>';
                            if (file.file_type && file.file_type.includes('image')) {
                                icon = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>';
                            }
                            card.innerHTML = `
                                <div class="attachment-info">
                                    ${icon}
                                    <span class="attachment-name" title="${file.file_name}">${file.file_name}</span>
                                </div>
                                <div class="attachment-actions">
                                    <a href="../Includes/view-attachment.php?id=${file.attachment_id}" target="_blank" class="att-action-btn view">View</a>
                                    <a href="../Includes/view-attachment.php?id=${file.attachment_id}&download=1" class="att-action-btn download">Download</a>
                                </div>`;
                            attachmentsContainer.appendChild(card);
                        });
                    }
                })
                .catch(err => {
                    console.error('Error fetching attachments:', err);
                    attachmentsContainer.innerHTML = '<p class="no-attachments">Error loading attachments.</p>';
                });
        }

        openModal(viewModal);
    }

    // ── Search + Pagination ─────────────────────────────────────────────────
    const pager = initTablePagination({
        tableBodyId    : 'tableBody',
        entriesSelectId: 'entriesSelect',
        entriesInfoId  : 'entriesInfo',
        prevBtnId      : 'prevBtn',
        nextBtnId      : 'nextBtn',
    });

    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const term = this.value.toLowerCase();
            const tableBody = document.getElementById('tableBody');
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
