// Complaint Management JavaScript
document.addEventListener('DOMContentLoaded', function () {

    // Select elements
    const statusModal = document.getElementById('statusModal');
    const statusModalClose = document.getElementById('statusModalClose');
    const statusCancelBtn = document.getElementById('statusCancelBtn');
    const statusForm = document.getElementById('statusForm');

    const viewModal = document.getElementById('viewModal');
    const viewModalClose = document.getElementById('viewModalClose');
    const viewCancelBtn = document.getElementById('viewCancelBtn');

    const deleteModal = document.getElementById('deleteModal');
    const deleteCancel = document.getElementById('deleteCancel');
    const deleteConfirm = document.getElementById('deleteConfirm');
    const deleteForm = document.getElementById('deleteForm');

    const assignModal = document.getElementById('assignModal');
    const assignModalClose = document.getElementById('assignModalClose');
    const assignCancelBtn = document.getElementById('assignCancelBtn');
    const assignForm = document.getElementById('assignForm');

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

    // Status & Assignment Modal Logic
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const statusId = this.getAttribute('data-status');
            const message = this.getAttribute('data-message');
            const title = this.getAttribute('data-title');

            document.getElementById('statusComplaintId').value = id;
            document.getElementById('displayTitle').textContent = title;
            document.getElementById('statusId').value = statusId;
            document.getElementById('statusMessage').value = message || "";

            // Trigger preview update
            updateStatusPreview(statusId);

            openModal(statusModal);
        });
    });

    function updateStatusPreview(statusId) {
        const previewElement = document.getElementById('statusDefaultMessage');
        if (!statusId || !previewElement) return;
        
        const status = statusMessages.find(s => s.status_id == statusId);
        if (status && status.status_message) {
            previewElement.textContent = "";
        }
    }

    const statusIdSelect = document.getElementById('statusId');
    if (statusIdSelect) {
        statusIdSelect.addEventListener('change', function () {
            updateStatusPreview(this.value);
        });
    }

    // View Modal Logic
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const category = this.getAttribute('data-category');
            const title = this.getAttribute('data-title');
            const desc = this.getAttribute('data-desc');
            const date = this.getAttribute('data-date');
            const updated = this.getAttribute('data-updated');
            const complainant = this.getAttribute('data-complainant');
            const email = this.getAttribute('data-email');
            const batch = this.getAttribute('data-batch');
            const incidentDate = this.getAttribute('data-incident-date');
            const assignedName = this.getAttribute('data-assigned-name');
            const statusLabel = this.getAttribute('data-status-label');

            // Basic Info
            document.getElementById('viewId').textContent = '#' + id;
            document.getElementById('viewCategory').textContent = category;
            document.getElementById('viewTitle').textContent = title;
            document.getElementById('viewDesc').textContent = desc;
            document.getElementById('viewDate').textContent = date;
            document.getElementById('viewUpdated').textContent = updated || 'Never';
            document.getElementById('viewComplainant').textContent = complainant;
            document.getElementById('viewEmail').textContent = email;
            document.getElementById('viewBatch').textContent = batch || '---';
            document.getElementById('viewIncidentDate').textContent = incidentDate;
            document.getElementById('viewAssignedName').textContent = assignedName;

            // Status Badge in Sidebar
            const statusContainer = document.getElementById('viewStatus');
            let statusClass = 'status-pending';
            if (statusLabel === 'Solved') statusClass = 'status-solved';
            else if (statusLabel === 'In Progress') statusClass = 'status-progress';
            statusContainer.innerHTML = `<span class="status-badge ${statusClass}">${statusLabel}</span>`;

            // Clear and Load Attachments
            const attachmentsContainer = document.getElementById('viewAttachments');
            attachmentsContainer.innerHTML = '<p class="no-attachments">Loading attachments...</p>';

            fetch(`complaint-management.php?action=get_attachments&complaintId=${id}`)
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
                            if (file.file_type.includes('image')) {
                                icon = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>';
                            }

                            card.innerHTML = `
                                <div class="attachment-info">
                                    ${icon}
                                    <span class="attachment-name" title="${file.file_name}">${file.file_name}</span>
                                </div>
                                <div class="attachment-actions">
                                    <a href="../Includes/view-attachment.php?id=${file.attachment_id}" target="_blank" class="att-action-btn view" title="View">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                        View
                                    </a>
                                    <a href="../Includes/view-attachment.php?id=${file.attachment_id}&download=1" class="att-action-btn download" title="Download">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                        Download
                                    </a>
                                </div>
                            `;
                            attachmentsContainer.appendChild(card);
                        });
                    }
                })
                .catch(err => {
                    console.error('Error fetching attachments:', err);
                    attachmentsContainer.innerHTML = '<p class="no-attachments">Error loading attachments.</p>';
                });

            openModal(viewModal);
        });
    });

    if (statusModalClose) statusModalClose.addEventListener('click', () => closeModal(statusModal));
    if (statusCancelBtn) statusCancelBtn.addEventListener('click', () => closeModal(statusModal));

    if (viewModalClose) viewModalClose.addEventListener('click', () => closeModal(viewModal));
    if (viewCancelBtn) viewCancelBtn.addEventListener('click', () => closeModal(viewModal));

    if (assignModalClose) assignModalClose.addEventListener('click', () => closeModal(assignModal));
    if (assignCancelBtn) assignCancelBtn.addEventListener('click', () => closeModal(assignModal));

    // Delete Modal Logic
    let complaintIdToDelete = null;

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            complaintIdToDelete = this.getAttribute('data-id');
            openModal(deleteModal);
        });
    });

    if (deleteCancel) {
        deleteCancel.addEventListener('click', () => {
            complaintIdToDelete = null;
            closeModal(deleteModal);
        });
    }

    if (deleteConfirm) {
        deleteConfirm.addEventListener('click', () => {
            if (complaintIdToDelete) {
                const hiddenInput = document.getElementById('deleteComplaintId');
                if (hiddenInput) {
                    hiddenInput.value = complaintIdToDelete;
                    if (deleteForm) deleteForm.submit();
                }
            }
        });
    }

    // Close on outside click
    window.addEventListener('click', (e) => {
        if (e.target === statusModal) closeModal(statusModal);
        if (e.target === viewModal) closeModal(viewModal);
        if (e.target === deleteModal) closeModal(deleteModal);
        if (e.target === assignModal) closeModal(assignModal);
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