@props(['uploadUrl'])

<!-- Backdrop for Modal -->
<div id="upload-component-modal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-[#1a1a24]/40 backdrop-blur-sm transition-opacity duration-300 opacity-0">
    
    <!-- Modal Card -->
    <div id="upload-modal-content" class="bg-white rounded-[24px] shadow-2xl p-6 w-full max-w-md transform scale-95 transition-transform duration-300 text-center relative mx-4">
        
        <!-- Close button (optional, good for UX) -->
        <button type="button" onclick="closeUploadModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>

        <!-- Drop Zone -->
        <div id="upload-dropzone" class="bg-[#f8f9fa] rounded-[20px] p-10 mb-6 flex flex-col items-center justify-center relative cursor-pointer border-2 border-transparent transition-colors hover:border-[var(--color-dp-primary)]/30 group">
            
            <input type="file" id="component-file-input" class="hidden" accept="image/*,application/pdf">

            <!-- Custom Cloud/Upload Icon from Image-2 -->
            <div class="relative w-32 h-32 flex items-center justify-center mb-2">
                <!-- Concentric lines representing cloud -->
                <svg width="120" height="120" viewBox="0 0 120 120" fill="none" class="absolute">
                    <path d="M40 70 A20 20 0 0 1 40 30 A30 30 0 0 1 90 40 A20 20 0 0 1 80 80 Z" stroke="#e5e7eb" stroke-width="2" fill="none" class="opacity-50"/>
                    <path d="M30 80 A30 30 0 0 1 30 20 A45 45 0 0 1 100 35 A30 30 0 0 1 90 90 Z" stroke="#e5e7eb" stroke-width="1.5" fill="none" class="opacity-30"/>
                    <path d="M20 90 A40 40 0 0 1 20 10 A60 60 0 0 1 110 30 A40 40 0 0 1 100 100 Z" stroke="#e5e7eb" stroke-width="1" fill="none" class="opacity-20"/>
                </svg>
                
                <!-- White cloud shape in middle -->
                <svg width="70" height="50" viewBox="0 0 24 24" fill="white" class="absolute top-[20px] drop-shadow-sm">
                    <path d="M17.5 19C19.9853 19 22 16.9853 22 14.5C22 12.1325 20.1793 10.203 17.8596 10.0245C17.4339 6.6436 14.5516 4 11 4C7.13401 4 4 7.13401 4 11C4 11.2335 4.01141 11.4643 4.03362 11.6917C2.28292 12.3021 1 13.9785 1 16C1 18.2091 2.79086 20 5 20H17.5V19Z" fill="white"/>
                </svg>

                <!-- Blue Upload Button -->
                <div class="w-14 h-14 bg-[var(--color-dp-primary)] rounded-full flex items-center justify-center absolute bottom-0 shadow-[0_4px_15px_rgba(92,65,201,0.4)] z-10 group-hover:-translate-y-1 transition-transform">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="19" x2="12" y2="5"></line><polyline points="5 12 12 5 19 12"></polyline></svg>
                </div>
            </div>
        </div>

        <!-- Text content -->
        <p class="text-[13px] font-medium text-gray-500 mb-1">Media Upload</p>
        <h3 class="text-[20px] font-bold text-[#1a1a24] mb-2">Upload Media Files</h3>
        <p class="text-[14px] text-gray-500 leading-relaxed px-2">
            Upload your photos, videos and documents to your account. You can edit them later.
        </p>

    </div>
</div>

<!-- Progress Toast Container -->
<div id="upload-toast-container" class="fixed bottom-6 right-6 z-[110] flex flex-col gap-3">
    <!-- Toast template (cloned dynamically) -->
    <div id="upload-toast-template" class="hidden w-[400px] bg-white rounded-2xl shadow-[0_8px_30px_rgba(0,0,0,0.12)] p-4 flex items-center gap-4 border border-gray-100 transform translate-y-10 opacity-0 transition-all duration-500">
        <!-- File Icon (Simple Image Icon) -->
        <div class="w-12 h-14 bg-gray-50 rounded-lg flex items-center justify-center shrink-0 border border-gray-200">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <circle cx="8.5" cy="8.5" r="1.5"/>
                <polyline points="21 15 16 10 5 21"/>
            </svg>
        </div>
        
        <!-- Progress Details -->
        <div class="flex-1 min-w-0">
            <div class="flex justify-between items-end mb-2">
                <p class="text-[15px] font-bold text-[#1a1a24] truncate pr-2" id="toast-file-name">filename.ext</p>
                <span class="text-[13px] font-semibold text-gray-500" id="toast-percentage">0%</span>
            </div>
            
            <!-- Progress Bar Background -->
            <div class="h-1.5 w-full bg-gray-100 rounded-full overflow-hidden mb-2">
                <!-- Progress Bar Fill -->
                <div id="toast-progress-bar" class="h-full bg-[var(--color-dp-primary)] rounded-full w-0 transition-all duration-200"></div>
            </div>
            
            <!-- Size Details -->
            <p class="text-[12px] font-medium text-gray-500" id="toast-size-info">0.0 MB of 0.0 MB</p>
        </div>
    </div>
</div>

<script>
    // Configuration
    const UPLOAD_URL = '{{ $uploadUrl }}';
    const CSRF_TOKEN = '{{ csrf_token() }}';

    // Elements
    const modal = document.getElementById('upload-component-modal');
    const modalContent = document.getElementById('upload-modal-content');
    const dropzone = document.getElementById('upload-dropzone');
    const fileInput = document.getElementById('component-file-input');
    
    // Open Modal globally
    window.openUploadModal = function() {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        // Small delay to allow display to apply before fading in
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modalContent.classList.remove('scale-95');
        }, 10);
    }

    window.closeUploadModal = function() {
        modal.classList.add('opacity-0');
        modalContent.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }

    // Trigger file selection when clicking dropzone
    dropzone.addEventListener('click', () => fileInput.click());

    // Drag and Drop Events
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, () => {
            dropzone.classList.add('border-[var(--color-dp-primary)]', 'bg-[var(--color-dp-primary)]/5');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, () => {
            dropzone.classList.remove('border-[var(--color-dp-primary)]', 'bg-[var(--color-dp-primary)]/5');
        }, false);
    });

    dropzone.addEventListener('drop', handleDrop, false);
    fileInput.addEventListener('change', handleFileSelect, false);

    function handleDrop(e) {
        let dt = e.dataTransfer;
        let files = dt.files;
        handleFiles(files);
    }

    function handleFileSelect(e) {
        let files = e.target.files;
        handleFiles(files);
    }

    function handleFiles(files) {
        if(files.length > 0) {
            closeUploadModal();
            uploadFile(files[0]);
        }
    }

    // Convert bytes to MB string
    function formatBytesToMB(bytes) {
        return (bytes / (1024 * 1024)).toFixed(1);
    }

    function getFileExtension(filename) {
        return filename.split('.').pop().toUpperCase();
    }

    function createProgressToast(file) {
        const container = document.getElementById('upload-toast-container');
        const template = document.getElementById('upload-toast-template');
        const toast = template.cloneNode(true);
        
        toast.id = 'toast-' + Date.now();
        toast.classList.remove('hidden');
        
        // Setup initial UI
        toast.querySelector('#toast-file-name').textContent = file.name;
        
        const totalMB = formatBytesToMB(file.size);
        toast.querySelector('#toast-size-info').textContent = `0.0 MB of ${totalMB} MB`;
        
        container.appendChild(toast);
        
        // Animate in
        requestAnimationFrame(() => {
            toast.classList.remove('translate-y-10', 'opacity-0');
        });

        return {
            element: toast,
            update: function(percent, loadedBytes) {
                toast.querySelector('#toast-percentage').textContent = percent + '%';
                toast.querySelector('#toast-progress-bar').style.width = percent + '%';
                toast.querySelector('#toast-size-info').textContent = `${formatBytesToMB(loadedBytes)} MB of ${totalMB} MB`;
            },
            complete: function() {
                setTimeout(() => {
                    toast.classList.add('opacity-0', 'translate-x-full');
                    setTimeout(() => toast.remove(), 500);
                }, 2000); // Wait 2 secs before disappearing
            }
        };
    }

    function uploadFile(file) {
        let formData = new FormData();
        formData.append('avatar', file); // key name expected by backend

        let xhr = new XMLHttpRequest();
        let toast = createProgressToast(file);

        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                let percent = Math.round((e.loaded / e.total) * 100);
                toast.update(percent, e.loaded);
            }
        });

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    toast.update(100, file.size);
                    toast.complete();
                    
                    try {
                        let response = JSON.parse(xhr.responseText);
                        if(response.success && response.path) {
                            // Emit global event
                            const event = new CustomEvent('upload-success', { 
                                detail: { url: response.path } 
                            });
                            document.dispatchEvent(event);
                            if (window.DevOSAlert) {
                                const alert = response.alert || {};
                                window.DevOSAlert.show({
                                    type: alert.type || 'update',
                                    title: alert.title || 'done successfully :)',
                                    description: alert.description || 'Profile photo updated.',
                                });
                            }
                            window.DevOSNotifications?.refresh();
                        }
                    } catch(e) {
                        console.error("Failed to parse upload response", e);
                    }
                } else {
                    // Upload failed visually update toast
                    toast.element.querySelector('#toast-percentage').textContent = 'Failed';
                    toast.element.querySelector('#toast-percentage').classList.replace('text-gray-500', 'text-red-500');
                    toast.element.querySelector('#toast-progress-bar').classList.replace('bg-[var(--color-dp-primary)]', 'bg-red-500');
                    toast.complete();
                }
            }
        };

        xhr.open('POST', UPLOAD_URL, true);
        xhr.setRequestHeader('X-CSRF-TOKEN', CSRF_TOKEN);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.send(formData);
    }
</script>
