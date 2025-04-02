# WordPress Author Management Meta Box

A lightweight WordPress theme modification to add an "Author Management" meta box to the post edit screen. Allows selecting existing authors or creating new ones with Latin-only usernames and multilingual nicknames.

## Features
- Meta box on post edit screen for author management.
- Select existing users (Subscriber, Author, Editor, Administrator).
- Create new authors with:
  - Username (Latin-only, e.g., `rahimkhan`).
  - Nickname (Any language, e.g., `রহিম খান`).
  - Email and role selection.
- AJAX-powered author creation with real-time feedback.
- Assigns authors to posts on save.
- Displays author nickname on the frontend.
- Modern, attractive UI with hover effects.

## Advantages
- No plugin required.
- Multilingual nickname support.
- Secure with nonce and capability checks.
- Optimized and lightweight.

## Installation
1. Clone or download this repository.
2. Copy `functions.php` to your theme's directory (`/wp-content/themes/your-theme-name/`).
3. Backup your existing `functions.php` first.
4. Append this code to your existing `functions.php` if it contains other functions.
5. Test on a staging site before deploying to production.

## Usage
- Go to Posts > Add New.
- Use the "Author Management" meta box to select or create an author.
- Save the post to assign the author.
- View the post to see "Written by: [Nickname]".

## Requirements
- WordPress 5.0+
- PHP 7.0+
- jQuery (included with WordPress)

## License
MIT License - feel free to use and modify!



![image](https://github.com/user-attachments/assets/0a5ed06c-76f1-4599-bf7c-634b9cb633e3)

