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
          // Paradocks Brand Colors (Bentley Modern: 25% Saturation Sophisticated)
          "primary": "#6B9FA8",        // Desaturated Cyan (25% sat, luxury automotive)
          "secondary": "#2B2D2F",      // Warm Charcoal (dark primary)
          "accent": "#8B7355",         // Bronze (premium touch)
          "success": "#34C759",        // iOS Green
          "warning": "#FF9500",        // iOS Orange
          "error": "#FF3B30",          // iOS Red
          "info": "#6B9FA8",           // Desaturated Cyan

          // Base colors (Warm & Sophisticated)
          "base-100": "#FFFFFF",       // Pure White Background
          "base-200": "#D4C5B0",       // Tan Leather (30% usage)
          "base-300": "#E8DFD0",       // Light Tan
          "base-content": "#2B2D2F",   // Warm Charcoal Text

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
