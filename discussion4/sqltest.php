<?php // sqltest.php
  session_start();

  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }

  // Reject state-changing requests without a valid session token.
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? '';
    if (!is_string($submittedToken) ||
        !hash_equals($_SESSION['csrf_token'], $submittedToken)) {
      http_response_code(403);
      exit('Invalid request. Please refresh the page and try again.');
    }
  }

  // Form fields should be scalar values, not arrays supplied in a crafted request.
  foreach (['author', 'title', 'category', 'year', 'isbn', 'delete'] as $field) {
    if (isset($_POST[$field]) && !is_string($_POST[$field])) {
      http_response_code(400);
      exit('Invalid form submission.');
    }
  }

  require_once 'login.php';

  try
  {
    $pdo = new PDO($attr, $user, $pass, $opts);
  }
  catch (PDOException $e)
  {
    // Keep connection details in the server log instead of exposing them to visitors.
    error_log('Publications database connection failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Unable to connect to the database. Please try again later.');
  }

  $message = $_SESSION['flash_message'] ?? '';
  $formError = $_SESSION['flash_error'] ?? '';
  unset($_SESSION['flash_message'], $_SESSION['flash_error']);

  if (isset($_POST['delete']) && isset($_POST['isbn']))
  {
    // Uses a prepared statement so the ISBN is treated as a value, not SQL.
    $isbn = trim($_POST['isbn']);

    try {
      $query = "DELETE FROM classics WHERE isbn = :isbn";
      $statement = $pdo->prepare($query);
      $statement->execute([':isbn' => $isbn]);
      $message = $statement->rowCount() > 0
        ? 'Book deleted successfully.'
        : 'No book was found with that ISBN.';
    } catch (PDOException $e) {
      error_log('Publications book deletion failed: ' . $e->getMessage());
      $formError = 'The book could not be deleted. Please try again.';
    }
  }

  if (!isset($_POST['delete']) &&
      (isset($_POST['author']) || isset($_POST['title']) ||
       isset($_POST['category']) || isset($_POST['year']) || isset($_POST['isbn'])))
  {
    $author   = trim($_POST['author'] ?? '');
    $title    = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $yearInput = $_POST['year'] ?? '';
    $isbn     = trim($_POST['isbn'] ?? '');

    // Validate submitted values on the server before inserting a record.
    $year = filter_var($yearInput, FILTER_VALIDATE_INT);
    if ($author === '' || $title === '' || $category === '' || $isbn === '' ||
        $year === false || $year < 0) {
      $formError = 'Enter all fields and provide a non-negative whole-number year.';
    } else {
      // Bind form values separately so they cannot change the SQL query.
      $query = "INSERT INTO classics
        (author, title, category, year, isbn)
        VALUES (:author, :title, :category, :year, :isbn)";
      $statement = $pdo->prepare($query);

      try {
        $statement->execute([
          ':author'   => $author,
          ':title'    => $title,
          ':category' => $category,
          ':year'     => $year,
          ':isbn'     => $isbn
        ]);
        $message = 'Book added successfully.';
      } catch (PDOException $e) {
        // MySQL error 1062 means a unique value, such as the ISBN, already exists.
        if (($e->errorInfo[1] ?? null) === 1062) {
          $formError = 'A book with that ISBN already exists.';
        } else {
          error_log('Publications book insertion failed: ' . $e->getMessage());
          $formError = 'The book could not be added. Please try again.';
        }
      }
    }
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_error'] = $formError;
    header('Location: sqltest.php');
    exit;
  }

  $csrfToken = htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');

  echo '<link rel="stylesheet" href="sqltest.css">';
  echo <<<_END
  <form class="book-form" action="sqltest.php" method="post">
    <input type="hidden" name="csrf_token" value="$csrfToken">
    <div><label for="author">Author</label>
      <input id="author" type="text" name="author" required></div>
    <div><label for="title">Title</label>
      <input id="title" type="text" name="title" required></div>
    <div><label for="category">Category</label>
      <input id="category" type="text" name="category" required></div>
    <div><label for="year">Year</label>
      <input id="year" type="number" name="year" min="0" step="1" required></div>
    <div><label for="isbn">ISBN</label>
      <input id="isbn" type="text" name="isbn" required></div>
    <input type="submit" value="ADD RECORD">
  </form>
_END;

  if ($message !== '') {
    echo '<p role="status">' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
  }
  if ($formError !== '') {
    echo '<p role="alert">' . htmlspecialchars($formError, ENT_QUOTES, 'UTF-8') . '</p>';
  }

  try {
    $query = "SELECT author, title, category, year, isbn FROM classics ORDER BY title ASC";
    $result = $pdo->query($query);
    $books = $result->fetchAll(PDO::FETCH_OBJ);
  } catch (PDOException $e) {
    error_log('Publications book list query failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Unable to load the book list. Please try again later.');
  }

  if (count($books) === 0) {
    echo '<p>No books have been added yet.</p>';
  } else {
    echo '<table class="book-table">';
    echo '<thead><tr><th scope="col">Author</th><th scope="col">Title</th>';
    echo '<th scope="col">Category</th><th scope="col">Year</th>';
    echo '<th scope="col">ISBN</th><th scope="col">Action</th></tr></thead><tbody>';

    foreach ($books as $row)
    {
      $author = htmlspecialchars($row->author, ENT_QUOTES, 'UTF-8');
      $title = htmlspecialchars($row->title, ENT_QUOTES, 'UTF-8');
      $category = htmlspecialchars($row->category, ENT_QUOTES, 'UTF-8');
      $year = htmlspecialchars((string) $row->year, ENT_QUOTES, 'UTF-8');
      $isbn = htmlspecialchars($row->isbn, ENT_QUOTES, 'UTF-8');

      echo "<tr><td>$author</td><td>$title</td><td>$category</td>";
      echo "<td>$year</td><td>$isbn</td><td>";
      echo '<form action="sqltest.php" method="post" onsubmit="return confirm(\'Delete this book?\')">';
      echo '<input type="hidden" name="csrf_token" value="' .
        htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') . '">';
      echo '<input type="hidden" name="delete" value="yes">';
      echo '<input type="hidden" name="isbn" value="' . $isbn . '">';
      echo '<button type="submit">Delete</button></form></td></tr>';
    }

    echo '</tbody></table>';
  }
?>