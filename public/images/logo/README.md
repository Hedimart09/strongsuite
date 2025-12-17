# Logo Images Directory

This directory is for storing StrongSuite logo images.

## Recommended Files

Place your logo files here with the following names:

- **`logo.png`** - Main logo (PNG format with transparency)
- **`logo.svg`** - Main logo (SVG format, scalable vector)
- **`logo-light.png`** - Logo for dark backgrounds
- **`logo-dark.png`** - Logo for light backgrounds
- **`logo-icon.png`** - Icon/favicon version (square, at least 512x512px)
- **`logo-icon.svg`** - Icon/favicon version (SVG format)

## Usage

To use your logo in the application:

1. Add your logo files to this directory
2. Update the `AppLogoIcon.vue` component at:
   `resources/js/components/AppLogoIcon.vue`

   Replace the SVG content with your logo or use an `<img>` tag:
   ```vue
   <img src="/images/logo/logo-icon.svg" alt="StrongSuite" />
   ```

3. For the sidebar logo, you can update `AppLogo.vue` at:
   `resources/js/components/AppLogo.vue`

## Current Status

✅ Application name updated to **StrongSuite**
✅ Subtitle added: **Gym Management**
⏳ Waiting for custom logo images

## File Size Recommendations

- PNG files: Optimize to keep under 100KB
- SVG files: Keep simple, optimize paths
- Icon sizes: 512x512px (for high-DPI displays)
