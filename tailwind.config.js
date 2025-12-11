import daisyui from 'daisyui';

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
  plugins: [
    daisyui,
  ],
  daisyui: {
    themes: [
      {
        ios: {
          // Paradocks Brand Colors (Treatwell Inspired: Cyan + Coral)
          "primary": "#6BC6D9",        // Brand Cyan (from logo)
          "secondary": "#0891B2",      // Cyan Interactive (WCAG AA 4.52:1)
          "accent": "#FF6B6B",         // Coral CTA (complementary warm)
          "success": "#34C759",        // iOS Green
          "warning": "#FF9500",        // iOS Orange
          "error": "#FF3B30",          // iOS Red
          "info": "#6BC6D9",           // Brand Cyan

          // Base colors (Light & Airy)
          "base-100": "#FFFFFF",       // Pure White Background
          "base-200": "#FAFAFA",       // Off-White (premium apps standard)
          "base-300": "#F5F5F5",       // Light Gray
          "base-content": "#171717",   // Text Primary (WCAG AA 10.4:1)

          // Border radius (iOS-style)
          "--rounded-box": "1.5rem",   // 24px for cards
          "--rounded-btn": "9999px",   // Pill buttons
          "--rounded-badge": "1rem",   // 16px badges

          // Animations
          "--animation-btn": "0.3s",
          "--animation-input": "0.2s",

          // Borders
          "--border-btn": "1px",
        }
      }
    ],
    base: true,     // Apply base styles
    styled: true,   // Include component classes
    utils: true,    // Add utility classes
    logs: false,    // Disable console logs
  },
}
