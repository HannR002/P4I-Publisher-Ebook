<div x-data="{
        theme: localStorage.getItem('theme') || 'system',
        isOpen: false,
        setTheme(val) {
            this.theme = val;
            if (val === 'system') {
                localStorage.removeItem('theme');
                if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            } else {
                localStorage.setItem('theme', val);
                if (val === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            }
            this.isOpen = false;
        }
    }"
    class="relative inline-block text-left"
    @click.outside="isOpen = false"
>
    <button @click="isOpen = !isOpen" type="button" class="flex items-center justify-center p-2 rounded-lg text-text-secondary hover:text-text-primary hover:bg-surface-muted transition-colors focus:outline-none focus:ring-2 focus:ring-focus-ring" aria-label="Theme options">
        <!-- Sun icon (Light) -->
        <svg x-show="theme === 'light'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
        <!-- Moon icon (Dark) -->
        <svg x-show="theme === 'dark'" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
        <!-- Computer icon (System) -->
        <svg x-show="theme === 'system'" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
    </button>

    <div x-show="isOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute right-0 mt-2 w-32 origin-top-right rounded-lg bg-surface-elevated border border-border shadow-lg focus:outline-none overflow-hidden z-[100]">
        <div class="py-1" role="menu" aria-orientation="vertical">
            <button @click="setTheme('light')" class="w-full text-left px-4 py-2 text-sm text-text-primary hover:bg-surface-muted flex items-center gap-2" :class="{'bg-primary-subtle text-primary': theme === 'light'}" role="menuitem">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Light
            </button>
            <button @click="setTheme('dark')" class="w-full text-left px-4 py-2 text-sm text-text-primary hover:bg-surface-muted flex items-center gap-2" :class="{'bg-primary-subtle text-primary': theme === 'dark'}" role="menuitem">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                Dark
            </button>
            <button @click="setTheme('system')" class="w-full text-left px-4 py-2 text-sm text-text-primary hover:bg-surface-muted flex items-center gap-2" :class="{'bg-primary-subtle text-primary': theme === 'system'}" role="menuitem">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                System
            </button>
        </div>
    </div>
</div>
