# Accessibility

## Current support

- Semantic shared `header`, `nav`, `main`, and `footer` regions
- One primary page heading in normal page states and logical subsection headings
- A keyboard-visible skip link targeting the shared main region
- Native links for navigation and buttons for actions
- Keyboard-operable mobile navigation with `aria-controls`,
  updated `aria-expanded`, Escape-to-close, and focus return
- Three-state theme control with a dynamically updated accessible label
- Visible labels for existing form controls
- Required attributes on required inputs
- Alert/status roles for validation and success summaries
- Useful vehicle-image alt text, with fallback text derived from vehicle identity
- Text labels within status badges, so status does not rely only on colour
- Visible global focus indicators
- Responsive card/form/video layouts and horizontally scrollable admin tables
- Captions and `scope` attributes on administrator data tables
- Light/dark/showroom theme colour variables and browser `color-scheme`
- Reduced animation for `prefers-reduced-motion`

## Manual accessibility review required

The code and generated HTML received static review, but assistive-technology
behavior must still be verified manually:

- Keyboard-only traversal and focus order in desktop and mobile layouts
- Skip-link visibility and focus movement in Chrome and Edge
- Mobile menu Escape behavior and focus return
- Screen-reader landmark, heading, label, table, status, and error announcements
- Colour contrast with a measurement tool in all three themes
- 200% and 400% zoom/reflow
- High-contrast/forced-colours mode
- JavaScript-disabled usability

## Known limitations

- Form errors are summarized with alerts but are not consistently connected to
  individual controls with `aria-describedby` and `aria-invalid`.
- The supplied MP4 files do not include caption or transcript files.
- Automated WCAG, screen-reader, and browser accessibility tests are not part
  of the repository.

These limitations should be addressed and manually retested before asserting
conformance to a specific WCAG level.
