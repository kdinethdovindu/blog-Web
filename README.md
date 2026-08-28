# Blog Web Application

A full-stack blog web application developed using PHP and MySQL. The application allows users to create accounts, authenticate, publish blog posts, and manage their own content.

This project was independently developed to gain practical experience in PHP, MySQL, authentication, CRUD operations, sessions, authorization, and database-driven web application development.

## Features

- User registration and login with username or email
- Session-based authentication
- Create, read, update, and delete blog posts
- User-specific post management
- Authorization checks for editing and deleting posts
- Unique, readable blog post URLs using generated slugs
- Lightweight Markdown-style post formatting and live preview
- Responsive interface built with custom CSS
- Flash messages for important user actions

## Tech Stack

- PHP 8
- MySQL or MariaDB
- PDO for database access
- HTML5
- CSS3
- JavaScript
- Git and GitHub

## Security Features

- Password hashing with `password_hash()`
- Password verification with `password_verify()`
- Prepared PDO statements for queries containing user input
- Session ID regeneration after registration, login, and logout
- HTTP-only, SameSite session cookies
- CSRF protection for authentication, post creation, editing, deletion, and logout
- Ownership and role checks before posts can be edited or deleted
- HTML escaping for user-controlled output
- Local database credentials excluded from Git

## Project Structure

```text
assets/
  css/                 Application styles
  js/                  Navigation and post-preview scripts
config/
  .env.example.php     Safe configuration template
  db.php               PDO database connection
database/
  schema.sql            Database tables for a new installation
includes/
  init.php              Sessions, database bootstrap, and shared helpers
  header.php            Shared page header and navigation
  footer.php            Shared page footer
  flash.php             Flash-message display
public/
  index.php              Blog home page
  register.php           User registration
  login.php              User login
  new.php                Post creation
  edit.php               Post editing
  delete.php             Post deletion
  mine.php               Current user's posts
  post.php               Individual post page
index.php                 Local PHP environment health check
```

## Getting Started

### Requirements

- PHP 8.0 or newer with the PDO MySQL and `mbstring` extensions
- MySQL or MariaDB
- A local server environment such as XAMPP, WampServer, or PHP's built-in development server

### Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/kdinethdovindu/blog-Web.git
   cd blog-Web
   ```

2. Create a MySQL database, for example `blog_web`.

3. Import [`database/schema.sql`](database/schema.sql) into the new database. You can use phpMyAdmin or the MySQL command line:

   ```bash
   mysql -u root -p blog_web < database/schema.sql
   ```

4. Copy the example configuration:

   ```bash
   cp config/.env.example.php config/.env.php
   ```

   On Windows PowerShell, use:

   ```powershell
   Copy-Item config/.env.example.php config/.env.php
   ```

5. Edit `config/.env.php` with your local database settings. For a typical XAMPP installation:

   ```php
   <?php

   define('DB_HOST', '127.0.0.1');
   define('DB_NAME', 'blog_web');
   define('DB_USER', 'root');
   define('DB_PASS', '');

   define('BASE_URL', 'http://localhost/blog-Web');
   ```

   `BASE_URL` should point to the project root without `/public`. Never commit this local configuration file.

6. Start the application:

   - **XAMPP:** place the project in `htdocs`, start Apache and MySQL, and open `http://localhost/blog-Web/public/`.
   - **PHP development server:** set `BASE_URL` to `http://localhost:8000`, run `php -S localhost:8000` from the project root, and open `http://localhost:8000/public/`.

7. Register an account and create your first post.

## Author

**Dineth Dovindu**

GitHub: [github.com/kdinethdovindu](https://github.com/kdinethdovindu)
