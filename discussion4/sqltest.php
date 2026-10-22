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
      $query = "INSERT INTO classics VALUES (:author, :title, :category, :year, :isbn)";
      $statement = $pdo->prepare($query);
      $statement->execute([
        ':author'   => $author,
        ':title'    => $title,
        ':category' => $category,
        ':year'     => $year,
        ':isbn'     => $isbn
      ]);
      $message = 'Book added successfully.';
    }
  }

  echo <<<_END
  <form action="sqltest.php" method="post"><pre>
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

  while ($row = $result->fetch(PDO::FETCH_OBJ)) //Added PDO::FETCH_OBJ, returning aeach row as an object
  {
      //changed from brackets to arrows because the row is an object
$r0 = htmlspecialchars($row->author); 
$r1 = htmlspecialchars($row->title);
$r2 = htmlspecialchars($row->category);
$r3 = htmlspecialchars($row->year);
$r4 = htmlspecialchars($row->isbn);
    
    echo <<<_END
  <pre>
    Author $r0
     Title $r1
  Category $r2
      Year $r3
      ISBN $r4
  </pre>
  <form action='sqltest.php' method='post'>
  <input type='hidden' name='delete' value='yes'>
  <input type='hidden' name='isbn' value='$r4'>
  <input type='submit' value='DELETE RECORD'></form>
_END;
  }

?>