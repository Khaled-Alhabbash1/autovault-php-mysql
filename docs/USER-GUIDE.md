# AutoVault user guide

## Registration

Choose **Register**, enter your first name, last name, email address, and a
password of at least eight characters, then confirm the password. Required
fields are checked on the server. Registration always creates a normal user.

## Login

Choose **Login** and enter the registered email and password. Incorrect login
details return a general error. A disabled account cannot log in.

## Browse the catalogue

The Catalogue page lists available vehicles. Select a vehicle image or title
to open its details. Sold, reserved, or missing vehicles are not exposed as
available public records.

## Search, filter, sort, and paginate

Use the labelled controls to search make/model and filter by make, fuel,
transmission, year, and price. Choose one of the provided sort choices, then
select **Apply filters**. Use pagination links to move through results; filter
values remain in the page links.

## Vehicle details

The details page shows the vehicle name, price, images or an image placeholder,
specifications, options, description, and available account actions. An
invalid ID receives a safe invalid-link response and an unavailable record
receives a not-found response.

## Favourites

Log in, open an available vehicle, and select **Add to favourites**. Open
**Favourites** in the navigation to view or remove saved vehicles. Each
account sees only its own saved records.

## Test-drive requests

Log in and select **Request a test drive** on an available vehicle. Choose a
future preferred date. Time, phone, and message are optional. The server takes
the name and email from the signed-in account. A request is pending until an
administrator reviews it.

## Theme switching

Use the theme control in the main navigation. It cycles through light, dark,
and showroom and saves only the selected theme name in browser storage. Without
a saved choice, the site follows the browser's light/dark system preference.

## Vehicle media

Open **Media** to watch the three student-supplied vehicle videos. Each player
uses browser-native controls and loads metadata only; playback never starts or
loops automatically. See **Media help** if a browser cannot play an MP4.

## Help/Wiki

The Help link follows the current task. Separate topics cover catalogue and
vehicles, accounts, favourites, test drives, media, and administrator tools.

## Logout

Choose **Logout** from the navigation. Logout uses a protected POST action,
clears session data, removes the session cookie, and destroys the server session.

For shorter task-based help, see the public `help.php` page and its topic links.
