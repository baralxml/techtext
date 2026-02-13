/**
 * TechText - Markup Language Converter
 * Client-side JavaScript Application with PWA Support
 * 
 * Built by: Santosh Baral
 * Company: Techzen Corporation
 * Web: https://techzeninc.com
 */

(function() {
    'use strict';

    // Global state
    let csrfToken = '';
    let currentOutput = '';
    let currentOutputFormat = 'richtext';
    let isOnline = navigator.onLine;

    // DOM Elements
    const elements = {
        markupType: document.getElementById('markupType'),
        outputFormat: document.getElementById('outputFormat'),
        inputContent: document.getElementById('inputContent'),
        outputContainer: document.getElementById('outputContainer'),
        outputPlaceholder: document.getElementById('outputPlaceholder'),
        outputRaw: document.getElementById('outputRaw'),
        outputPreview: document.getElementById('outputPreview'),
        charCount: document.getElementById('charCount'),
        historyList: document.getElementById('historyList'),
        emptyHistory: document.getElementById('emptyHistory'),
        dropZone: document.getElementById('dropZone'),
        fileInput: document.getElementById('fileInput'),
        toast: document.getElementById('toast'),
        convertBtn: document.getElementById('convertBtn'),
        clearBtn: document.getElementById('clearBtn'),
        copyBtn: document.getElementById('copyBtn'),
        downloadBtn: document.getElementById('downloadBtn'),
        clearHistoryBtn: document.getElementById('clearHistoryBtn'),
        viewRawBtn: document.getElementById('viewRawBtn'),
        viewPreviewBtn: document.getElementById('viewPreviewBtn'),
        fabConvert: document.getElementById('fabConvert'),
        menuBtn: document.getElementById('menuBtn')
    };

    /**
     * Initialize application
     */
    function init() {
        fetchCsrfToken();
        bindEvents();
        loadHistory();
        initClipboard();
        updateCharCount();
        initPWAFeatures();
        initAnimations();
        
        // Check URL params for actions
        handleUrlParams();
    }

    /**
     * Initialize PWA-specific features
     */
    function initPWAFeatures() {
        // Listen for online/offline events
        window.addEventListener('online', () => {
            isOnline = true;
            showToast('Back online!', 'success');
        });

        window.addEventListener('offline', () => {
            isOnline = false;
            showToast('You are offline. Some features may be limited.', 'info');
        });

        // Register for background sync if available
        if ('serviceWorker' in navigator && 'SyncManager' in window) {
            navigator.serviceWorker.ready.then((registration) => {
                // App is ready for background sync
                console.log('Background sync ready');
            });
        }

        // Handle visibility change (save state)
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                saveAppState();
            } else {
                restoreAppState();
            }
        });
    }

    /**
     * Save app state to session storage
     */
    function saveAppState() {
        const state = {
            content: elements.inputContent.value,
            markupType: elements.markupType.value,
            outputFormat: elements.outputFormat.value,
            timestamp: Date.now()
        };
        sessionStorage.setItem('techtext_state', JSON.stringify(state));
    }

    /**
     * Restore app state from session storage
     */
    function restoreAppState() {
        const savedState = sessionStorage.getItem('techtext_state');
        if (savedState) {
            try {
                const state = JSON.parse(savedState);
                // Only restore if less than 1 hour old
                if (Date.now() - state.timestamp < 3600000) {
                    if (!elements.inputContent.value && state.content) {
                        elements.inputContent.value = state.content;
                        elements.markupType.value = state.markupType;
                        elements.outputFormat.value = state.outputFormat;
                        updateCharCount();
                    }
                }
            } catch (e) {
                console.error('Failed to restore state:', e);
            }
        }
    }

    /**
     * Handle URL parameters
     */
    function handleUrlParams() {
        const urlParams = new URLSearchParams(window.location.search);
        const action = urlParams.get('action');
        
        if (action === 'new') {
            // Focus on input
            setTimeout(() => {
                elements.inputContent.focus();
            }, 500);
        } else if (action === 'history') {
            // Scroll to history
            setTimeout(() => {
                elements.historyList.scrollIntoView({ behavior: 'smooth' });
            }, 500);
        }
    }

    /**
     * Initialize scroll animations
     */
    function initAnimations() {
        // Intersection Observer for scroll animations
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-slide-up');
                }
            });
        }, {
            threshold: 0.1
        });

        // Observe cards
        document.querySelectorAll('.glass-card').forEach(card => {
            observer.observe(card);
        });
    }

    /**
     * Fetch CSRF token from server
     */
    async function fetchCsrfToken() {
        try {
            const response = await fetch('api.php?action=csrf');
            const data = await response.json();
            if (data.success) {
                csrfToken = data.token;
            }
        } catch (error) {
            console.error('Failed to fetch CSRF token:', error);
        }
    }

    /**
     * Bind event listeners
     */
    function bindEvents() {
        // Conversion
        elements.convertBtn.addEventListener('click', handleConvert);
        elements.fabConvert.addEventListener('click', handleConvert);
        
        // Clear
        elements.clearBtn.addEventListener('click', () => {
            if (elements.inputContent.value) {
                if (confirm('Are you sure you want to clear all content?')) {
                    clearAll();
                }
            } else {
                clearAll();
            }
        });

        // Character count
        elements.inputContent.addEventListener('input', debounce(updateCharCount, 100));

        // File upload
        elements.dropZone.addEventListener('click', () => elements.fileInput.click());
        elements.dropZone.addEventListener('dragover', handleDragOver);
        elements.dropZone.addEventListener('dragleave', handleDragLeave);
        elements.dropZone.addEventListener('drop', handleFileDrop);
        elements.fileInput.addEventListener('change', handleFileSelect);

        // History
        elements.clearHistoryBtn.addEventListener('click', clearHistory);

        // View toggles
        elements.viewRawBtn.addEventListener('click', () => showOutputView('raw'));
        elements.viewPreviewBtn.addEventListener('click', () => showOutputView('preview'));

        // Download
        elements.downloadBtn.addEventListener('click', downloadOutput);

        // Format change with auto-convert
        let formatChangeTimeout;
        elements.outputFormat.addEventListener('change', () => {
            clearTimeout(formatChangeTimeout);
            if (currentOutput && elements.inputContent.value.trim()) {
                formatChangeTimeout = setTimeout(() => {
                    handleConvert();
                }, 300);
            }
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    handleConvert();
                } else if (e.key === 's') {
                    e.preventDefault();
                    downloadOutput();
                } else if (e.key === 'c' && document.activeElement !== elements.inputContent) {
                    e.preventDefault();
                    copyToClipboard();
                }
            }
            
            // Escape to close any modals or reset
            if (e.key === 'Escape') {
                elements.pwaPrompt.classList.remove('show');
            }
        });

        // Mobile menu (if needed in future)
        if (elements.menuBtn) {
            elements.menuBtn.addEventListener('click', () => {
                showToast('Menu feature coming soon!', 'info');
            });
        }

        // Paste handling
        elements.inputContent.addEventListener('paste', (e) => {
            setTimeout(updateCharCount, 0);
        });
    }

    /**
     * Clear all content
     */
    function clearAll() {
        elements.inputContent.value = '';
        updateCharCount();
        resetOutput();
        showToast('Content cleared', 'success');
        
        // Clear saved state
        sessionStorage.removeItem('techtext_state');
    }

    /**
     * Handle markup conversion
     */
    async function handleConvert() {
        const content = elements.inputContent.value.trim();
        
        if (!content) {
            showToast('Please enter some content to convert', 'error');
            elements.inputContent.focus();
            return;
        }

        if (!isOnline) {
            showToast('You are offline. Please connect to the internet to convert.', 'error');
            return;
        }

        const markupType = elements.markupType.value;
        const outputFormat = elements.outputFormat.value;

        // Show loading state
        const originalBtnContent = elements.convertBtn.innerHTML;
        elements.convertBtn.innerHTML = '<span class="loading mr-2"></span>Converting...';
        elements.convertBtn.disabled = true;
        elements.fabConvert.style.display = 'none';

        // Add converting animation to output container
        elements.outputContainer.classList.add('opacity-50');

        try {
            const formData = new FormData();
            formData.append('content', content);
            formData.append('markup_type', markupType);
            formData.append('output_format', outputFormat);
            formData.append('csrf_token', csrfToken);

            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 30000);

            const response = await fetch('api.php?action=convert', {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': csrfToken
                },
                body: formData,
                signal: controller.signal
            });

            clearTimeout(timeoutId);

            const data = await response.json();

            if (data.success) {
                currentOutput = data.output;
                currentOutputFormat = outputFormat;
                displayOutput(data.output, outputFormat);
                loadHistory();
                showToast('✨ Conversion successful!', 'success');
                
                // Scroll to output on mobile
                if (window.innerWidth < 1024) {
                    elements.outputContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            } else {
                showToast(data.error || 'Conversion failed', 'error');
            }
        } catch (error) {
            if (error.name === 'AbortError') {
                showToast('Conversion timed out. Please try with smaller content.', 'error');
            } else {
                console.error('Conversion error:', error);
                showToast('Network error. Please try again.', 'error');
            }
        } finally {
            elements.convertBtn.innerHTML = originalBtnContent;
            elements.convertBtn.disabled = false;
            elements.fabConvert.style.display = '';
            elements.outputContainer.classList.remove('opacity-50');
        }
    }

    /**
     * Display converted output
     */
    function displayOutput(output, format) {
        elements.outputPlaceholder.classList.add('hidden');
        
        if (format === 'json') {
            try {
                const parsed = JSON.parse(output);
                elements.outputRaw.textContent = JSON.stringify(parsed, null, 2);
                elements.outputPreview.innerHTML = parsed.html || output;
            } catch (e) {
                elements.outputRaw.textContent = output;
                elements.outputPreview.innerHTML = output;
            }
        } else if (format === 'plaintext') {
            elements.outputRaw.textContent = output;
            elements.outputPreview.innerHTML = escapeHtml(output).replace(/\n/g, '<br>');
        } else {
            elements.outputRaw.textContent = output;
            elements.outputPreview.innerHTML = output;
        }

        // Apply syntax highlighting to code blocks
        if (elements.outputPreview.querySelectorAll('pre code').length > 0) {
            if (window.Prism) {
                Prism.highlightAllUnder(elements.outputPreview);
            }
        }

        // Show preview by default for rich text, raw for others
        if (format === 'richtext') {
            showOutputView('preview');
        } else {
            showOutputView('raw');
        }

        // Add animation
        elements.outputContainer.classList.add('animate-fade-in');
        setTimeout(() => {
            elements.outputContainer.classList.remove('animate-fade-in');
        }, 500);
    }

    /**
     * Show specific output view
     */
    function showOutputView(view) {
        elements.outputRaw.classList.add('hidden');
        elements.outputPreview.classList.add('hidden');

        // Update button states
        if (view === 'raw') {
            elements.outputRaw.classList.remove('hidden');
            elements.viewRawBtn.classList.add('bg-white', 'text-blue-600', 'shadow-sm');
            elements.viewRawBtn.classList.remove('text-gray-600');
            elements.viewPreviewBtn.classList.remove('bg-white', 'text-blue-600', 'shadow-sm');
            elements.viewPreviewBtn.classList.add('text-gray-600');
        } else {
            elements.outputPreview.classList.remove('hidden');
            elements.viewPreviewBtn.classList.add('bg-white', 'text-blue-600', 'shadow-sm');
            elements.viewPreviewBtn.classList.remove('text-gray-600');
            elements.viewRawBtn.classList.remove('bg-white', 'text-blue-600', 'shadow-sm');
            elements.viewRawBtn.classList.add('text-gray-600');
        }
    }

    /**
     * Reset output display
     */
    function resetOutput() {
        elements.outputPlaceholder.classList.remove('hidden');
        elements.outputRaw.classList.add('hidden');
        elements.outputPreview.classList.add('hidden');
        elements.outputRaw.textContent = '';
        elements.outputPreview.innerHTML = '';
        currentOutput = '';
        
        // Reset button states
        elements.viewPreviewBtn.classList.add('bg-white', 'text-blue-600', 'shadow-sm');
        elements.viewRawBtn.classList.remove('bg-white', 'text-blue-600', 'shadow-sm');
    }

    /**
     * Handle file drag over
     */
    function handleDragOver(e) {
        e.preventDefault();
        e.stopPropagation();
        elements.dropZone.classList.add('dragover');
    }

    /**
     * Handle file drag leave
     */
    function handleDragLeave(e) {
        e.preventDefault();
        e.stopPropagation();
        elements.dropZone.classList.remove('dragover');
    }

    /**
     * Handle file drop
     */
    function handleFileDrop(e) {
        e.preventDefault();
        e.stopPropagation();
        elements.dropZone.classList.remove('dragover');

        const files = e.dataTransfer.files;
        if (files.length > 0) {
            processFile(files[0]);
        }
    }

    /**
     * Handle file select
     */
    function handleFileSelect(e) {
        const files = e.target.files;
        if (files.length > 0) {
            processFile(files[0]);
        }
    }

    /**
     * Process uploaded file
     */
    async function processFile(file) {
        // Check file size (2MB max)
        if (file.size > 2 * 1024 * 1024) {
            showToast('File too large. Maximum size is 2MB.', 'error');
            return;
        }

        // Detect file type and set appropriate markup type
        const fileName = file.name.toLowerCase();
        if (fileName.endsWith('.md') || fileName.endsWith('.markdown')) {
            elements.markupType.value = 'markdown';
        } else if (fileName.endsWith('.html') || fileName.endsWith('.htm')) {
            elements.markupType.value = 'html';
        } else if (fileName.endsWith('.rst')) {
            elements.markupType.value = 'rst';
        } else if (fileName.endsWith('.textile')) {
            elements.markupType.value = 'textile';
        } else if (fileName.endsWith('.bbcode') || fileName.endsWith('.bb')) {
            elements.markupType.value = 'bbcode';
        }

        const formData = new FormData();
        formData.append('file', file);
        formData.append('csrf_token', csrfToken);

        // Show loading
        showToast('Uploading file...', 'info');

        try {
            const response = await fetch('api.php?action=upload', {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': csrfToken
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                elements.inputContent.value = data.content;
                updateCharCount();
                showToast(`File "${data.filename}" uploaded successfully`, 'success');
                
                // Auto-convert after short delay
                setTimeout(() => {
                    if (elements.inputContent.value.trim()) {
                        handleConvert();
                    }
                }, 500);
            } else {
                showToast(data.error || 'Upload failed', 'error');
            }
        } catch (error) {
            console.error('Upload error:', error);
            showToast('Upload failed. Please try again.', 'error');
        }

        // Reset file input
        elements.fileInput.value = '';
    }

    /**
     * Load conversion history
     */
    async function loadHistory() {
        if (!isOnline) {
            // Try to load from cache or show offline message
            return;
        }

        try {
            const response = await fetch('api.php?action=history');
            const data = await response.json();

            if (data.success) {
                renderHistory(data.history);
            }
        } catch (error) {
            console.error('Failed to load history:', error);
        }
    }

    /**
     * Render history list
     */
    function renderHistory(history) {
        if (history.length === 0) {
            elements.historyList.innerHTML = elements.emptyHistory.outerHTML;
            return;
        }

        const html = history.map((item, index) => `
            <div class="history-item p-4 border-b border-gray-100 cursor-pointer group" 
                 data-id="${item.id}"
                 style="animation-delay: ${index * 0.05}s">
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0" onclick="restoreConversion(${item.id})">
                        <div class="flex items-center space-x-2 mb-1">
                            <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded">
                                ${formatMarkupType(item.markup_type)}
                            </span>
                            <i class="fas fa-arrow-right text-gray-300 text-xs"></i>
                            <span class="text-xs font-semibold text-green-600 bg-green-50 px-2 py-0.5 rounded">
                                ${formatOutputType(item.output_format)}
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 truncate">${escapeHtml(item.preview)}...</p>
                        <p class="text-xs text-gray-400 mt-1 flex items-center">
                            <i class="far fa-clock mr-1"></i>${formatDate(item.created_at)}
                        </p>
                    </div>
                    <button onclick="deleteConversion(${item.id}, event)" 
                            class="text-gray-300 hover:text-red-500 p-2 rounded-lg hover:bg-red-50 transition-all duration-200 opacity-0 group-hover:opacity-100"
                            title="Delete">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>
        `).join('');

        elements.historyList.innerHTML = html;
    }

    /**
     * Restore conversion from history
     */
    async function restoreConversion(id) {
        if (!isOnline) {
            showToast('You are offline. Cannot restore from history.', 'error');
            return;
        }

        try {
            const response = await fetch(`api.php?action=get&id=${id}`);
            const data = await response.json();

            if (data.success) {
                elements.inputContent.value = data.conversion.input_content;
                elements.markupType.value = data.conversion.markup_type;
                elements.outputFormat.value = data.conversion.output_format;
                updateCharCount();
                
                currentOutput = data.conversion.output_content;
                currentOutputFormat = data.conversion.output_format;
                displayOutput(currentOutput, currentOutputFormat);
                
                showToast('Conversion restored from history', 'success');
                
                // Scroll to top on mobile
                if (window.innerWidth < 1024) {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }
        } catch (error) {
            console.error('Failed to restore conversion:', error);
            showToast('Failed to restore conversion', 'error');
        }
    }

    /**
     * Delete single conversion
     */
    async function deleteConversion(id, event) {
        if (event) event.stopPropagation();
        
        if (!confirm('Are you sure you want to delete this conversion?')) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('id', id);
            formData.append('csrf_token', csrfToken);

            const response = await fetch('api.php?action=delete', {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': csrfToken
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Animate removal
                const item = document.querySelector(`[data-id="${id}"]`);
                if (item) {
                    item.style.opacity = '0';
                    item.style.transform = 'translateX(20px)';
                    setTimeout(() => loadHistory(), 300);
                } else {
                    loadHistory();
                }
                showToast('Conversion deleted', 'success');
            }
        } catch (error) {
            console.error('Failed to delete conversion:', error);
            showToast('Failed to delete conversion', 'error');
        }
    }

    /**
     * Clear all history
     */
    async function clearHistory() {
        if (!confirm('Are you sure you want to clear all history? This cannot be undone.')) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('csrf_token', csrfToken);

            const response = await fetch('api.php?action=clear', {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': csrfToken
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Animate clearing
                elements.historyList.style.opacity = '0';
                setTimeout(() => {
                    loadHistory();
                    elements.historyList.style.opacity = '1';
                }, 300);
                showToast('History cleared', 'success');
            }
        } catch (error) {
            console.error('Failed to clear history:', error);
            showToast('Failed to clear history', 'error');
        }
    }

    /**
     * Initialize clipboard functionality
     */
    function initClipboard() {
        if (!ClipboardJS.isSupported()) {
            elements.copyBtn.style.display = 'none';
            return;
        }

        const clipboard = new ClipboardJS('#copyBtn');

        clipboard.on('success', function(e) {
            showToast('Copied to clipboard!', 'success');
            e.clearSelection();
        });

        clipboard.on('error', function(e) {
            showToast('Failed to copy. Please try manually.', 'error');
        });
    }

    /**
     * Copy output to clipboard manually
     */
    function copyToClipboard() {
        if (!currentOutput) {
            showToast('Nothing to copy', 'error');
            return;
        }

        navigator.clipboard.writeText(currentOutput).then(() => {
            showToast('Copied to clipboard!', 'success');
        }).catch(() => {
            // Fallback
            const textarea = document.createElement('textarea');
            textarea.value = currentOutput;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            
            try {
                document.execCommand('copy');
                showToast('Copied to clipboard!', 'success');
            } catch (err) {
                showToast('Failed to copy', 'error');
            }
            
            document.body.removeChild(textarea);
        });
    }

    /**
     * Download output as file
     */
    function downloadOutput() {
        if (!currentOutput) {
            showToast('Nothing to download', 'error');
            return;
        }

        let filename = 'techtext-converted';
        let mimeType = 'text/plain';

        switch (currentOutputFormat) {
            case 'html':
            case 'richtext':
                filename += '.html';
                mimeType = 'text/html';
                break;
            case 'json':
                filename += '.json';
                mimeType = 'application/json';
                break;
            default:
                filename += '.txt';
        }

        const blob = new Blob([currentOutput], { type: mimeType });
        
        // Use FileSaver API if available, otherwise fallback
        if (window.navigator.msSaveOrOpenBlob) {
            window.navigator.msSaveOrOpenBlob(blob, filename);
        } else {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }

        showToast(`Downloaded as ${filename}`, 'success');
    }

    /**
     * Update character count
     */
    function updateCharCount() {
        const count = elements.inputContent.value.length;
        const max = 1000000;
        const percentage = (count / max) * 100;
        
        let colorClass = 'text-gray-500';
        if (percentage > 90) {
            colorClass = 'text-red-500';
        } else if (percentage > 75) {
            colorClass = 'text-yellow-500';
        }
        
        elements.charCount.innerHTML = `<span class="${colorClass}">${count.toLocaleString()} / ${max.toLocaleString()}</span>`;
    }

    /**
     * Show toast notification
     */
    function showToast(message, type = 'success') {
        elements.toast.textContent = message;
        elements.toast.className = `toast ${type}`;
        
        // Add icon
        const icon = type === 'success' ? '✓' : type === 'error' ? '✗' : 'ℹ';
        elements.toast.innerHTML = `<span class="mr-2 text-lg">${icon}</span>${message}`;
        
        elements.toast.classList.add('show');

        // Auto-hide after 3 seconds
        clearTimeout(window.toastTimeout);
        window.toastTimeout = setTimeout(() => {
            elements.toast.classList.remove('show');
        }, 3000);

        // Hide on click
        elements.toast.onclick = () => {
            elements.toast.classList.remove('show');
        };
    }

    /**
     * Format markup type for display
     */
    function formatMarkupType(type) {
        const types = {
            'markdown': 'Markdown',
            'bbcode': 'BBCode',
            'rst': 'RST',
            'textile': 'Textile',
            'wiki': 'Wiki',
            'html': 'HTML'
        };
        return types[type] || type;
    }

    /**
     * Format output type for display
     */
    function formatOutputType(type) {
        const types = {
            'plaintext': 'Text',
            'richtext': 'Rich Text',
            'html': 'HTML',
            'json': 'JSON'
        };
        return types[type] || type;
    }

    /**
     * Format date for display
     */
    function formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        
        // Less than a minute
        if (diff < 60000) {
            return 'Just now';
        }
        // Less than an hour
        if (diff < 3600000) {
            const minutes = Math.floor(diff / 60000);
            return `${minutes}m ago`;
        }
        // Less than a day
        if (diff < 86400000) {
            const hours = Math.floor(diff / 3600000);
            return `${hours}h ago`;
        }
        // Less than a week
        if (diff < 604800000) {
            const days = Math.floor(diff / 86400000);
            return `${days}d ago`;
        }
        
        return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
    }

    /**
     * Escape HTML entities
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Debounce function
     */
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Expose functions to global scope for inline event handlers
    window.restoreConversion = restoreConversion;
    window.deleteConversion = deleteConversion;

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();