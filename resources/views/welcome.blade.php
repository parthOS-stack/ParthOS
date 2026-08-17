@extends('layouts.app')

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <!-- Column 1: Task Progress & Reminders -->
    <div class="flex flex-col gap-6 col-span-1">
        <!-- Task Progress -->
        <div class="dp-card flex flex-col h-[280px]">
            <div class="flex justify-between items-start mb-2 gap-2 flex-wrap">
                <div>
                    <h2 class="text-[18px] font-bold text-[#1a1a24] mb-1">Task Progress</h2>
                    <p class="text-[13px] text-[var(--color-dp-text-muted)]">Weekly overview of completed tasks.</p>
                </div>
                <div class="flex gap-2">
                    <button class="flex items-center gap-1.5 bg-gray-100 text-gray-700 px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-gray-200 transition-colors">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                            <line x1="16" y1="2" x2="16" y2="6" />
                            <line x1="8" y1="2" x2="8" y2="6" />
                            <line x1="3" y1="10" x2="21" y2="10" />
                        </svg>
                        THIS WEEK
                    </button>
                    <div class="flex items-center gap-1 bg-[#fff6df] text-[#d99f16] px-3 py-1.5 rounded-lg text-xs font-bold">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="22 7 13.5 15.5 8.5 10.5 2 17" />
                            <polyline points="16 7 22 7 22 13" />
                        </svg>
                        +12%
                    </div>
                </div>
            </div>
            
            <div class="mt-auto">
                <div class="flex items-end gap-1 mb-1">
                    <span class="text-[52px] font-extrabold leading-none tracking-tight text-[#1a1a24]">24</span>
                    <span class="text-xl font-bold text-gray-400 mb-2">/32</span>
                </div>
                <p class="text-sm text-gray-500 font-medium mb-6">Tasks completed</p>
                
                <!-- Progress Bar Container -->
                <div class="relative pt-1">
                    <div class="flex h-2 mb-4 overflow-hidden rounded-full bg-gray-100">
                        <div style="width: 75%" class="flex flex-col justify-center overflow-hidden bg-gray-200"></div>
                    </div>
                    <div class="absolute top-0 w-full h-1 bg-gray-100"></div>
                    <div class="absolute top-0 h-1 bg-[#1a1a24]" style="width: 75%"></div>
                    <div class="absolute top-4 w-full h-px bg-gray-100"></div>
                </div>
            </div>
        </div>

        <!-- Reminders -->
        <div class="dp-card flex flex-col flex-1 h-[400px]">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-[18px] font-bold text-[#1a1a24]">Reminders</h2>
                <button class="text-xs font-bold text-[var(--color-dp-primary)] hover:underline">SEE ALL</button>
            </div>
            
            <div class="flex flex-col gap-4 flex-1">
                <!-- Active Reminder -->
                <div class="bg-[var(--color-dp-primary)] text-white p-4 rounded-[20px] flex items-center justify-between shadow-sm relative overflow-hidden">
                    <div class="absolute right-0 top-0 w-24 h-24 bg-white opacity-5 rounded-full blur-xl transform translate-x-1/2 -translate-y-1/2"></div>
                    
                    <div class="flex items-center gap-3 relative z-10">
                        <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="font-semibold text-[15px]">Update</span>
                            <span class="font-semibold text-[15px]">Devparth UI</span>
                        </div>
                    </div>
                    <button class="text-white/80 hover:text-white p-1 relative z-10">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="1" />
                            <circle cx="19" cy="12" r="1" />
                            <circle cx="5" cy="12" r="1" />
                        </svg>
                    </button>
                </div>

                <!-- Inactive Reminder -->
                <div class="bg-gray-50 p-4 rounded-[20px] flex items-center justify-between border border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full border border-gray-200 flex items-center justify-center text-gray-500 bg-white">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="font-semibold text-[#1a1a24] text-[15px]">Team sync at</span>
                            <span class="font-semibold text-gray-500 text-[15px]">4 PM</span>
                        </div>
                    </div>
                    <button class="text-gray-400 hover:text-gray-600 p-1">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="1" />
                            <circle cx="19" cy="12" r="1" />
                            <circle cx="5" cy="12" r="1" />
                        </svg>
                    </button>
                </div>

                <!-- Add Reminder Button -->
                <button class="mt-auto border-2 border-dashed border-gray-200 rounded-[20px] p-4 flex items-center justify-center gap-2 text-gray-500 font-semibold hover:bg-gray-50 transition-colors">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    Add Reminder
                </button>
            </div>
        </div>
    </div>

    <!-- Column 2: Team Velocity -->
    <div class="col-span-1">
        <div class="dp-card h-full flex flex-col relative overflow-hidden">
            <div class="relative z-10">
                <h2 class="text-[18px] font-bold text-[#1a1a24] mb-1">Team Velocity</h2>
                <p class="text-[13px] text-[var(--color-dp-text-muted)] w-2/3">Sprint completion rate is above average.</p>
            </div>
            
            <div class="flex-1 flex items-center justify-center relative z-10">
                <div class="text-[64px] font-extrabold text-[var(--color-dp-primary)] tracking-tight flex items-center gap-2 drop-shadow-sm">
                    94% 
                    <span class="text-4xl">🔥</span>
                </div>
            </div>
            
            <!-- Subtle Watermark -->
            <div class="absolute -bottom-4 right-0 text-[160px] font-black text-gray-50 leading-none select-none z-0">
                #
            </div>

            <div class="flex flex-wrap gap-2 mt-auto relative z-10 pb-4">
                <span class="bg-gray-100 text-gray-600 px-4 py-1.5 rounded-full text-[13px] font-semibold">Design</span>
                <span class="bg-gray-100 text-gray-600 px-4 py-1.5 rounded-full text-[13px] font-semibold">Frontend</span>
                <span class="bg-[var(--color-dp-primary-light)] text-[var(--color-dp-primary)] px-4 py-1.5 rounded-full text-[13px] font-semibold">Backend</span>
            </div>
        </div>
    </div>

    <!-- Column 3: Project Overview & Today's Schedule -->
    <div class="flex flex-col gap-6 col-span-1">
        <!-- Project Overview -->
        <div class="dp-card flex flex-col h-[280px]">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-[18px] font-bold text-[#1a1a24]">Project Overview</h2>
                <button class="text-gray-400 hover:text-gray-600">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="1" />
                        <circle cx="12" cy="5" r="1" />
                        <circle cx="12" cy="19" r="1" />
                    </svg>
                </button>
            </div>
            
            <div class="flex-1 flex items-center justify-center relative mb-4">
                <!-- SVG Donut Chart -->
                <div class="relative w-40 h-40">
                    <svg viewBox="0 0 100 100" class="w-full h-full transform -rotate-90">
                        <!-- Background Ring (Not Started) - Yellow -->
                        <circle cx="50" cy="50" r="40" fill="transparent" stroke="var(--color-dp-yellow)" stroke-width="12" />
                        
                        <!-- In Progress Ring - Light Gray -->
                        <circle cx="50" cy="50" r="40" fill="transparent" stroke="#e4e4e7" stroke-width="12" stroke-dasharray="251.2" stroke-dashoffset="62.8" />
                        
                        <!-- Completed Ring - Primary Purple -->
                        <circle cx="50" cy="50" r="40" fill="transparent" stroke="var(--color-dp-primary)" stroke-width="12" stroke-dasharray="251.2" stroke-dashoffset="100.48" class="transition-all duration-1000 ease-out" />
                    </svg>
                    <!-- Inner Text -->
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-2xl font-bold text-[#1a1a24]">60%</span>
                        <span class="text-[11px] font-bold text-gray-400">DONE</span>
                    </div>
                </div>
            </div>
            
            <!-- Legend -->
            <div class="flex justify-between items-center px-2 mt-auto">
                <div class="flex items-center gap-1.5">
                    <div class="w-2.5 h-2.5 rounded-full bg-[var(--color-dp-primary)]"></div>
                    <span class="text-xs text-gray-500 font-medium">Completed</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <div class="w-2.5 h-2.5 rounded-full bg-gray-300"></div>
                    <span class="text-xs text-gray-500 font-medium leading-tight">In<br/>Progress</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <div class="w-2.5 h-2.5 rounded-full bg-[var(--color-dp-yellow)]"></div>
                    <span class="text-xs text-gray-500 font-medium leading-tight">Not<br/>Started</span>
                </div>
            </div>
        </div>

        <!-- Today's Schedule -->
        <div class="dp-card flex flex-col flex-1 h-[400px]">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h2 class="text-[18px] font-bold text-[#1a1a24] mb-1">Today's Schedule</h2>
                    <p class="text-[12px] font-medium text-[var(--color-dp-text-muted)]">Thursday, 12.05.24</p>
                </div>
                <div class="flex gap-2">
                    <button class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded-lg">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                            <line x1="16" y1="2" x2="16" y2="6" />
                            <line x1="8" y1="2" x2="8" y2="6" />
                            <line x1="3" y1="10" x2="21" y2="10" />
                        </svg>
                    </button>
                    <button class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded-lg border border-gray-100">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19" />
                            <line x1="5" y1="12" x2="19" y2="12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Tabs -->
            <div class="flex gap-2 mb-4">
                <button class="bg-[#1a1a24] text-white px-4 py-2 rounded-full text-[13px] font-semibold">
                    All Meetings
                </button>
                <button class="bg-gray-100 text-gray-600 px-4 py-2 rounded-full text-[13px] font-semibold hover:bg-gray-200">
                    Developer team
                </button>
            </div>

            <!-- Meeting Cards -->
            <div class="flex gap-3 h-full overflow-hidden">
                <!-- Card 1 -->
                <div class="flex-1 bg-gradient-to-b from-[#6b52dc] to-[#5034c4] text-white rounded-[20px] p-4 flex flex-col relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-white opacity-5 rounded-full blur-xl transform translate-x-1/2 -translate-y-1/2"></div>
                    
                    <div class="flex items-start gap-2 mb-2 relative z-10">
                        <img src="https://api.dicebear.com/7.x/notionists/svg?seed=hr" class="w-8 h-8 rounded-full bg-white/20" alt="HR" />
                        <div>
                            <h3 class="font-bold text-sm leading-tight mb-1">HRD<br/>Meeting</h3>
                            <span class="text-[9px] font-bold tracking-wider text-white/80 uppercase">HIGH PRIORITY</span>
                        </div>
                    </div>
                    
                    <div class="mt-auto relative z-10">
                        <div class="text-[22px] font-bold leading-none mb-1">10:00<br/>AM</div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-white/80 font-medium">Full Review</span>
                            <div class="flex -space-x-2">
                                <img src="https://api.dicebear.com/7.x/notionists/svg?seed=a" class="w-6 h-6 rounded-full border-2 border-[#5c41c9] bg-yellow-100" />
                                <img src="https://api.dicebear.com/7.x/notionists/svg?seed=b" class="w-6 h-6 rounded-full border-2 border-[#5c41c9] bg-blue-100" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="flex-1 bg-gray-50 rounded-[20px] p-4 flex flex-col border border-gray-100">
                    <div class="flex items-start gap-2 mb-2">
                        <img src="https://api.dicebear.com/7.x/notionists/svg?seed=design" class="w-8 h-8 rounded-full bg-gray-200" alt="Design" />
                        <div>
                            <h3 class="font-bold text-sm leading-tight mb-1 text-[#1a1a24]">Design<br/>Review</h3>
                            <span class="text-[9px] font-bold tracking-wider text-gray-500 uppercase">DEV TEAM</span>
                        </div>
                    </div>
                    
                    <div class="mt-auto">
                        <div class="text-[22px] font-bold text-[#1a1a24] leading-none mb-1">2:30<br/>PM</div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500 font-medium">Sync Up</span>
                            <div class="flex -space-x-2">
                                <img src="https://api.dicebear.com/7.x/notionists/svg?seed=c" class="w-6 h-6 rounded-full border-2 border-white bg-green-100" />
                                <img src="https://api.dicebear.com/7.x/notionists/svg?seed=d" class="w-6 h-6 rounded-full border-2 border-white bg-pink-100" />
                                <img src="https://api.dicebear.com/7.x/notionists/svg?seed=e" class="w-6 h-6 rounded-full border-2 border-white bg-indigo-100" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
