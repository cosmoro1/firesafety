<x-layout>
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Site Audit</h2>
            <p class="text-gray-500 text-sm">Barangay-level fire safety inspections and compliance monitoring</p>
        </div>

        <div class="flex gap-2">
            {{-- IMPORT BUTTON --}}
            <button onclick="openImportModal()" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg font-medium shadow-sm transition flex items-center">
                <i class="fa-solid fa-file-import mr-2"></i> Import CSV
            </button>

            {{-- NEW AUDIT BUTTON --}}
            <button onclick="openModal()" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-lg font-medium shadow-sm transition flex items-center">
                <i class="fa-solid fa-plus mr-2"></i> New Audit
            </button>
        </div>
    </div>

    {{-- STATS GRID --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex items-center">
            <div class="h-12 w-12 rounded-lg bg-green-50 flex items-center justify-center text-green-600 mr-4"><i class="fa-solid fa-shield-check text-xl"></i></div>
            <div><p class="text-xs text-gray-500 font-medium">Low Risk</p><h3 class="text-2xl font-bold text-gray-900">{{ \App\Models\SiteAudit::where('risk_level', 'Low')->count() }}</h3></div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex items-center">
            <div class="h-12 w-12 rounded-lg bg-red-50 flex items-center justify-center text-red-600 mr-4"><i class="fa-solid fa-circle-exclamation text-xl"></i></div>
            <div><p class="text-xs text-gray-500 font-medium">High Risk</p><h3 class="text-2xl font-bold text-gray-900">{{ \App\Models\SiteAudit::where('risk_level', 'High')->count() }}</h3></div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex items-center">
            <div class="h-12 w-12 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 mr-4"><i class="fa-solid fa-building text-xl"></i></div>
            <div><p class="text-xs text-gray-500 font-medium">Total Audits</p><h3 class="text-2xl font-bold text-gray-900">{{ \App\Models\SiteAudit::count() }}</h3></div>
        </div>
    </div>

    {{-- TABLE SECTION --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
        <div class="p-5 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="w-full md:w-1/2 relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search by audit ID, barangay, or owner..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500">
            </div>
            
            <div class="flex items-center gap-3 w-full md:w-auto">
                <select onchange="window.location.href=this.value" class="px-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-red-500 bg-white text-gray-600 w-full md:w-auto cursor-pointer">
                    <option value="{{ route('site_audit.index', ['risk' => 'all']) }}" {{ request('risk') == 'all' ? 'selected' : '' }}>All Risk Levels</option>
                    <option value="{{ route('site_audit.index', ['risk' => 'High']) }}" {{ request('risk') == 'High' ? 'selected' : '' }}>High Risk</option>
                    <option value="{{ route('site_audit.index', ['risk' => 'Low']) }}" {{ request('risk') == 'Low' ? 'selected' : '' }}>Low Risk</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-xs uppercase text-gray-500 font-semibold">
                        <th class="px-6 py-4">Audit ID</th>
                        <th class="px-6 py-4">Barangay</th>
                        <th class="px-6 py-4">Audit Date</th>
                        <th class="px-6 py-4">Owner / Establishment</th>
                        <th class="px-6 py-4">Inspector</th> 
                        <th class="px-6 py-4">Compliance</th>
                        <th class="px-6 py-4">Risk Level</th>
                        <th class="px-6 py-4">Hazards</th>
                        <th class="px-6 py-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="auditTableBody" class="divide-y divide-gray-100 text-sm">
                    @forelse($audits as $audit)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-medium text-gray-900">AUD-{{ $audit->id }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $audit->barangay }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $audit->created_at->format('Y-m-d') }}</td>
                            <td class="px-6 py-4 text-gray-900 font-medium">{{ $audit->owner_name }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $audit->auditor->name ?? 'System Admin' }}</td>
                            <td class="px-6 py-4 font-bold {{ $audit->compliance_score >= 65 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $audit->compliance_score }}%
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $riskColor = match($audit->risk_level) {
                                        'Low' => 'bg-green-100 text-green-600',
                                        'High' => 'bg-red-100 text-red-600',
                                        default => 'bg-gray-100 text-gray-600',
                                    };
                                    $explanation = $audit->remarks ?? ($audit->risk_level == 'High' 
                                        ? 'Critical safety violations detected.' 
                                        : 'Excellent compliance.');
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $riskColor }}">
                                    {{ $audit->risk_level }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600 truncate max-w-xs">
                                {{ Str::limit($audit->hazards, 30) ?: 'None' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button onclick="openViewModal(
                                    '{{ $audit->id }}',
                                    '{{ addslashes($audit->barangay) }}',
                                    '{{ $audit->created_at->format('Y-m-d') }}',
                                    '{{ addslashes($audit->owner_name) }}',
                                    '{{ addslashes($audit->auditor->name ?? 'System Admin') }}',
                                    '{{ $audit->compliance_score }}',
                                    '{{ $audit->risk_level }}',
                                    '{{ addslashes(str_replace(["\r", "\n"], " ", $audit->hazards)) }}',
                                    '{{ addslashes($audit->address) }}',
                                    '{{ addslashes($audit->type) }}',
                                    '{{ addslashes($explanation) }}'
                                )" class="text-gray-400 hover:text-gray-600 transition transform hover:scale-110" title="View Audit Details">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-10 text-center text-gray-500">No audits found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-100">
            {{ $audits->links() }}
        </div>
    </div>

    {{-- IMPORT MODAL --}}
    <div id="importModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeImportModal()"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center">
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:w-full sm:max-w-lg">
                    <form action="{{ route('site_audit.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <i class="fa-solid fa-file-csv text-green-600"></i>
                                </div>
                                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                    <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">Import Audit Data</h3>
                                    <div class="mt-2">
                                      
                                        
                                        <label class="block mb-2 text-sm font-medium text-gray-900" for="file_input">Upload file</label>
                                        <input class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none" id="file_input" name="file" type="file" accept=".csv" required>
                                        
                                    
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="submit" class="inline-flex w-full justify-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 sm:ml-3 sm:w-auto">Import Data</button>
                            <button type="button" onclick="closeImportModal()" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- NEW AUDIT MODAL (FULL FORM) --}}
    <div id="newAuditModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeModal()"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl">
                    <form action="{{ route('site_audit.store') }}" method="POST">
                        @csrf 
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 border-b border-gray-100 flex justify-between items-center">
                            <h3 class="text-xl font-semibold leading-6 text-gray-900" id="modal-title">New Site Audit</h3>
                            <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-500"><i class="fa-solid fa-xmark text-xl"></i></button>
                        </div>

                        <div class="px-6 py-6 max-h-[70vh] overflow-y-auto">
                            
                            <h4 class="font-bold text-gray-800 mb-4">Basic Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Barangay <span class="text-red-500">*</span></label>
                                    <select name="barangay" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-red-500" required>
                                        <option value="">Select Barangay</option>
                                        <option value="Bagong Kalsada">Bagong Kalsada</option>
                                        <option value="Bañadero">Bañadero</option>
                                        <option value="Banlic">Banlic</option>
                                        <option value="Barandal">Barandal</option>
                                        <option value="Barangay 1 (Poblacion)">Barangay 1 (Poblacion)</option>
                                        <option value="Barangay 2 (Poblacion)">Barangay 2 (Poblacion)</option>
                                        <option value="Barangay 3 (Poblacion)">Barangay 3 (Poblacion)</option>
                                        <option value="Barangay 4 (Poblacion)">Barangay 4 (Poblacion)</option>
                                        <option value="Barangay 5 (Poblacion)">Barangay 5 (Poblacion)</option>
                                        <option value="Barangay 6 (Poblacion)">Barangay 6 (Poblacion)</option>
                                        <option value="Barangay 7 (Poblacion)">Barangay 7 (Poblacion)</option>
                                        <option value="Batino">Batino</option>
                                        <option value="Bubuyan">Bubuyan</option>
                                        <option value="Bucal">Bucal</option>
                                        <option value="Bunggo">Bunggo</option>
                                        <option value="Burol">Burol</option>
                                        <option value="Camaligan">Camaligan</option>
                                        <option value="Canlubang">Canlubang</option>
                                        <option value="Halang">Halang</option>
                                        <option value="Hornalan">Hornalan</option>
                                        <option value="Kay-Anlog">Kay-Anlog</option>
                                        <option value="La Mesa">La Mesa</option>
                                        <option value="Laguerta">Laguerta</option>
                                        <option value="Lawa">Lawa</option>
                                        <option value="Lecheria">Lecheria</option>
                                        <option value="Lingga">Lingga</option>
                                        <option value="Looc">Looc</option>
                                        <option value="Mabato">Mabato</option>
                                        <option value="Majada Labas">Majada Labas</option>
                                        <option value="Makiling">Makiling</option>
                                        <option value="Mapagong">Mapagong</option>
                                        <option value="Masili">Masili</option>
                                        <option value="Maunong">Maunong</option>
                                        <option value="Mayapa">Mayapa</option>
                                        <option value="Paciano Rizal">Paciano Rizal</option>
                                        <option value="Palingon">Palingon</option>
                                        <option value="Palo-Alto">Palo-Alto</option>
                                        <option value="Pansol">Pansol</option>
                                        <option value="Parian">Parian</option>
                                        <option value="Prinza">Prinza</option>
                                        <option value="Punta">Punta</option>
                                        <option value="Puting Lupa">Puting Lupa</option>
                                        <option value="Real">Real</option>
                                        <option value="Saimsim">Saimsim</option>
                                        <option value="Sampiruhan">Sampiruhan</option>
                                        <option value="San Cristobal">San Cristobal</option>
                                        <option value="San Jose">San Jose</option>
                                        <option value="San Juan">San Juan</option>
                                        <option value="Sirang Lupa">Sirang Lupa</option>
                                        <option value="Sucol">Sucol</option>
                                        <option value="Tulo">Tulo</option>
                                        <option value="Turbina">Turbina</option>
                                        <option value="Ulango">Ulango</option>
                                        <option value="Uwisan">Uwisan</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Owner Name / Establishment <span class="text-red-500">*</span></label>
                                    <input type="text" name="owner_name" placeholder="Enter owner name" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-red-500" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Type <span class="text-red-500">*</span></label>
                                    <select name="type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-red-500" required>
                                        <option value="Residential">Residential</option>
                                        <option value="Commercial">Commercial</option>
                                        <option value="Industrial">Industrial</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Address <span class="text-red-500">*</span></label>
                                    <input type="text" name="address" placeholder="Enter complete address" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-red-500" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact Person</label>
                                    <input type="text" name="contact_person" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-red-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                                    <input type="text" name="contact_number" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-red-500">
                                </div>
                            </div>

                            <hr class="border-gray-100 my-6">

                            <h4 class="font-bold text-gray-800 mb-4">Structural Composition</h4>
                            <div class="mb-6 overflow-x-auto">
                                <p class="text-sm text-gray-600 mb-2">Ang mga pangunahing bahagi ng tahanan ay yari sa? (Select one per row)</p>
                                
                                <table class="w-full text-sm text-left border border-gray-200">
                                    <thead class="bg-gray-50 text-gray-700 font-semibold">
                                        <tr>
                                            <th class="p-2 border border-gray-200 w-1/4">BAHAGI</th>
                                            <th class="p-2 border border-gray-200 text-center w-1/4">KAHOY</th> 
                                            <th class="p-2 border border-gray-200 text-center w-1/4">SEMENTO</th> 
                                            <th class="p-2 border border-gray-200 text-center w-1/4">BAKAL</th> 
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $structures = ['ROOF', 'CEILING', 'ROOM PARTITIONS', 'TRUSSES', 'WINDOWS', 'CORRIDOR WALLS', 'COLUMNS', 'MAIN DOOR', 'EXTERIOR WALL', 'BEAMS'];
                                        @endphp
                                        @foreach($structures as $part)
                                        <tr class="hover:bg-gray-50">
                                            <td class="p-2 border border-gray-200 font-medium text-gray-800">{{ $part }}</td>
                                            <td class="p-2 border border-gray-200 text-center">
                                                <input type="radio" name="struct[{{$part}}][material]" value="wood" class="text-red-600 focus:ring-red-500 h-4 w-4">
                                            </td>
                                            <td class="p-2 border border-gray-200 text-center">
                                                <input type="radio" name="struct[{{$part}}][material]" value="cement" class="text-red-600 focus:ring-red-500 h-4 w-4">
                                            </td>
                                            <td class="p-2 border border-gray-200 text-center">
                                                <input type="radio" name="struct[{{$part}}][material]" value="metal" class="text-red-600 focus:ring-red-500 h-4 w-4">
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <hr class="border-gray-100 my-6">

                            <h4 class="font-bold text-gray-800 mb-4">Fire Safety Audit Checklist</h4>
                            
                            {{-- Checklist Sections (A) --}}
                            <div class="mb-6">
                                <h5 class="font-semibold text-gray-900 mb-3 bg-gray-50 p-2 rounded">A. KAAYUSAN SA BAHAY</h5>
                                <div class="space-y-3">
                                    @php
                                        $sectionA = [
                                            1 => 'Maayos at malinis',
                                            2 => 'Nakatago ang mga flammable na bagay',
                                            3 => 'Walang anumang kalat malapit sa mga saksakan',
                                            4 => 'Nasa tamang ayos ang bahay',
                                            5 => 'Pag-tapon ng mga maaring pagmulan ng sunog tulad ng mga basura',
                                            6 => 'Nakaligpit ang mga gamit ng maayos',
                                            7 => 'May nakaka-alam ng "Stop, Drop and Cover, Roll"',
                                            8 => 'Mayroong Evacuation Plan ang tahanan',
                                            9 => 'May naninigarilyo'
                                        ];
                                    @endphp
                                    @foreach($sectionA as $index => $item)
                                    <div class="flex items-center justify-between text-sm py-1 border-b border-gray-50">
                                        <span class="text-gray-700">{{ $index }}. {{ $item }}</span>
                                        <div class="flex gap-4">
                                            <label class="flex items-center gap-1">
                                                <input type="radio" name="checklist[{{$index}}]" value="Yes" class="text-red-600 focus:ring-red-500"> Yes
                                            </label>
                                            <label class="flex items-center gap-1">
                                                <input type="radio" name="checklist[{{$index}}]" value="No" class="text-red-600 focus:ring-red-500"> No
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Checklist Sections (B) --}}
                            <div class="mb-6">
                                <h5 class="font-semibold text-gray-900 mb-3 bg-gray-50 p-2 rounded">B. KONEKSYONG ELEKTRIKAL</h5>
                                <div class="space-y-3">
                                    @php
                                        $sectionB = [
                                            10 => 'May Circuit Breaker',
                                            11 => 'Nakatakip ang lahat ng electrical panels, junction boxes, outlets at switches ng maayos',
                                            12 => 'Maayos na paggamit ng extension cords',
                                            13 => 'Hindi pagsasaksak ng maraming appliances sa iisang outlet',
                                            14 => 'Walang nakausling Electrical cords',
                                            15 => 'Maayos ang mga saksakan at switch ng ilaw',
                                            16 => 'Ang mga appliances ay direktang nakasaksak sa saksakan',
                                            17 => 'Ang mga kable na gamit ay naayon sa tamang sukat nito',
                                            18 => 'Nasa tamang sukat ang mga kable',
                                            19 => 'Pag tanggal ng mga nakasaksak kapag hindi ginagamit',
                                            20 => 'May electrical safety switch'
                                        ];
                                    @endphp
                                    @foreach($sectionB as $index => $item)
                                    <div class="flex items-center justify-between text-sm py-1 border-b border-gray-50">
                                        <span class="text-gray-700">{{ $index }}. {{ $item }}</span>
                                        <div class="flex gap-4">
                                            <label class="flex items-center gap-1">
                                                <input type="radio" name="checklist[{{$index}}]" value="Yes" class="text-red-600 focus:ring-red-500"> Yes
                                            </label>
                                            <label class="flex items-center gap-1">
                                                <input type="radio" name="checklist[{{$index}}]" value="No" class="text-red-600 focus:ring-red-500"> No
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Checklist Sections (C) --}}
                            <div class="mb-6">
                                <h5 class="font-semibold text-gray-900 mb-3 bg-gray-50 p-2 rounded">C. KAAYUSAN SA KUSINA</h5>
                                <div class="space-y-3">
                                    @php
                                        $sectionC = [
                                            21 => 'Binabantayan ang lutuin sa kusina',
                                            22 => 'Nasa tamang lalagyan ang LPG',
                                            23 => 'Laging nakapatay ang LPG matapos gamitin',
                                            24 => 'Walang tumatagas na tubig sa kusina',
                                            25 => 'Walang anumang bagay sa kusina na maaring magliyab',
                                            26 => 'Kaugaliang pagiinspeksyon ng mga kagamitan sa kusina',
                                            27 => 'May sapat na singawan ng usok sa kusina',
                                            28 => 'Nasa tamang lalagyan ang mga kandila at lighter'
                                        ];
                                    @endphp
                                    @foreach($sectionC as $index => $item)
                                    <div class="flex items-center justify-between text-sm py-1 border-b border-gray-50">
                                        <span class="text-gray-700">{{ $index }}. {{ $item }}</span>
                                        <div class="flex gap-4">
                                            <label class="flex items-center gap-1">
                                                <input type="radio" name="checklist[{{$index}}]" value="Yes" class="text-red-600 focus:ring-red-500"> Yes
                                            </label>
                                            <label class="flex items-center gap-1">
                                                <input type="radio" name="checklist[{{$index}}]" value="No" class="text-red-600 focus:ring-red-500"> No
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Checklist Sections (D) --}}
                            <div class="mb-6">
                                <h5 class="font-semibold text-gray-900 mb-3 bg-gray-50 p-2 rounded">D. DAANAN O LABASAN SA BAHAY</h5>
                                <div class="space-y-3">
                                    @php
                                        $sectionD = [
                                            29 => 'Walang anumang kalat sa pintuan at bintana',
                                            30 => 'Walang mga naipong tuyong dahon sa paligid ng bahay',
                                            31 => 'Madaling makalakabas ng bahay kapag may sunog',
                                            32 => 'Malapit sa daanan papunta sa kalsada',
                                            33 => 'Maayos ang daanan sa loob ng bahay',
                                            34 => 'May sapat na liwanag sa loob ng tahanan'
                                        ];
                                    @endphp
                                    @foreach($sectionD as $index => $item)
                                    <div class="flex items-center justify-between text-sm py-1 border-b border-gray-50">
                                        <span class="text-gray-700">{{ $index }}. {{ $item }}</span>
                                        <div class="flex gap-4">
                                            <label class="flex items-center gap-1">
                                                <input type="radio" name="checklist[{{$index}}]" value="Yes" class="text-red-600 focus:ring-red-500"> Yes
                                            </label>
                                            <label class="flex items-center gap-1">
                                                <input type="radio" name="checklist[{{$index}}]" value="No" class="text-red-600 focus:ring-red-500"> No
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <hr class="border-gray-100 my-6">

                            <h4 class="font-bold text-gray-800 mb-4">Additional Information</h4>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Identified Hazards</label>
                                <textarea name="hazards" rows="3" placeholder="Describe any fire hazards identified during inspection..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-red-500"></textarea>
                            </div>

                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="submit" class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-50 sm:ml-3 sm:w-auto">Save Audit</button>
                            <button type="button" onclick="closeModal()" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- VIEW AUDIT MODAL --}}
    <div id="viewAuditModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeViewModal()"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:w-full sm:max-w-2xl">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-900">Audit Summary</h3>
                        <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-500"><i class="fa-solid fa-xmark text-xl"></i></button>
                    </div>
                    <div class="px-6 py-6 space-y-6">
                        <div class="flex items-center justify-between bg-gray-50 p-4 rounded-lg border border-gray-100">
                            <div><p class="text-sm text-gray-500">Audit ID</p><p id="view_id" class="text-xl font-bold text-gray-900">AUD-000</p></div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500">Compliance Score</p>
                                <div class="flex items-center gap-2 justify-end"><span id="view_score" class="text-2xl font-bold text-gray-900">0%</span><div id="view_risk_badge"></div></div>
                            </div>
                        </div>
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-100"><p class="text-xs text-blue-600 uppercase font-bold mb-1">Score Analysis</p><p id="view_remarks" class="text-sm text-blue-800 leading-relaxed"></p></div>
                        <div class="grid grid-cols-2 gap-4">
                            <div><p class="text-xs text-gray-500 uppercase font-semibold">Location Details</p><p id="view_owner" class="font-medium text-gray-900 mt-1"></p><p id="view_barangay" class="text-sm text-gray-600"></p><p id="view_address" class="text-sm text-gray-600"></p></div>
                            <div><p class="text-xs text-gray-500 uppercase font-semibold">Inspection Info</p><p id="view_type" class="font-medium text-gray-900 mt-1"></p><p class="text-sm text-gray-600">Date: <span id="view_date"></span></p><p class="text-sm text-gray-600">Inspector: <span id="view_auditor"></span></p></div>
                        </div>
                        <div><p class="text-xs text-gray-500 uppercase font-semibold mb-2">Identified Hazards / Notes</p><div id="view_hazards" class="bg-red-50 p-3 rounded-md text-sm text-red-800 border border-red-100 italic">No hazards recorded.</div></div>
                    </div>
                    <div class="bg-gray-50 px-6 py-3 flex flex-row-reverse"><button onclick="closeViewModal()" class="w-full inline-flex justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:w-auto">Close</button></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openModal() { document.getElementById('newAuditModal').classList.remove('hidden'); }
        function closeModal() { document.getElementById('newAuditModal').classList.add('hidden'); }
        
        function openImportModal() { document.getElementById('importModal').classList.remove('hidden'); }
        function closeImportModal() { document.getElementById('importModal').classList.add('hidden'); }

        function openViewModal(id, barangay, date, owner, auditor, score, risk, hazards, address, type, remarks) {
            document.getElementById('view_id').innerText = "AUD-" + id;
            document.getElementById('view_barangay').innerText = barangay;
            document.getElementById('view_date').innerText = date;
            document.getElementById('view_owner').innerText = owner;
            document.getElementById('view_auditor').innerText = auditor;
            document.getElementById('view_score').innerText = score + "%";
            document.getElementById('view_address').innerText = address;
            document.getElementById('view_type').innerText = type;

            let cleanRemarks = remarks || "No specific details available.";
            cleanRemarks = cleanRemarks.replace(/^[A-Za-z]+ Risk: Final score \d+%\.\s*/, '');
            document.getElementById('view_remarks').innerText = cleanRemarks;

            const hazardBox = document.getElementById('view_hazards');
            if(hazards && hazards !== 'null' && hazards !== '') {
                hazardBox.innerText = hazards;
                hazardBox.className = "bg-red-50 p-3 rounded-md text-sm text-red-800 border border-red-100";
            } else {
                hazardBox.innerText = "No specific hazards recorded.";
                hazardBox.className = "bg-green-50 p-3 rounded-md text-sm text-green-800 border border-green-100 italic";
            }

            let badgeClass = "bg-gray-100 text-gray-600";
            if(risk === 'Low') badgeClass = "bg-green-100 text-green-600";
            if(risk === 'High') badgeClass = "bg-red-100 text-red-600";
            
            document.getElementById('view_risk_badge').innerHTML = 
                `<span class="px-2.5 py-0.5 rounded-full text-sm font-semibold ${badgeClass}">${risk}</span>`;

            document.getElementById('viewAuditModal').classList.remove('hidden');
        }

        function closeViewModal() { document.getElementById('viewAuditModal').classList.add('hidden'); }
        
        function searchTable() {
            let input = document.getElementById('searchInput').value.toLowerCase();
            let rows = document.querySelectorAll('#auditTableBody tr');
            rows.forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(input) ? '' : 'none';
            });
        }
    </script>
</x-layout>