<?php // sqltest.php
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

  $message = '';
  $formError = '';

  if (isset($_POST['delete']) && isset($_POST['isbn']))
  {
    // Uses a prepared statement so the ISBN is treated as a value, not SQL.
    $isbn = trim($_POST['isbn']);
    $query = "DELETE FROM classics WHERE isbn = :isbn";
    $statement = $pdo->prepare($query);
    $statement->execute([':isbn' => $isbn]);
    $message = $statement->rowCount() > 0
      ? 'Book deleted successfully.'
      : 'No book was found with that ISBN.';
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
      $query = "INSERT INTO classics (author, title, category, year, isbn) VALUES (:author, :title, :category, :year, :isbn)";
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

  echo '<link rel="stylesheet" href="sqltest.css">';
  echo <<<_END
  <form class="book-form" action="sqltest.php" method="post"><pre>
    Author <input type="text" name="author" required>
     Title <input type="text" name="title" required>
  Category <input type="text" name="category" required>
      Year <input type="number" name="year" min="0" step="1" required>
      ISBN <input type="text" name="isbn" required>
           <input type="submit" value="ADD RECORD">
  </pre></form>
_END;

  if ($message !== '') {
    echo '<p role="status">' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
  }
  if ($formError !== '') {
    echo '<p role="alert">' . htmlspecialchars($formError, ENT_QUOTES, 'UTF-8') . '</p>';
  }

  $query  = "SELECT * FROM classics";
  $result = $pdo->query($query);

  echo '<table class="book-table">';
  echo '<thead><tr><th scope="col">Author</th><th scope="col">Title</th>';
  echo '<th scope="col">Category</th><th scope="col">Year</th>';
  echo '<th scope="col">ISBN</th><th scope="col">Action</th></tr></thead><tbody>';

  while ($row = $result->fetch(PDO::FETCH_OBJ))
  {
    $author = htmlspecialchars($row->author, ENT_QUOTES, 'UTF-8');
    $title = htmlspecialchars($row->title, ENT_QUOTES, 'UTF-8');
    $category = htmlspecialchars($row->category, ENT_QUOTES, 'UTF-8');
    $year = htmlspecialchars((string) $row->year, ENT_QUOTES, 'UTF-8');
    $isbn = htmlspecialchars($row->isbn, ENT_QUOTES, 'UTF-8');

    echo "<tr><td>$author</td><td>$title</td><td>$category</td>";
    echo "<td>$year</td><td>$isbn</td><td>";
    echo '<form action="sqltest.php" method="post">';
    echo '<input type="hidden" name="delete" value="yes">';
    echo '<input type="hidden" name="isbn" value="' . $isbn . '">';
    echo '<button type="submit">Delete</button></form></td></tr>';
  }

  echo '</tbody></table>';
?>