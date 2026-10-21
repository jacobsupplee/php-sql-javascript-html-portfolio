# Web Programming Projects

A collection of web projects built with HTML, CSS, JavaScript, PHP, and MySQL. The examples cover front-end interactions, form processing, server-side logic, and database operations.

## What's Included

- HTML and CSS page layouts and styling
- JavaScript examples using arrays and objects
- PHP examples with arrays, objects, and dynamic page output
- Form processing and input validation
- MySQL database access through PDO
- Interactive course-registration examples
- An index page for navigating the projects

## Built With

- HTML and CSS
- JavaScript
- PHP
- MySQL and PDO
- Apache HTTP Server

## Run Locally

1. Set up Apache with PHP and the PDO MySQL extension enabled.
2. Place the repository in the server's document root.
3. Copy `config.example.php` to `config.local.php`.
4. Edit `config.local.php` with the database usernames and passwords for your local MySQL instance. This file is ignored by Git and must not be committed.
5. Create the databases and tables required by the examples. The Publications example includes a SQL script in `discussion4/`.
6. Start Apache and MySQL, then open `index.php` through your local server.

Database-backed examples require their corresponding database and tables to exist. The local configuration file keeps credentials out of tracked source files.
