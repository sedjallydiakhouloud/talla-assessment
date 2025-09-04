# talla-assessment
Filament Image Gallery Application
This is a Filament-based web application that integrates with the Art Institute of Chicago API to fetch, manage, and favorite images. The application consists of three main pages: Image Gallery (API integration), My Images (user-uploaded images), and Favorites (favorited images from both sources). It supports search, pagination, favoriting, downloading, and basic animations.
Assessment Overview
This project fulfills the following requirements:
1. Image Gallery Page (API Integration)

API Source: Fetches images from the Art Institute of Chicago API.
Image URL: Utilizes the IIIF Image API to generate image URLs.
Features:

Displays images in a grid layout with:

The image itself.
Title of the artwork.
"Favorite" icon (❤️/🤍) to toggle favorite status.
"Download" icon (⬇️) to download the image.
"View" icon (👁️) to zoom in a modal with title.


Implements local filtering for search by title (after fetching data).
Supports pagination with "Prev" and "Next" buttons.
Allows image downloads via a dedicated controller.
Enables favoriting of API images, stored in the favorites table.
Uses Alpine.js for simple zoom animation on click.


Best Practices: Follows API guidelines for field selection, pagination, and image URL construction.

2. My Images Page

Purpose: Displays all images uploaded by the authenticated user.
Features:

Each image displays:

Image.
Title.
Description.
"Favorite" icon.
"Download" icon.

Allows users to add new images with:

Maximum file size: 6MB (validated as max:2048).
Required fields: Title and optional Description.
Uploaded files stored in public/uploads.

Supports downloading and deleting user-added images.
Includes search by title and pagination with configurable items per page (5, 9, 15, 30).
Favoriting toggles with validation and logging for debugging.

3. Favorites Page

Purpose: Displays all images favorited by the user, including both API-fetched and user-added images.
Features:
Each image displays:
Image (with fallback placeholder if not available).
Title (fetched dynamically for API images).

Allows users to unfavorite images with a remove button ("X") and animation.
Supports search and pagination (12 items per page by default).
Handles both API and user images uniformly, with detailed logging for debugging.
Includes a Filament resource for admin management.

Prerequisites

PHP: 8.1 or higher.
Composer: For dependency management.
Node.js and NPM: For frontend assets (if using Tailwind or Alpine.js).
Laravel: 10.x with Filament.
Database: MySQL, PostgreSQL, or SQLite (SQLite for local development; note foreign key issues in logs).
API Access: Internet connection for the Art Institute of Chicago API.
Storage: Configured public disk for uploaded images.

Installation

Clone the Repository:
bashgit clone https://github.com/your-username/filament-image-gallery.git
cd filament-image-gallery

Install Dependencies:
bashcomposer install
npm install

Configure Environment:

Copy the .env.example file to .env:
bashcp .env.example .env

Update .env with your database credentials:
textDB_CONNECTION=sqlite  # Or mysql/postgresql
DB_DATABASE=/absolute/path/to/your/database.sqlite  # For SQLite

Generate an application key:
bashphp artisan key:generate



Set Up the Database:

Run migrations to create tables (users, user_images, favorites):
bashphp artisan migrate



Link Storage (for uploaded images):
bashphp artisan storage:link

Compile Assets:
bashnpm run dev

Start the Development Server:
bashphp artisan serve


Usage

Access the Application:

Navigate to http://localhost:8000 (or your server URL).
Register/login as a user (Filament handles auth).


Image Gallery Page:

URL: /admin/gallery (Filament page).
Search artworks by title (local filter).
Paginate with "Prev/Next".
Favorite, view (modal zoom), or download images.


My Images Page:

URL: /admin/my-images (Filament page).
Upload images with title/description (max 2MB).
Search by title, paginate (configurable per page).
Favorite, download, or delete images.

Favorites Page:

URL: /favorites (custom route).
View favorited images with search/pagination.
Unfavorite with the "X" button.

Project Structure

app/Filament/Pages/Gallery.php: Handles API fetching, search, pagination, and favoriting for the gallery.
app/Filament/Pages/MyImages.php: Manages user image uploads, display, search, pagination, and favoriting.
app/Http/Controllers/ImageDownloadController.php: Downloads API images.
app/Http/Controllers/MyImagesController.php: HTTP handlers for my images (upload, delete, download, favorite).
app/Http/Controllers/FavoriteController.php: Handles favorites listing, unfavoriting, with API/user image support.
app/Models/Favorite.php: Model for favorites, with relations and API fetching.
app/Models/UserImage.php: Model for user-uploaded images.
resources/views/gallery.blade.php: View for gallery grid and modal.
resources/views/favorites.blade.php: View for favorites grid with placeholders and debug info.
routes/web.php: Defines routes for downloads, my-images, and favorites.
database/migrations/: Assumed migrations for user_images and favorites tables.

Additional Notes

API Usage: Respects Art Institute API best practices (field limiting, pagination). No authentication required.
Security: Validates uploads (max 6MB), restricts actions to authenticated users. Foreign keys are managed with logs for SQLite issues.
Debugging: Extensive logging in controllers (check storage/logs/laravel.log) for image URLs, favorites, and errors. Debug info in views for IDs/URLs.
Limitations: Search in My Images/Favorites is local (no API). Favorites may show placeholders if images fail to load. No initial seed data.
Customization: Adjust image sizes in CSS (e.g., height: 256px in gallery-item img). Update validation in controllers as needed.

Contributing
This project is for assessment purposes and is not open for public contributions. Fork and adapt as needed.
License
This project is unlicensed and intended for assessment evaluation only.
Acknowledgments

Filament for the admin framework.
Art Institute of Chicago API for image data.
Livewire for reactive components.
Tailwind CSS and Alpine.js for styling and animations.
