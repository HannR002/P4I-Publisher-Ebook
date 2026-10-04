# P4I Dual-Theme Design System

## Visual Philosophy
The P4I Digital Library uses a unified semantic design system. We maintain ONE design system mapped to TWO themes (Light and Dark).
The visual direction prioritizes a modern, clean, premium, and academic look, suited for long-form reading and professional publishing, without being overly gaming-like or flashy.

## Semantic Colors
We avoid hard-coding colors like `bg-white` and `dark:bg-gray-900` in UI components. Instead, we use semantic tokens that adapt automatically based on the theme.

### Backgrounds & Surfaces
- `bg-background`: The outermost layer of the application (e.g., page body).
- `bg-surface`: Primary content containers (e.g., cards, forms, content areas).
- `bg-surface-elevated`: Floating surfaces (e.g., dropdowns, modals).
- `bg-surface-muted`: Secondary surfaces for subdued contrast.

### Typography (Text)
- `text-text-primary`: Standard body text, headings, and primary reading material.
- `text-text-secondary`: Supporting text, captions, less critical metadata.
- `text-text-muted`: Placeholders, disabled states, or subtle hints.
- `text-text-inverse`: Text on primary backgrounds (e.g., white text on a primary button).

### Borders
- `border-border`: Standard divider lines, card borders, table rows.
- `border-border-strong`: Emphasized borders, active input borders, or high-contrast structural dividers.

### Brand Palette (Purple/Indigo)
P4I purple is our recognizable brand identity. We use it for primary actions, links, and selected states.
- `bg-primary`: Main interactive elements (Buttons, active tabs).
- `bg-primary-hover`: Hover states for primary elements.
- `bg-primary-subtle`: Backgrounds for subtle callouts or brand-tinted surfaces.
- `text-primary-foreground`: High-contrast text placed over primary backgrounds.

### State Colors
- `success`: Positive actions, completion, approval.
- `warning`: Cautionary alerts, pending states.
- `danger`: Destructive actions, errors, rejections.
- `info`: Informational banners, helpful notes.
- `focus-ring`: Accessible outline for keyboard navigation.

## Typography
Primary typeface: **Inter** (sans-serif) for the entire application (UI and public-facing academic layouts). We maintain standard HTML semantic tags (`h1`, `h2`, `p`, `small`) mapped to Tailwind utility classes.
- Line heights and line lengths are optimized for long-form reading (book synopsis, journal abstracts).

## Theme Toggle & Persistence
- Implemented in `public-layout.blade.php`.
- Evaluates `localStorage.theme`.
- Falls back to `window.matchMedia('(prefers-color-scheme: dark)')` (System Preference).
- An inline `<script>` in the `<head>` initializes the theme before DOM rendering to eliminate Flash of Unstyled Content (FOUC).

## Reusable Components Strategy
In Phase 2.5, foundational components (`x-button`, `x-form-field`, `x-card`, `x-badge`, `x-alert`, `x-publication-card`) were built using semantic colors exclusively (e.g., `class="bg-surface border border-border text-text-primary"`). Components automatically shift between light and dark modes.

## Theme Switcher
- Real interactive `x-theme-switcher` component replacing simple toggles.
- Supports "Light", "Dark", and "System" (synced to local storage).
- `x-theme-init` centralizes the `<head>` FOUC-prevention script.

## Accessibility (A11y)
- Semantic HTML tags are strictly utilized.
- All brand and text semantic colors have been calibrated to maintain WCAG-compliant contrast ratios in BOTH Light and Dark modes.
- Interactive elements receive a clear `focus-ring`.

## Responsive Layouts
- Global structural components use standardized containers (`max-w-7xl`, `max-w-5xl`).
- Grid/Flex gaps follow a scalable progression (`gap-4`, `gap-6`, `gap-8`) tailored to mobile, tablet, and desktop breakpoints.
