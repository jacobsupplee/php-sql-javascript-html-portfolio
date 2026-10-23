# Web Development Portfolio

A collection of web development projects built with PHP, JavaScript, HTML, CSS, and MySQL. The projects demonstrate object-oriented programming, interactive interfaces, database operations, input validation, and foundational web security practices.

## Project Areas

### PHP & Object-Oriented Programming
- **PHP User Profile Objects** — model profile information with a PHP class and render escaped output.
- **PHP Course Data Structures** — compare associative arrays with arrays of objects.
- **PHP Course Object Collection** — represent course records with a class, getters, and calculated totals.
- **Earth Volume Calculator** — use PHP constants and arithmetic to estimate a sphere's volume.

### JavaScript Applications
- **JavaScript Course Data Structures** — compare object properties with an array of course objects.
- **Interactive Course Registration** — add and remove courses, prevent duplicate selections, and calculate credit totals.
- **JavaScript Course Object Collection** — render a collection of course objects using DOM methods and array methods.
- **Client-Side Form Validation** — validate form fields in the browser before submission.

### Database Applications
- **Publications Library Manager** — PHP and MySQL CRUD operations, prepared statements, search, pagination, and validation.
- **PDO Course Database** — add and list course records using PDO and server-side validation.

### Web Security & Server-Side Validation
- **HTTP Basic Authentication Demo** — demonstrate HTTP authentication with credentials kept in a local configuration file.
- **Server-Side Form Validation** — validate submitted data in PHP as a counterpart to client-side checks.

## Built With

- PHP and object-oriented programming
- JavaScript and DOM APIs
- HTML5 and CSS
- MySQL and PDO
- Apache HTTP Server

## Run Locally

1. Set up Apache with PHP and the PDO MySQL extension enabled.
2. Place the repository in the server's document root.
3. Copy `config.example.php` to `config.local.php` and add your local database credentials. The local file is ignored by Git and must not be committed.
4. For the Publications Library Manager, run `php-mysql-library-manager/schema.sql` in your local MySQL environment. **The script drops and recreates the `publications` database**, so do not run it against a database containing data you need.
5. Create the `cmsc340` database and `mycourses` table required by the PDO Course Database example.
6. For the HTTP Basic Authentication Demo, copy `php-http-basic-authentication/auth.example.php` to `auth.local.php` in that same folder and set local demo credentials. Never use a real account password.
7. Start Apache and MySQL, then open `index.php` through your local server.

Database-backed projects require their corresponding databases and tables. These projects are learning and portfolio demonstrations, not claims of production deployment.
