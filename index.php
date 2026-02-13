<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="description" content="TechText - Convert markup languages to various formats including plain text, rich text, and HTML">
    <meta name="author" content="Santosh Baral">
    <meta name="copyright" content="Techzen Corporation">
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#2563eb">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="TechText">
    <meta name="application-name" content="TechText">
    <meta name="msapplication-TileColor" content="#2563eb">
    <meta name="msapplication-config" content="none">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="manifest.json">
    
    <!-- Favicon & Icons -->
    <link rel="icon" type="image/svg+xml" href="icons/icon.svg">
    <link rel="alternate icon" type="image/png" href="icons/icon.php?size=72">
    <link rel="apple-touch-icon" href="icons/icon.php?size=192">
    <link rel="mask-icon" href="icons/icon.svg" color="#2563eb">
    
    <title>TechText - Markup Language Converter</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Configure Tailwind -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-out',
                        'slide-up': 'slideUp 0.5s ease-out',
                        'slide-down': 'slideDown 0.3s ease-out',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'bounce-slow': 'bounce 2s infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(20px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        },
                        slideDown: {
                            '0%': { transform: 'translateY(-10px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Prism.js -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    
    <!-- Clipboard.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.11/clipboard.min.js"></script>
    
    <style>
        * {
            -webkit-tap-highlight-color: transparent;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        /* Animated background */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: 
                radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(120, 219, 255, 0.3) 0%, transparent 50%);
            animation: gradientShift 15s ease infinite;
        }
        
        @keyframes gradientShift {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -30px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
        }
        
        /* Glassmorphism card */
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.1),
                0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .glass-header {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.3);
        }
        
        /* Button animations */
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-primary:hover::before {
            left: 100%;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        /* Input styling */
        .input-field {
            transition: all 0.3s ease;
            border: 2px solid transparent;
            background: linear-gradient(white, white) padding-box,
                        linear-gradient(135deg, #e5e7eb 0%, #f3f4f6 100%) border-box;
        }
        
        .input-field:focus {
            border-color: #3b82f6;
            background: white;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }
        
        /* Drop zone styling */
        .drop-zone {
            border: 2px dashed rgba(59, 130, 246, 0.3);
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(37, 99, 235, 0.05) 100%);
            transition: all 0.3s ease;
        }
        
        .drop-zone:hover, .drop-zone.dragover {
            border-color: #3b82f6;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(37, 99, 235, 0.1) 100%);
            transform: scale(1.01);
        }
        
        /* Output preview */
        .output-preview {
            max-height: 500px;
            overflow-y: auto;
            line-height: 1.8;
        }
        
        .output-preview h1, .output-preview h2, .output-preview h3,
        .output-preview h4, .output-preview h5, .output-preview h6 {
            margin-top: 1.5em;
            margin-bottom: 0.75em;
            font-weight: 700;
            color: #1f2937;
        }
        
        .output-preview h1 { font-size: 2em; border-bottom: 3px solid #e5e7eb; padding-bottom: 0.3em; }
        .output-preview h2 { font-size: 1.75em; border-bottom: 2px solid #e5e7eb; padding-bottom: 0.3em; }
        .output-preview h3 { font-size: 1.5em; }
        .output-preview h4 { font-size: 1.25em; }
        
        .output-preview p {
            margin-bottom: 1.25em;
            color: #374151;
        }
        
        .output-preview blockquote {
            border-left: 4px solid #3b82f6;
            background: rgba(59, 130, 246, 0.05);
            padding: 1em 1.5em;
            margin: 1.5em 0;
            border-radius: 0 8px 8px 0;
            font-style: italic;
            color: #4b5563;
        }
        
        .output-preview code {
            background: rgba(59, 130, 246, 0.1);
            color: #1d4ed8;
            padding: 0.2em 0.5em;
            border-radius: 4px;
            font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
            font-size: 0.9em;
            font-weight: 500;
        }
        
        .output-preview pre {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: #e2e8f0;
            padding: 1.5em;
            border-radius: 12px;
            overflow-x: auto;
            margin: 1.5em 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .output-preview pre code {
            background: transparent;
            color: inherit;
            padding: 0;
            font-size: 0.9em;
        }
        
        .output-preview ul, .output-preview ol {
            margin-left: 1.5em;
            margin-bottom: 1.25em;
        }
        
        .output-preview li {
            margin-bottom: 0.5em;
        }
        
        .output-preview table {
            width: 100%;
            border-collapse: collapse;
            margin: 1.5em 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        .output-preview th {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            font-weight: 600;
            padding: 1em;
            text-align: left;
        }
        
        .output-preview td {
            padding: 1em;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .output-preview tr:hover {
            background: rgba(59, 130, 246, 0.05);
        }
        
        .output-preview a {
            color: #2563eb;
            text-decoration: none;
            border-bottom: 2px solid rgba(37, 99, 235, 0.3);
            transition: all 0.2s ease;
        }
        
        .output-preview a:hover {
            border-bottom-color: #2563eb;
            background: rgba(37, 99, 235, 0.1);
        }
        
        .output-preview hr {
            border: none;
            height: 2px;
            background: linear-gradient(90deg, transparent, #e5e7eb, transparent);
            margin: 2em 0;
        }
        
        /* Loading animation */
        .loading {
            display: inline-block;
            width: 24px;
            height: 24px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Toast notification */
        .toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            padding: 1em 1.5em;
            border-radius: 12px;
            color: white;
            font-weight: 500;
            z-index: 9999;
            transform: translateY(100px) scale(0.9);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }
        
        .toast.show {
            transform: translateY(0) scale(1);
            opacity: 1;
        }
        
        .toast.success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        
        .toast.error {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }
        
        .toast.info {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }
        
        /* PWA Install Prompt */
        .pwa-prompt {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 1em 1.5em;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.15);
            z-index: 9998;
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }
        
        .pwa-prompt.show {
            transform: translateY(0);
        }
        
        /* History items */
        .history-item {
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }
        
        .history-item:hover {
            background: rgba(59, 130, 246, 0.05);
            border-left-color: #3b82f6;
            transform: translateX(4px);
        }
        
        /* Floating action button for mobile */
        .fab {
            position: fixed;
            bottom: 24px;
            right: 24px;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            display: none;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
            z-index: 100;
            transition: all 0.3s ease;
        }
        
        .fab:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.5);
        }
        
        @media (max-width: 768px) {
            .fab {
                display: flex;
            }
        }
        
        /* Smooth scroll behavior */
        html {
            scroll-behavior: smooth;
        }
        
        /* Focus visible */
        *:focus-visible {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }
        
        /* Selection color */
        ::selection {
            background: rgba(59, 130, 246, 0.3);
            color: #1e40af;
        }
    </style>
</head>
<body class="antialiased">
    <!-- Animated Background -->
    <div class="bg-animation"></div>
    
    <!-- Header -->
    <header class="glass-header sticky top-0 z-50 animate-fade-in">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <div class="absolute inset-0 bg-blue-500 rounded-xl blur-lg opacity-50"></div>
                        <div class="relative w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-file-code text-white text-xl"></i>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">TechText</h1>
                        <p class="text-sm text-gray-500 font-medium">Markup Language Converter</p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 pb-24">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Input Section -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Conversion Controls -->
                <div class="glass-card rounded-2xl p-6 animate-slide-up" style="animation-delay: 0.1s;">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                        <div class="group">
                            <label for="markupType" class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                                <i class="fas fa-code mr-2 text-blue-500"></i>Input Format
                            </label>
                            <div class="relative">
                                <select id="markupType" class="input-field w-full px-4 py-3 rounded-xl text-gray-700 appearance-none cursor-pointer">
                                    <option value="markdown">📝 Markdown</option>
                                    <option value="bbcode">💬 BBCode</option>
                                    <option value="rst">📄 reStructuredText</option>
                                    <option value="textile">🧵 Textile</option>
                                    <option value="wiki">📚 Wiki Markup</option>
                                    <option value="html">🌐 HTML</option>
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                            </div>
                        </div>
                        <div class="group">
                            <label for="outputFormat" class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                                <i class="fas fa-exchange-alt mr-2 text-green-500"></i>Output Format
                            </label>
                            <div class="relative">
                                <select id="outputFormat" class="input-field w-full px-4 py-3 rounded-xl text-gray-700 appearance-none cursor-pointer">
                                    <option value="plaintext" selected>📄 Plain Text</option>
                                    <option value="richtext">✨ Rich Text (HTML)</option>
                                    <option value="html">🧹 Clean HTML</option>
                                    <option value="json">⚙️ JSON</option>
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex flex-wrap gap-3">
                        <button id="convertBtn" class="btn-primary text-white px-8 py-3 rounded-xl font-semibold shadow-lg flex items-center">
                            <i class="fas fa-magic mr-2"></i>Convert
                        </button>
                        <button id="clearBtn" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-xl font-semibold transition-all duration-200 flex items-center hover:shadow-md">
                            <i class="fas fa-trash-alt mr-2"></i>Clear
                        </button>
                        <button id="copyBtn" class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-xl font-semibold transition-all duration-200 flex items-center hover:shadow-lg hover:-translate-y-0.5" data-clipboard-target="#outputContent">
                            <i class="fas fa-copy mr-2"></i>Copy
                        </button>
                        <button id="downloadBtn" class="bg-purple-500 hover:bg-purple-600 text-white px-6 py-3 rounded-xl font-semibold transition-all duration-200 flex items-center hover:shadow-lg hover:-translate-y-0.5">
                            <i class="fas fa-download mr-2"></i>Download
                        </button>
                    </div>
                </div>

                <!-- Input Area -->
                <div class="glass-card rounded-2xl overflow-hidden animate-slide-up" style="animation-delay: 0.2s;">
                    <div class="border-b border-gray-100 px-6 py-4 flex items-center justify-between bg-gradient-to-r from-white to-gray-50">
                        <h2 class="text-lg font-bold text-gray-900 flex items-center">
                            <span class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-edit text-blue-600"></i>
                            </span>
                            Input Content
                        </h2>
                        <span id="charCount" class="text-sm text-gray-500 font-medium bg-gray-100 px-3 py-1 rounded-full">0 characters</span>
                    </div>
                    
                    <!-- File Upload -->
                    <div class="px-6 pt-5">
                        <div id="dropZone" class="drop-zone rounded-xl p-8 text-center cursor-pointer group">
                            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-cloud-upload-alt text-3xl text-blue-500"></i>
                            </div>
                            <p class="text-gray-700 font-semibold mb-1">Drag & drop a file here</p>
                            <p class="text-sm text-gray-400">or click to browse files</p>
                            <p class="text-xs text-gray-400 mt-2">Supports .md, .txt, .html, .rst (max 2MB)</p>
                            <input type="file" id="fileInput" class="hidden" accept=".md,.txt,.html,.rst,.textile,.bbcode">
                        </div>
                    </div>
                    
                    <!-- Text Input -->
                    <div class="p-6">
                        <textarea 
                            id="inputContent" 
                            class="input-field w-full h-72 p-5 rounded-xl resize-none font-mono text-sm text-gray-700"
                            placeholder="Paste your markup content here...&#10;&#10;Example Markdown:&#10;# Heading&#10;**Bold text** and *italic*&#10;- List item 1&#10;- List item 2&#10;&#10;[Link](https://example.com)"></textarea>
                    </div>
                </div>

                <!-- Output Area -->
                <div class="glass-card rounded-2xl overflow-hidden animate-slide-up" style="animation-delay: 0.3s;">
                    <div class="border-b border-gray-100 px-6 py-4 flex items-center justify-between bg-gradient-to-r from-white to-gray-50">
                        <h2 class="text-lg font-bold text-gray-900 flex items-center">
                            <span class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-eye text-green-600"></i>
                            </span>
                            Output Preview
                        </h2>
                        <div class="flex items-center space-x-3 bg-gray-100 rounded-lg p-1">
                        <button id="viewRawBtn" class="text-sm px-4 py-2 rounded-md bg-white text-blue-600 shadow-sm font-medium">
                            <i class="fas fa-code mr-1"></i>Source
                        </button>
                        <button id="viewPreviewBtn" class="text-sm px-4 py-2 rounded-md text-gray-600 hover:text-blue-600 hover:bg-white transition-all duration-200 font-medium">
                            <i class="fas fa-eye mr-1"></i>Preview
                        </button>
                        </div>
                    </div>
                    <div class="p-6">
                        <div id="outputContainer" class="output-preview border-2 border-dashed border-gray-200 rounded-xl p-6 min-h-80 bg-gradient-to-br from-gray-50 to-white">
                            <div id="outputPlaceholder" class="text-center text-gray-400 py-20">
                                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                    <i class="fas fa-file-code text-5xl opacity-30"></i>
                                </div>
                                <p class="text-lg font-medium">Converted content will appear here</p>
                                <p class="text-sm mt-2">Select your markup and click Convert</p>
                            </div>
                            <pre id="outputRaw" class="hidden whitespace-pre-wrap font-mono text-sm text-gray-700 bg-gray-900 text-gray-100 p-6 rounded-lg overflow-x-auto"></pre>
                            <div id="outputPreview" class="hidden output-preview"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- History Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <div class="glass-card rounded-2xl sticky top-24 animate-slide-up" style="animation-delay: 0.4s;">
                    <div class="border-b border-gray-100 px-6 py-4 flex items-center justify-between bg-gradient-to-r from-white to-gray-50">
                        <h2 class="text-lg font-bold text-gray-900 flex items-center">
                            <span class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-history text-purple-600"></i>
                            </span>
                            History
                        </h2>
                        <button id="clearHistoryBtn" class="text-sm text-red-500 hover:text-red-700 font-medium px-3 py-1 rounded-lg hover:bg-red-50 transition-all duration-200">
                            Clear
                        </button>
                    </div>
                    <div id="historyList" class="max-h-96 overflow-y-auto">
                        <div id="emptyHistory" class="p-8 text-center text-gray-400">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-clock text-3xl opacity-30"></i>
                            </div>
                            <p class="text-sm">No conversions yet</p>
                            <p class="text-xs mt-1 text-gray-300">Your recent conversions will appear here</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Tips Card -->
                <div class="glass-card rounded-2xl p-6 animate-slide-up" style="animation-delay: 0.5s;">
                    <h3 class="text-md font-bold text-gray-900 mb-4 flex items-center">
                        <span class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-lightbulb text-yellow-600"></i>
                        </span>
                        Quick Tips
                    </h3>
                    <ul class="text-sm text-gray-600 space-y-3">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3 flex-shrink-0"></i>
                            <span>Supports <strong>Markdown, BBCode, RST, Textile</strong></span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3 flex-shrink-0"></i>
                            <span>Drag & drop files to upload instantly</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3 flex-shrink-0"></i>
                            <span>Click history items to restore</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3 flex-shrink-0"></i>
                            <span><strong>Ctrl+Enter</strong> to convert quickly</span>
                        </li>
                    </ul>
                </div>

                <!-- Supported Formats Card -->
                <div class="glass-card rounded-2xl p-6 animate-slide-up" style="animation-delay: 0.6s;">
                    <h3 class="text-md font-bold text-gray-900 mb-4 flex items-center">
                        <span class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-info-circle text-blue-600"></i>
                        </span>
                        Supported Formats
                    </h3>
                    <div class="space-y-4 text-sm">
                        <div>
                            <span class="text-gray-500 font-medium block mb-1">Input:</span>
                            <div class="flex flex-wrap gap-2">
                                <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs font-medium">Markdown</span>
                                <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs font-medium">BBCode</span>
                                <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs font-medium">RST</span>
                                <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs font-medium">Textile</span>
                                <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs font-medium">Wiki</span>
                                <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs font-medium">HTML</span>
                            </div>
                        </div>
                        <div>
                            <span class="text-gray-500 font-medium block mb-1">Output:</span>
                            <div class="flex flex-wrap gap-2">
                                <span class="px-2 py-1 bg-green-50 text-green-700 rounded text-xs font-medium">Plain Text</span>
                                <span class="px-2 py-1 bg-green-50 text-green-700 rounded text-xs font-medium">Rich Text</span>
                                <span class="px-2 py-1 bg-green-50 text-green-700 rounded text-xs font-medium">HTML</span>
                                <span class="px-2 py-1 bg-green-50 text-green-700 rounded text-xs font-medium">JSON</span>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="glass-card mt-12 mx-4 mb-6 rounded-2xl border-t-4 border-blue-500">
        <div class="max-w-7xl mx-auto px-6 py-8">
            <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                <div class="text-center md:text-left">
                    <p class="text-lg font-bold text-gray-800 mb-1">TechText</p>
                    <p class="text-sm text-gray-500">
                        &copy; 2026 All rights reserved.
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600 mb-2">Built by</p>
                    <p class="text-lg font-bold text-blue-600">Santosh Baral</p>
                    <a href="https://techzeninc.com" target="_blank" class="text-sm text-gray-500 hover:text-blue-600 transition font-medium">
                        Techzen Corporation
                    </a>
                </div>
                <div class="text-center md:text-right">
                    <p class="text-sm text-gray-500">
                        Made with <i class="fas fa-heart text-red-500 mx-1 animate-pulse"></i> for developers
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Floating Action Button (Mobile) -->
    <button id="fabConvert" class="fab">
        <i class="fas fa-magic text-xl"></i>
    </button>

    <!-- Toast Container -->
    <div id="toast" class="toast"></div>



    <!-- Application JavaScript -->
    <script src="app.js"></script>
    
    <!-- PWA Registration -->
    <script>
        // Register Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('sw.js')
                    .then((registration) => {
                        console.log('SW registered: ', registration);
                    })
                    .catch((registrationError) => {
                        console.log('SW registration failed: ', registrationError);
                    });
            });
        }

        // Simple PWA registration
        console.log('[PWA] Service Worker supported:', 'serviceWorker' in navigator);
        
        // Check if app is already installed
        if (window.matchMedia('(display-mode: standalone)').matches) {
            console.log('[PWA] App running in standalone mode');
        }
    </script>
</body>
</html>