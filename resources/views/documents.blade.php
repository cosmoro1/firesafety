<x-layout>
    {{-- Success Message --}}
    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    {{-- Error Message --}}
    @if($errors->any())
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Document Management</h2>
            <p class="text-gray-500 text-sm">Centralized repository for official BFP documents and files</p>
        </div>
        <button onclick="openUploadModal()" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-lg font-medium shadow-sm transition flex items-center">
            <i class="fa-solid fa-cloud-arrow-up mr-2"></i> Upload Document
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        
        {{-- Sidebar Categories --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden sticky top-24">
                <div class="p-4 border-b border-gray-100">
                    <h3 class="font-bold text-gray-800">Categories</h3>
                </div>
                <div class="p-2 space-y-1" id="category-list">
                    
                    {{-- Helper for category buttons (REMOVED Training/Announcement) --}}
                    @php
                        $categories = [
                            'all' => ['label' => 'All Documents', 'icon' => 'fa-file-lines'],
                            'memo' => ['label' => 'Memorandums', 'icon' => 'fa-clipboard'],
                            'policy' => ['label' => 'Policies', 'icon' => 'fa-shield-halved'],
                            'circular' => ['label' => 'Circulars', 'icon' => 'fa-circle-question'],
                            'sop' => ['label' => 'SOPs', 'icon' => 'fa-list-check'],
                        ];
                    @endphp

                    @foreach($categories as $key => $data)
                        <button onclick="filterDocuments('{{ $key }}', this)" 
                            class="category-btn w-full flex items-center justify-between px-4 py-3 rounded-lg transition-colors cursor-pointer group {{ $key === 'all' ? 'bg-red-50 text-red-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid {{ $data['icon'] }} w-5 text-center {{ $key === 'all' ? '' : 'text-gray-400 group-hover:text-gray-600' }}"></i>
                                <span class="font-medium text-sm">{{ $data['label'] }}</span>
                            </div>
                            <span class="badge text-xs {{ $key === 'all' ? 'font-bold bg-white text-red-700 shadow-sm' : 'font-medium text-gray-400' }} px-2 py-0.5 rounded-full">
                                {{ $counts[$key] ?? 0 }}
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="lg:col-span-3">
            
            {{-- Search and Sort --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-6 flex flex-col md:flex-row gap-4">
                <form action="{{ route('documents.index') }}" method="GET" class="contents">
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" name="search" id="docSearch" onkeyup="clientSideSearch()" value="{{ request('search') }}" placeholder="Search documents by title..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500">
                    </div>
                    <div class="w-full md:w-48">
                        <select name="sort" onchange="this.form.submit()" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-red-500 bg-white text-gray-600">
                            <option value="recent" {{ request('sort') == 'recent' ? 'selected' : '' }}>Most Recent</option>
                            <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest</option>
                            <option value="az" {{ request('sort') == 'az' ? 'selected' : '' }}>A-Z</option>
                        </select>
                    </div>
                </form>
            </div>

            {{-- Document List --}}
            <div class="space-y-4" id="document-container">
                @forelse($documents as $doc)
                    @php
                        // Removed logic for training/announcement
                        $badgeColor = match($doc->category) {
                            'memo' => 'bg-blue-100 text-blue-800',
                            'policy' => 'bg-green-100 text-green-800',
                            'sop' => 'bg-orange-100 text-orange-800',
                            'circular' => 'bg-purple-100 text-purple-800',
                            default => 'bg-gray-100 text-gray-800'
                        };
                        
                        $catLabel = match($doc->category) {
                            'memo' => 'Memo',
                            'policy' => 'Policy',
                            'sop' => 'SOP',
                            'circular' => 'Circular',
                            default => ucfirst($doc->category)
                        };
                    @endphp

                    <div class="doc-item bg-white rounded-xl border border-gray-200 shadow-sm p-5 hover:shadow-md transition" data-category="{{ $doc->category }}">
                        <div class="flex flex-col sm:flex-row items-start gap-4">
                            <div class="h-12 w-12 rounded-lg bg-red-50 flex items-center justify-center text-red-500 flex-shrink-0">
                                <i class="fa-regular fa-file-pdf text-2xl"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between mb-1">
                                    <h3 class="text-base font-bold text-gray-900 truncate pr-4 doc-title">{{ $doc->title }}</h3>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeColor }}">{{ $catLabel }}</span>
                                </div>
                                <div class="flex items-center text-xs text-gray-500 gap-4 mb-3 flex-wrap">
                                    <span class="flex items-center"><i class="fa-regular fa-user mr-1.5"></i> {{ $doc->uploader->name ?? 'Unknown' }}</span>
                                    <span class="flex items-center"><i class="fa-regular fa-calendar mr-1.5"></i> {{ $doc->created_at->format('Y-m-d') }}</span>
                                    <span class="flex items-center"><i class="fa-solid fa-download mr-1.5"></i> {{ $doc->downloads }} downloads</span>
                                </div>
                                @if($doc->description)
                                    <p class="text-sm text-gray-600 mb-3">{{ Str::limit($doc->description, 100) }}</p>
                                @endif
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3 text-xs text-gray-500 font-mono uppercase">
                                        <span>{{ $doc->file_type }}</span><span class="w-1 h-1 bg-gray-300 rounded-full"></span><span>{{ $doc->readable_size }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('documents.preview', $doc->id) }}" target="_blank" class="text-gray-600 hover:text-gray-900 text-xs font-medium px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50 transition">Preview</a>
                                        <a href="{{ route('documents.download', $doc->id) }}" class="bg-red-600 hover:bg-red-700 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition flex items-center">
                                            <i class="fa-solid fa-download mr-1.5"></i> Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10 bg-white rounded-xl border border-dashed border-gray-300">
                        <i class="fa-regular fa-folder-open text-4xl text-gray-300 mb-3"></i>
                        <p class="text-gray-500">No documents found.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Upload Modal --}}
    <div id="uploadModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeUploadModal()"></div>

        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-xl font-semibold leading-6 text-gray-900" id="modal-title">Upload New Document</h3>
                        <button type="button" onclick="closeUploadModal()" class="text-gray-400 hover:text-gray-500">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>

                    <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="px-6 py-6">
                            
                            <div class="mb-5">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Document File <span class="text-red-500">*</span></label>
                                <div class="mt-1 flex justify-center rounded-md border-2 border-dashed border-gray-300 px-6 pt-5 pb-6 hover:border-red-400 transition cursor-pointer bg-gray-50 relative">
                                    <div class="space-y-1 text-center">
                                        <i class="fa-solid fa-cloud-arrow-up text-3xl text-gray-400"></i>
                                        <div class="flex text-sm text-gray-600 justify-center">
                                            <label for="file-upload" class="relative cursor-pointer rounded-md bg-white font-medium text-red-600 focus-within:outline-none hover:text-red-500">
                                                <span>Upload a file</span>
                                                <input id="file-upload" name="file_upload" type="file" class="sr-only" required onchange="showFileName(this)">
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500">PDF, DOC, DOCX up to 10MB</p>
                                        <p id="file-name-display" class="text-xs font-bold text-gray-800 mt-2"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Document Title <span class="text-red-500">*</span></label>
                                <input type="text" name="title" required placeholder="e.g., Annual Fire Safety Report" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-red-500">
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                                <select name="category" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-red-500 bg-white">
                                    <option value="">Select Category</option>
                                    <option value="memo">Memorandum</option>
                                    <option value="policy">Policy</option>
                                    <option value="circular">Circular</option>
                                    <option value="sop">SOP</option>
                                </select>
                            </div>

                            <div class="mb-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                                <textarea name="description" rows="3" placeholder="Add a brief description..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-red-500"></textarea>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="submit" class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">Upload</button>
                            <button type="button" onclick="closeUploadModal()" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
      function filterDocuments(category, btnElement) {
        // 1. Filter the Content
        const items = document.querySelectorAll('.doc-item');
        items.forEach(item => {
            if (category === 'all' || item.getAttribute('data-category') === category) {
                item.classList.remove('hidden');
                item.classList.remove('hidden-by-category');
            } else {
                item.classList.add('hidden');
                item.classList.add('hidden-by-category');
            }
        });

        // 2. Reset ALL Buttons to Inactive State
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.className = "category-btn w-full flex items-center justify-between px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors cursor-pointer group";
            
            const icon = btn.querySelector('i');
            if(icon) {
                icon.classList.remove('text-red-700', 'text-white'); 
                icon.classList.add('text-gray-400', 'group-hover:text-gray-600');
            }

            const badge = btn.querySelector('.badge');
            if(badge) badge.className = "badge text-xs font-medium text-gray-400 px-2 py-0.5 rounded-full";
        });

        // 3. Set CLICKED Button to Active State
        btnElement.className = "category-btn w-full flex items-center justify-between px-4 py-3 bg-red-50 text-red-700 rounded-lg transition-colors cursor-pointer group";
        
        const activeIcon = btnElement.querySelector('i');
        if(activeIcon) {
            activeIcon.classList.remove('text-gray-400', 'group-hover:text-gray-600'); 
        }

        const activeBadge = btnElement.querySelector('.badge');
        if(activeBadge) {
            activeBadge.className = "badge text-xs font-bold bg-white text-red-700 px-2 py-0.5 rounded-full shadow-sm";
        }
      }

        function clientSideSearch() {
            const input = document.getElementById('docSearch');
            const filter = input.value.toLowerCase();
            const items = document.querySelectorAll('.doc-item');

            items.forEach(item => {
                const title = item.querySelector('.doc-title').innerText.toLowerCase();
                if (title.indexOf(filter) > -1 && !item.classList.contains('hidden-by-category')) {
                    item.style.display = "";
                } else {
                    item.style.display = "none";
                }
            });
        }

        function openUploadModal() {
            document.getElementById('uploadModal').classList.remove('hidden');
        }

        function closeUploadModal() {
            document.getElementById('uploadModal').classList.add('hidden');
        }

        function showFileName(input) {
            const display = document.getElementById('file-name-display');
            if (input.files && input.files[0]) {
                display.innerText = "Selected: " + input.files[0].name;
            } else {
                display.innerText = "";
            }
        }
    </script>

</x-layout>