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
          // Colors from design-system.json
          "primary": "#007AFF",        // iOS Blue
          "secondary": "#5856D6",      // iOS Purple
          "accent": "#FF9500",         // iOS Orange
          "success": "#34C759",        // iOS Green
          "warning": "#FF9500",        // iOS Orange
          "error": "#FF3B30",          // iOS Red
          "info": "#0A84FF",           // iOS Light Blue

          // Base colors
          "base-100": "#FFFFFF",       // Background
          "base-200": "#F2F2F7",       // iOS Light Gray
          "base-300": "#E5E5EA",       // iOS Gray
          "base-content": "#000000",   // Text Primary

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
