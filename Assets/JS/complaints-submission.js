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
        
        newFiles.forEach(file => {
            // Check if file is already in our list (by name and size)
            if (!selectedFiles.some(f => f.name === f.name && f.size === file.size)) {
                selectedFiles.push(file);
            }
        });
        
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

// Set today's date as default
if (document.getElementById('date')) {
    document.getElementById('date').valueAsDate = new Date();
}