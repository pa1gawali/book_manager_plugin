# Books Manager Plugin

A WordPress plugin for managing a restricted Books collection.

## Features

- Registers a custom post type `Book`.
- Adds admin fields for Author, Genre, and Published Date.
- Uses the built-in editor for the description.
- Restricts single book pages and archive/book list access to logged-in users.
- Provides a `[books_list]` shortcode for listing books with pagination.
- Ensures all user input is sanitized and validated.
- Supports search and filter by Author and Genre.
- Uses AJAX filtering for the book listing.
- Includes responsive front-end styling.

## Install

1. Copy the folder `wp-content/plugins/books-manager` into your WordPress install.
2. Activate the plugin in the WordPress admin under Plugins.
3. A new page titled **Books Listing** will be created automatically with the `[books_list]` shortcode.

## Testing

1. Go to **Books > Add New** in the WordPress admin.
2. Create books with Title, Author, Genre, Published Date, and Description.
3. Visit the auto-created **Books Listing** page while logged in to see the list.
4. Log out and try to access a single book or the listing page to confirm restricted access.

## Access Restriction Implementation

- Single book pages are intercepted via `template_redirect` and blocked for non-logged-in users.
- The listing shortcode checks authentication before rendering.
- A message with login and registration links is displayed when access is denied.

## Notes

- The custom post type archive is also protected.
- `books-manager.php` includes the full CPT registration, meta boxes, save handlers, shortcode, AJAX action, and front-end templates.
- Front-end assets are loaded from `assets/css/books-manager.css` and `assets/js/books-manager.js`.

## Files

- `books-manager.php` — main plugin file
- `assets/css/books-manager.css` — front-end styles
- `assets/js/books-manager.js` — AJAX filter behavior
- `templates/single-book.php` — single book template
- `templates/archive-book.php` — books archive template
- `templates/shortcode-books-list.php` — listing shortcode template

## Screenshots

The plugin now includes uploaded screenshot files in `assets/images/`.

- ![Books Listing](assets/images/book%20listing%20page.png)
- ![Single Book View](assets/images/single%20book%20page.png)
- ![Admin Page](assets/images/book%20manager%20admin%20page.png)

Place any additional screenshot files in `wp-content/plugins/books-manager/assets/images/` and commit them to the repository.
