# SafeNest — Smart Home Security Store

A PHP and MySQL store for home security products with customer, security expert and admin areas.

## Features

- Browse products by security category
- Product and order administration
- Security expert access
- Nestor chatbot page and support messages

## Technology

PHP, MySQL, HTML, CSS and JavaScript.

## Local setup

Clone the repository:

```bash
git clone https://github.com/nasirabbas827/Safenest-Ai-final.git
cd Safenest-Ai-final
```

1. Install a local PHP web server and MySQL, such as XAMPP.
2. Place the project in the server's document root, or configure a virtual host pointing to this folder.
3. Import `Database/safenest_db.sql` using phpMyAdmin or your MySQL client.
4. Update the database connection in `config.php` and in role-specific copies of `config.php` to match your local database name and credentials.
5. Start Apache and MySQL, then open `http://localhost/Safenest-Ai-final/`.

Email verification and notification features require your own SMTP settings in the relevant mail handlers.

## Repository layout

| Path | Purpose |
|---|---|
| `Database/` | SQL database export |
| `PHPMailer/` | Bundled email library |
| `admin/` | Role-specific pages |
| `config.php` | Database connection configuration |
| `css/` | Project resources |
| `expert/` | Role-specific pages |
| `images/` | Project resources |
| `index.php` | Web entry page |
| `uploads/` | Project resources |
| `user/` | Project resources |

## Development notes

Setup steps describe local development. Configure your own database, mail and API credentials where applicable. Review debug settings and replace development secrets before deploying. The application was not executed as part of this documentation update.

Maintained by [Nasir Abbas](https://github.com/nasirabbas827).
