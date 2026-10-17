# PHP and JavaScript Web Programming Portfolio

A collection of small web programming examples demonstrating how HTML, CSS, JavaScript, PHP, and MySQL can work together to build interactive pages and process form data.

The site groups examples into a simple index so individual implementations can be explored independently.

## Areas covered

- **PHP fundamentals:** Dynamic page output, arrays, and objects
- **Database access:** PHP Data Objects (PDO) and retrieval of MySQL records
- **Form handling:** Reading submitted form values and displaying validation feedback
- **Validation:** Client-side JavaScript checks paired with server-side PHP validation
- **JavaScript:** Arrays and objects, plus interactive course-registration examples
- **Presentation:** Shared CSS styling and a linked project index

## Technology

- HTML
- CSS
- JavaScript
- PHP
- MySQL
- PDO

## Run locally

This repository is designed to run in a local PHP-capable web environment with a web server and PHP installed. Some examples also require a configured MySQL database.

1. Clone the repository into the web server's document root.
2. Start the web server and configure PHP.
3. Open `index.php` through the local server.
4. For database examples, configure the connection in the relevant included file and prepare the expected database/table before running the example.

Individual folders may have different setup requirements; inspect each example before running it.

## Security and scope notes

This is a learning portfolio, not a production-ready web application. Database examples should be reviewed before deployment; use parameterized queries, least-privilege database credentials, secure password hashing, output escaping, and appropriate session/authentication controls. Do not expose real credentials in source control.

## Skills demonstrated

Connecting browser-facing pages to server-side logic, handling form submissions, accessing relational data with PDO, and organizing related examples behind a navigable interface.

## Project context

The repository contains a set of focused web-programming examples rather than one integrated product. Each example is documented as a demonstration of a specific implementation technique.