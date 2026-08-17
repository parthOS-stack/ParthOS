@extends('layouts.app')

@section('header_title', 'Settings')
@section('header_subtitle', 'Manage your account and preferences.')

@section('content')

    {{-- Settings Tabs --}}
    <div class="mb-6">
        <div class="flex gap-1 border-b border-[var(--color-dp-border)]">
            <a href="{{ route('settings.profile') }}"
                class="settings-tab active px-5 py-3 text-[14px] font-semibold border-b-2 border-[var(--color-dp-primary)] text-[var(--color-dp-primary)] -mb-px transition-all">
                Profile Settings
            </a>
            <a href="{{ route('settings.admin') }}"
                class="settings-tab px-5 py-3 text-[14px] font-semibold border-b-2 border-transparent text-[var(--color-dp-text-muted)] hover:text-[#1a1a24] -mb-px transition-all">
                Admin Settings
            </a>
            <a href="{{ route('settings.notifications') }}"
                class="settings-tab px-5 py-3 text-[14px] font-semibold border-b-2 border-transparent text-[var(--color-dp-text-muted)] hover:text-[#1a1a24] -mb-px transition-all">
                Notification Settings
            </a>
            <a href="{{ route('settings.security') }}"
                class="settings-tab px-5 py-3 text-[14px] font-semibold border-b-2 border-transparent text-[var(--color-dp-text-muted)] hover:text-[#1a1a24] -mb-px transition-all">
                Security Locker
            </a>
        </div>
    </div>

    {{-- Success Alert --}}
    @if (session('success'))
        <div
            class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 px-5 py-3.5 rounded-2xl text-[14px] font-medium">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Two Column Profile Layout --}}
    <div class="flex flex-col lg:flex-row gap-6 items-start">
        {{-- Left Sidebar Profile Summary --}}
        <div class="lg:w-1/3 w-full shrink-0">
            <div class="dp-card p-8 flex flex-col items-center text-center">
                <div class="relative w-[120px] h-[120px] mb-5 group">
                    <div class="w-full h-full rounded-full overflow-hidden border border-gray-100 shadow-sm">
                        @if($admin->profile_photo)
                            <img src="{{ asset('storage/' . $admin->profile_photo) }}" alt="Profile" class="w-full h-full object-cover" id="avatar-preview-display">
                        @else
                            <img src="https://api.dicebear.com/7.x/notionists/svg?seed=devos" alt="Profile" class="w-full h-full object-cover bg-[#f3f1ff]" id="avatar-preview-display">
                        @endif
                    </div>
                    <button type="button" onclick="openUploadModal()" class="absolute bottom-1 right-1 w-9 h-9 bg-[var(--color-dp-primary)] text-white rounded-full flex items-center justify-center shadow-lg border-[3px] border-white hover:bg-[var(--color-dp-primary-hover)] transition-colors">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                    </button>
                </div>
                
                <h3 class="text-[20px] font-bold text-[#1a1a24] mb-1" id="display-name">{{ old('full_name', $admin->full_name ?? 'DevOS Admin') }}</h3>
                <p class="text-[14px] text-gray-500 mb-8" id="display-email">{{ old('email', $admin->email ?? 'admin@devos.local') }}</p>
                
                <div class="w-full pt-6 border-t border-[var(--color-dp-border)] flex flex-col gap-4">
                    <div class="flex justify-between items-center">
                        <span class="text-[13px] text-gray-500">Workspace Role</span>
                        <span class="text-[13px] font-bold text-[var(--color-dp-primary)]">Admin</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[13px] text-gray-500">Member Since</span>
                        <span class="text-[13px] font-medium text-[#1a1a24]">{{ \Carbon\Carbon::parse($admin->created_at)->format('M Y') ?? 'Nov 2023' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Main Content --}}
        <div class="lg:w-2/3 w-full">
            <div class="dp-card p-8 md:p-10">
                <form action="{{ route('settings.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <h3 class="text-[20px] font-bold text-[#1a1a24] mb-6">General Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
                        <div>
                            <label class="block text-[13px] font-medium text-gray-600 mb-2">Full Name <span class="text-[var(--color-dp-primary)]">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                </div>
                                <input type="text" name="full_name" value="{{ old('full_name', $admin->full_name ?? 'DevOS Admin') }}" onkeyup="document.getElementById('display-name').innerText = this.value || 'DevOS Admin'" class="w-full bg-white border border-gray-200 rounded-xl pl-11 pr-4 py-3 text-[14px] text-[#1a1a24] outline-none focus:border-[var(--color-dp-primary)] focus:ring-2 focus:ring-[var(--color-dp-primary)]/20 transition-all">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[13px] font-medium text-gray-600 mb-2">Email Address <span class="text-[var(--color-dp-primary)]">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c2 0 3 1 3 3v10c0 2-1 3-3 3H4c-2 0-3-1-3-3V7c0-2 1-3 3-3z"/><polyline points="22,6 12,13 2,6"/></svg>
                                </div>
                                <input type="email" name="email" value="{{ old('email', $admin->email ?? 'admin@devos.local') }}" onkeyup="document.getElementById('display-email').innerText = this.value || 'admin@devos.local'" class="w-full bg-white border border-gray-200 rounded-xl pl-11 pr-4 py-3 text-[14px] text-[#1a1a24] outline-none focus:border-[var(--color-dp-primary)] focus:ring-2 focus:ring-[var(--color-dp-primary)]/20 transition-all">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[13px] font-medium text-gray-600 mb-2">Phone Number</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                </div>
                                <input type="tel" name="phone" value="{{ old('phone', $admin->phone ?? '') }}" class="w-full bg-white border border-gray-200 rounded-xl pl-11 pr-4 py-3 text-[14px] text-[#1a1a24] outline-none focus:border-[var(--color-dp-primary)] focus:ring-2 focus:ring-[var(--color-dp-primary)]/20 transition-all">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[13px] font-medium text-gray-600 mb-2">Time Zone</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                </div>
                                <select name="timezone" class="w-full bg-white border border-gray-200 rounded-xl pl-11 pr-4 py-3 text-[14px] text-[#1a1a24] outline-none focus:border-[var(--color-dp-primary)] focus:ring-2 focus:ring-[var(--color-dp-primary)]/20 transition-all appearance-none cursor-pointer">
                                    @php
                                        $currentTz = old('timezone', $admin->timezone ?? 'Asia/Kolkata');
                                        $timezones = [
                                            // ── India (Default) ──
                                            'Asia/Kolkata'               => 'India Standard Time (IST) UTC+5:30',
                                            // ── Asia ──
                                            'Asia/Dubai'                 => 'Gulf Standard Time (GST) UTC+4',
                                            'Asia/Karachi'               => 'Pakistan Standard Time (PKT) UTC+5',
                                            'Asia/Dhaka'                 => 'Bangladesh Standard Time (BST) UTC+6',
                                            'Asia/Colombo'               => 'Sri Lanka Time (SLT) UTC+5:30',
                                            'Asia/Kathmandu'             => 'Nepal Time (NPT) UTC+5:45',
                                            'Asia/Kabul'                 => 'Afghanistan Time (AFT) UTC+4:30',
                                            'Asia/Tashkent'              => 'Uzbekistan Time (UZT) UTC+5',
                                            'Asia/Almaty'                => 'Almaty Time (ALMT) UTC+6',
                                            'Asia/Yangon'                => 'Myanmar Time (MMT) UTC+6:30',
                                            'Asia/Bangkok'               => 'Indochina Time (ICT) UTC+7',
                                            'Asia/Jakarta'               => 'Western Indonesia Time (WIB) UTC+7',
                                            'Asia/Singapore'             => 'Singapore Time (SGT) UTC+8',
                                            'Asia/Kuala_Lumpur'          => 'Malaysia Time (MYT) UTC+8',
                                            'Asia/Manila'                => 'Philippine Time (PST) UTC+8',
                                            'Asia/Shanghai'              => 'China Standard Time (CST) UTC+8',
                                            'Asia/Hong_Kong'             => 'Hong Kong Time (HKT) UTC+8',
                                            'Asia/Taipei'                => 'Taipei Standard Time (TST) UTC+8',
                                            'Asia/Seoul'                 => 'Korea Standard Time (KST) UTC+9',
                                            'Asia/Tokyo'                 => 'Japan Standard Time (JST) UTC+9',
                                            'Asia/Riyadh'                => 'Arabia Standard Time (AST) UTC+3',
                                            'Asia/Baghdad'               => 'Arabia Standard Time (AST) UTC+3',
                                            'Asia/Tehran'                => 'Iran Standard Time (IRST) UTC+3:30',
                                            'Asia/Baku'                  => 'Azerbaijan Time (AZT) UTC+4',
                                            'Asia/Tbilisi'               => 'Georgia Standard Time (GET) UTC+4',
                                            'Asia/Yerevan'               => 'Armenia Time (AMT) UTC+4',
                                            'Asia/Beirut'                => 'Eastern European Time (EET) UTC+2',
                                            'Asia/Jerusalem'             => 'Israel Standard Time (IST) UTC+2',
                                            'Asia/Amman'                 => 'Arabia Standard Time (AST) UTC+3',
                                            'Asia/Muscat'                => 'Gulf Standard Time (GST) UTC+4',
                                            'Asia/Bishkek'               => 'Kyrgyzstan Time (KGT) UTC+6',
                                            'Asia/Dushanbe'              => 'Tajikistan Time (TJT) UTC+5',
                                            'Asia/Ashgabat'              => 'Turkmenistan Time (TMT) UTC+5',
                                            'Asia/Phnom_Penh'            => 'Indochina Time (ICT) UTC+7',
                                            'Asia/Vientiane'             => 'Indochina Time (ICT) UTC+7',
                                            'Asia/Ulaanbaatar'           => 'Ulaanbaatar Time (ULAT) UTC+8',
                                            'Asia/Macau'                 => 'China Standard Time (CST) UTC+8',
                                            'Asia/Brunei'                => 'Brunei Darussalam Time (BNT) UTC+8',
                                            'Asia/Makassar'              => 'Central Indonesia Time (WITA) UTC+8',
                                            'Asia/Jayapura'              => 'Eastern Indonesia Time (WIT) UTC+9',
                                            'Asia/Vladivostok'           => 'Vladivostok Time (VLAT) UTC+10',
                                            'Asia/Sakhalin'              => 'Sakhalin Time (SAKT) UTC+11',
                                            'Asia/Kamchatka'             => 'Kamchatka Time (PETT) UTC+12',
                                            // ── Europe ──
                                            'Europe/London'              => 'Greenwich Mean Time (GMT) UTC+0',
                                            'Europe/Dublin'              => 'Irish Standard Time (IST) UTC+1',
                                            'Europe/Lisbon'              => 'Western European Time (WET) UTC+0',
                                            'Europe/Paris'               => 'Central European Time (CET) UTC+1',
                                            'Europe/Berlin'              => 'Central European Time (CET) UTC+1',
                                            'Europe/Rome'                => 'Central European Time (CET) UTC+1',
                                            'Europe/Madrid'              => 'Central European Time (CET) UTC+1',
                                            'Europe/Amsterdam'           => 'Central European Time (CET) UTC+1',
                                            'Europe/Brussels'            => 'Central European Time (CET) UTC+1',
                                            'Europe/Zurich'              => 'Central European Time (CET) UTC+1',
                                            'Europe/Vienna'              => 'Central European Time (CET) UTC+1',
                                            'Europe/Warsaw'              => 'Central European Time (CET) UTC+1',
                                            'Europe/Prague'              => 'Central European Time (CET) UTC+1',
                                            'Europe/Budapest'            => 'Central European Time (CET) UTC+1',
                                            'Europe/Stockholm'           => 'Central European Time (CET) UTC+1',
                                            'Europe/Oslo'                => 'Central European Time (CET) UTC+1',
                                            'Europe/Copenhagen'          => 'Central European Time (CET) UTC+1',
                                            'Europe/Helsinki'            => 'Eastern European Time (EET) UTC+2',
                                            'Europe/Athens'              => 'Eastern European Time (EET) UTC+2',
                                            'Europe/Bucharest'           => 'Eastern European Time (EET) UTC+2',
                                            'Europe/Sofia'               => 'Eastern European Time (EET) UTC+2',
                                            'Europe/Riga'                => 'Eastern European Time (EET) UTC+2',
                                            'Europe/Tallinn'             => 'Eastern European Time (EET) UTC+2',
                                            'Europe/Vilnius'             => 'Eastern European Time (EET) UTC+2',
                                            'Europe/Kiev'                => 'Eastern European Time (EET) UTC+2',
                                            'Europe/Minsk'               => 'Further-eastern European Time (FET) UTC+3',
                                            'Europe/Moscow'              => 'Moscow Standard Time (MSK) UTC+3',
                                            'Europe/Istanbul'            => 'Turkey Time (TRT) UTC+3',
                                            'Europe/Kaliningrad'         => 'Eastern European Time (EET) UTC+2',
                                            'Europe/Samara'              => 'Samara Time (SAMT) UTC+4',
                                            'Europe/Yekaterinburg'       => 'Yekaterinburg Time (YEKT) UTC+5',
                                            // ── Americas ──
                                            'America/New_York'           => 'Eastern Time (ET) UTC-5',
                                            'America/Chicago'            => 'Central Time (CT) UTC-6',
                                            'America/Denver'             => 'Mountain Time (MT) UTC-7',
                                            'America/Phoenix'            => 'Mountain Standard Time (MST) UTC-7',
                                            'America/Los_Angeles'        => 'Pacific Time (PT) UTC-8',
                                            'America/Anchorage'          => 'Alaska Time (AKT) UTC-9',
                                            'America/Adak'               => 'Hawaii-Aleutian Time (HAT) UTC-10',
                                            'Pacific/Honolulu'           => 'Hawaii Standard Time (HST) UTC-10',
                                            'America/Toronto'            => 'Eastern Time (ET) UTC-5',
                                            'America/Vancouver'          => 'Pacific Time (PT) UTC-8',
                                            'America/Winnipeg'           => 'Central Time (CT) UTC-6',
                                            'America/Edmonton'           => 'Mountain Time (MT) UTC-7',
                                            'America/Halifax'            => 'Atlantic Time (AT) UTC-4',
                                            'America/St_Johns'           => 'Newfoundland Time (NT) UTC-3:30',
                                            'America/Mexico_City'        => 'Central Time (CT) UTC-6',
                                            'America/Tijuana'            => 'Pacific Time (PT) UTC-8',
                                            'America/Cancun'             => 'Eastern Standard Time (EST) UTC-5',
                                            'America/Guatemala'          => 'Central Standard Time (CST) UTC-6',
                                            'America/Bogota'             => 'Colombia Time (COT) UTC-5',
                                            'America/Lima'               => 'Peru Time (PET) UTC-5',
                                            'America/Caracas'            => 'Venezuela Time (VET) UTC-4',
                                            'America/La_Paz'             => 'Bolivia Time (BOT) UTC-4',
                                            'America/Santiago'           => 'Chile Standard Time (CLT) UTC-4',
                                            'America/Buenos_Aires'       => 'Argentina Time (ART) UTC-3',
                                            'America/Sao_Paulo'          => 'Brasilia Time (BRT) UTC-3',
                                            'America/Manaus'             => 'Amazon Time (AMT) UTC-4',
                                            'America/Fortaleza'          => 'Brasilia Time (BRT) UTC-3',
                                            'America/Montevideo'         => 'Uruguay Time (UYT) UTC-3',
                                            'America/Asuncion'           => 'Paraguay Time (PYT) UTC-4',
                                            'America/Guayaquil'          => 'Ecuador Time (ECT) UTC-5',
                                            'America/Havana'             => 'Cuba Standard Time (CST) UTC-5',
                                            'America/Santo_Domingo'      => 'Atlantic Standard Time (AST) UTC-4',
                                            'America/Port-au-Prince'     => 'Eastern Time (ET) UTC-5',
                                            'America/Puerto_Rico'        => 'Atlantic Standard Time (AST) UTC-4',
                                            'America/Jamaica'            => 'Eastern Standard Time (EST) UTC-5',
                                            'America/Panama'             => 'Eastern Standard Time (EST) UTC-5',
                                            'America/Costa_Rica'         => 'Central Standard Time (CST) UTC-6',
                                            'America/El_Salvador'        => 'Central Standard Time (CST) UTC-6',
                                            'America/Tegucigalpa'        => 'Central Standard Time (CST) UTC-6',
                                            'America/Managua'            => 'Central Standard Time (CST) UTC-6',
                                            // ── Africa ──
                                            'Africa/Cairo'               => 'Eastern European Time (EET) UTC+2',
                                            'Africa/Johannesburg'        => 'South Africa Standard Time (SAST) UTC+2',
                                            'Africa/Nairobi'             => 'East Africa Time (EAT) UTC+3',
                                            'Africa/Lagos'               => 'West Africa Time (WAT) UTC+1',
                                            'Africa/Accra'               => 'Greenwich Mean Time (GMT) UTC+0',
                                            'Africa/Casablanca'          => 'Western European Time (WET) UTC+0',
                                            'Africa/Tunis'               => 'Central European Time (CET) UTC+1',
                                            'Africa/Algiers'             => 'Central European Time (CET) UTC+1',
                                            'Africa/Tripoli'             => 'Eastern European Time (EET) UTC+2',
                                            'Africa/Khartoum'            => 'Central Africa Time (CAT) UTC+3',
                                            'Africa/Addis_Ababa'         => 'East Africa Time (EAT) UTC+3',
                                            'Africa/Dar_es_Salaam'       => 'East Africa Time (EAT) UTC+3',
                                            'Africa/Kampala'             => 'East Africa Time (EAT) UTC+3',
                                            'Africa/Mogadishu'           => 'East Africa Time (EAT) UTC+3',
                                            'Africa/Kinshasa'            => 'West Africa Time (WAT) UTC+1',
                                            'Africa/Luanda'              => 'West Africa Time (WAT) UTC+1',
                                            'Africa/Abidjan'             => 'Greenwich Mean Time (GMT) UTC+0',
                                            'Africa/Dakar'               => 'Greenwich Mean Time (GMT) UTC+0',
                                            'Africa/Bamako'              => 'Greenwich Mean Time (GMT) UTC+0',
                                            'Africa/Maputo'              => 'Central Africa Time (CAT) UTC+2',
                                            'Africa/Harare'              => 'Central Africa Time (CAT) UTC+2',
                                            'Africa/Lusaka'              => 'Central Africa Time (CAT) UTC+2',
                                            'Africa/Windhoek'            => 'Central Africa Time (CAT) UTC+2',
                                            'Africa/Gaborone'            => 'Central Africa Time (CAT) UTC+2',
                                            // ── Pacific / Oceania ──
                                            'Australia/Sydney'           => 'Australian Eastern Time (AET) UTC+10',
                                            'Australia/Melbourne'        => 'Australian Eastern Time (AET) UTC+10',
                                            'Australia/Brisbane'         => 'Australian Eastern Time (AET) UTC+10',
                                            'Australia/Perth'            => 'Australian Western Time (AWT) UTC+8',
                                            'Australia/Adelaide'         => 'Australian Central Time (ACT) UTC+9:30',
                                            'Australia/Darwin'           => 'Australian Central Standard Time (ACST) UTC+9:30',
                                            'Australia/Hobart'           => 'Australian Eastern Time (AET) UTC+10',
                                            'Pacific/Auckland'           => 'New Zealand Standard Time (NZST) UTC+12',
                                            'Pacific/Fiji'               => 'Fiji Time (FJT) UTC+12',
                                            'Pacific/Guam'               => 'Chamorro Standard Time (ChST) UTC+10',
                                            'Pacific/Port_Moresby'       => 'Papua New Guinea Time (PGT) UTC+10',
                                            'Pacific/Noumea'             => 'New Caledonia Time (NCT) UTC+11',
                                            'Pacific/Tongatapu'          => 'Tonga Time (TOT) UTC+13',
                                            'Pacific/Apia'               => 'Samoa Standard Time (SST) UTC+13',
                                            // ── UTC ──
                                            'UTC'                        => 'Coordinated Universal Time (UTC) UTC+0',
                                        ];
                                    @endphp
                                    @foreach($timezones as $value => $label)
                                        <option value="{{ $value }}" {{ $currentTz === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-10">
                        <h3 class="text-[20px] font-bold text-[#1a1a24] mb-1">Workspace Role</h3>
                        <p class="text-[14px] text-gray-500 mb-5">Determine your levels of system access and permission settings.</p>
                        
                        <div class="grid grid-cols-1 gap-4 w-full md:w-1/2">
                            <div class="border-[2px] border-[var(--color-dp-primary)] rounded-2xl p-5 relative cursor-default transition-all shadow-[0_4px_12px_rgba(92,65,201,0.08)] bg-[var(--color-dp-primary)]/5">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-bold text-[#1a1a24] text-[15px]">Master Admin</span>
                                    <div class="w-5 h-5 rounded-full bg-[var(--color-dp-primary)] flex items-center justify-center">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    </div>
                                </div>
                                <p class="text-[13px] text-gray-500 leading-relaxed pr-2">Full administrative power to configure workspace, billing, integrations and global preferences.</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-8 border-t border-[var(--color-dp-border)]">
                        <a href="{{ route('settings.profile') }}" class="px-6 py-2.5 text-[14px] font-semibold text-gray-600 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">Cancel</a>
                        <button type="submit" class="dp-btn-primary px-7 py-2.5 text-[14px] font-bold rounded-xl shadow-[0_4px_14px_0_rgba(92,65,201,0.30)] hover:shadow-[0_6px_20px_rgba(92,65,201,0.40)] hover:-translate-y-0.5 transition-all duration-300">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Update avatar image instantly on successful upload
        document.addEventListener('upload-success', function(e) {
            if (e.detail && e.detail.url) {
                document.getElementById('avatar-preview-display').src = e.detail.url;
                
                // Also update header profile picture if it exists
                const headerProfile = document.querySelector('#profile-btn img');
                if (headerProfile) {
                    headerProfile.src = e.detail.url;
                }
            }
        });
    </script>

    {{-- Global Upload Component --}}
    <x-uploadimage-component upload-url="{{ route('settings.avatar.upload') }}" />

@endsection
