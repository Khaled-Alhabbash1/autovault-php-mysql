# AutoVault features

## Public features

- Responsive Home, About, Media, Help/Wiki, Catalogue, and vehicle-details pages
- Catalogue keyword search across make and model
- Make, fuel, transmission, year, and price filters
- Fixed sorting choices and validated pagination
- Available-vehicle details, images, specifications, descriptions, and options
- Three complete themes: light, dark, and showroom
- Twenty sample vehicles, unique extracted photographs, and forty selectable options
- Three student-supplied videos with user-controlled HTML5 playback
- Seven context-sensitive Help/Wiki pages

## Authenticated-user features

- Registration as a normal user
- Login, logout, session handling, and disabled-account enforcement
- Private favourites scoped to the current session user
- Add/remove favourite actions for available vehicles
- Test-drive requests for available vehicles
- Preferred date/time, optional phone, and optional message validation
- Duplicate active-request protection for the same user, vehicle, and date

## Administrator features

- Role-protected dashboard
- Paginated vehicle listing
- Vehicle creation, editing, and soft deactivation
- Searchable/filterable/sortable/paginated user administration
- Account activation/deactivation with current/final-admin safeguards
- Searchable/filterable/sortable/paginated test-drive administration
- Request detail review and fixed status updates
- Read-only monitoring with safe health and aggregate information
- Non-programmer maintenance documentation for vehicles, images, videos, and credits

## Security features

- Native PDO prepared statements
- Fixed whitelists for dynamic sorting and enumerated actions
- Password hashing and verification through PHP's password API
- Session regeneration after login
- HttpOnly, SameSite=Lax session cookies and conditional Secure cookies
- POST and CSRF protection for state changes
- Output escaping and restricted local vehicle/media paths
- Login and administrator authorization gates
- Generic user-facing database errors with technical server logging
- No public administrator role selection

## Responsive, theme, and accessibility features

- Mobile-first CSS without a framework
- Keyboard-operable responsive navigation
- Skip-to-content link and stable main-content target
- Visible focus indicators
- Semantic headings, forms, navigation, sections, and status text
- Captioned, scoped, horizontally scrollable administrator tables
- Responsive images and HTML5 videos
- Persistent whitelisted light/dark/showroom theme
- `prefers-reduced-motion` support
