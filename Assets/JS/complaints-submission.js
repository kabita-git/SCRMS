// Logout functionality integrated with global logoutModal in header
const logoutTrigger = document.querySelector('.logout-btn');
const logoutModal = document.getElementById('logoutModal');

if (logoutTrigger && logoutModal) {
    logoutTrigger.addEventListener('click', function(e) {
        e.preventDefault();
        logoutModal.classList.add('active');
    });
}

// File upload management
const fileInput = document.getElementById('evidence');
const fileListContainer = document.getElementById('fileList');
const fileNameDisplay = document.getElementById('fileNameDisplay');

let selectedFiles = [];

if (fileInput) {
    fileInput.addEventListener('change', function(e) {
        const newFiles = Array.from(e.target.files);
        
        const allowedExtensions = ['jpg', 'png', 'gif', 'mp4', 'avi', 'mov', 'mp3', 'wav', 'aac', 'docx', 'pdf', 'txt'];
        let hasInvalidFiles = false;

        newFiles.forEach(file => {
            const fileName = file.name.toLowerCase();
            const fileExt = fileName.split('.').pop();

            if (!allowedExtensions.includes(fileExt)) {
                hasInvalidFiles = true;
            } else {
                // Check if file is already in our list (by name and size)
                if (!selectedFiles.some(f => f.name === f.name && f.size === file.size)) {
                    selectedFiles.push(file);
                }
            }
        });
        
        if (hasInvalidFiles) {
            const warningBox = document.querySelector('.file-warning span');
            if (warningBox) {
                warningBox.style.color = '#dc3545';
                warningBox.textContent = 'Some files were rejected! Only Audio, Video and Text files (JPG, PNG, MP4, PDF, etc.) are supported.';
                setTimeout(() => {
                    warningBox.style.color = '';
                    warningBox.textContent = 'Note: You can upload Audio, Video and Text files. (Supported: JPG, PNG, MP4, PDF, DOCX, TXT, etc.)';
                }, 5000);
            }
        }

        updateFileList();
        syncFileInput();
    });
}

function updateFileList() {
    fileListContainer.innerHTML = '';
    
    selectedFiles.forEach((file, index) => {
        const fileItem = document.createElement('div');
        fileItem.className = 'file-item';
        
        const fileName = document.createElement('span');
        fileName.className = 'file-name';
        fileName.textContent = file.name;
        
        const removeBtn = document.createElement('button');
        removeBtn.innerHTML = '&times;';
        removeBtn.type = 'button';
        removeBtn.className = 'remove-file-btn';
        
        removeBtn.onclick = function() {
            const deleteModal = document.getElementById('fileDeleteModal');
            const confirmBtn = document.getElementById('fileDeleteConfirm');
            const cancelBtn = document.getElementById('fileDeleteCancel');
            
            if (deleteModal && confirmBtn && cancelBtn) {
                deleteModal.classList.add('active');
                
                const handleConfirm = () => {
                    selectedFiles.splice(index, 1);
                    updateFileList();
                    syncFileInput();
                    deleteModal.classList.remove('active');
                    confirmBtn.removeEventListener('click', handleConfirm);
                };
                
                const handleCancel = () => {
                    deleteModal.classList.remove('active');
                    cancelBtn.removeEventListener('click', handleCancel);
                };
                
                confirmBtn.addEventListener('click', handleConfirm);
                cancelBtn.addEventListener('click', handleCancel);
            } else {
                // Fallback if modal elements missing
                selectedFiles.splice(index, 1);
                updateFileList();
                syncFileInput();
            }
        };
        
        fileItem.appendChild(fileName);
        fileItem.appendChild(removeBtn);
        fileListContainer.appendChild(fileItem);
    });
    
    if (selectedFiles.length > 0) {
        fileNameDisplay.textContent = `${selectedFiles.length} file(s) selected`;
    } else {
        fileNameDisplay.textContent = 'Upload Files';
    }
}

function syncFileInput() {
    const dataTransfer = new DataTransfer();
    selectedFiles.forEach(file => dataTransfer.items.add(file));
    fileInput.files = dataTransfer.files;
}

// Initialize Flatpickr datepicker on the incident date field (Calender)
const dateInput = document.getElementById('date');
const calendarIcon = document.querySelector('.calendar-icon');

if (dateInput) {
    const fp = flatpickr(dateInput, {
        dateFormat: 'd/m/Y',
        maxDate: 'today',
        defaultDate: 'today',
        allowInput: true,
        disableMobile: true
    });

    // Make the calendar icon open/toggle the datepicker
    if (calendarIcon) {
        calendarIcon.style.pointerEvents = 'auto';
        calendarIcon.style.cursor = 'pointer';
        calendarIcon.addEventListener('click', function () {
            fp.toggle();
        });
    }
}