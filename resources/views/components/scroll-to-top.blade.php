<div x-data="{
        show: false,
        handleScroll() {
            this.show = window.scrollY > 400;
        },
        scrollToTop() {
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            window.scrollTo({
                top: 0,
                behavior: prefersReducedMotion ? 'auto' : 'smooth'
            });
        }
    }"
     x-init="handleScroll()"
     @scroll.window.passive="handleScroll()"
     class="fixed right-4 bottom-4 md:right-6 md:bottom-6 z-40 transition-all duration-300"
     style="padding-bottom: env(safe-area-inset-bottom);"
     x-bind:class="{ 'opacity-100 translate-y-0': show, 'opacity-0 translate-y-8 pointer-events-none': !show }"
     x-cloak
>
    <button type="button" 
            aria-label="Kembali ke atas"
            title="Kembali ke atas"
            @click="scrollToTop()"
            class="relative flex flex-col items-center justify-center w-[40px] h-[48px] md:w-[46px] md:h-[54px] bg-primary rounded-r-lg rounded-l-sm shadow-md hover:shadow-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-focus-ring overflow-hidden transition-all duration-300 transform hover:-translate-y-1 hover:bg-primary-hover group"
    >
        <!-- Book Spine Detail -->
        <div class="absolute left-0 top-0 bottom-0 w-1.5 md:w-2 bg-black/20 border-r border-white/10 dark:bg-black/30 dark:border-white/5" aria-hidden="true"></div>
        
        <!-- Right side subtle edge (pages) -->
        <div class="absolute right-0 top-0 bottom-0 w-px bg-white/20" aria-hidden="true"></div>
        
        <!-- Top edge detail -->
        <div class="absolute left-1.5 right-0 top-0 h-px bg-white/30" aria-hidden="true"></div>
        
        <!-- Icon -->
        <svg class="w-5 h-5 text-primary-foreground z-10 ml-1.5 md:ml-2 transition-transform duration-300 group-hover:-translate-y-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"></path>
        </svg>
    </button>
</div>
