<?php // sqltest.php
  // Protect the session cookie from JavaScript access and cross-site requests.
  $isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' &&
    strtolower($_SERVER['HTTPS']) !== 'off';
  session_set_cookie_params([
    'httponly' => true,
    'secure' => $isHttps,
    'samesite' => 'Lax',
  ]);
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
  foreach (['author', 'title', 'category', 'year', 'isbn', 'delete', 'return_page', 'return_search'] as $field) {
    if (isset($_POST[$field]) && !is_string($_POST[$field])) {
      http_response_code(400);
      exit('Invalid form submission.');
    }
  }

  if (isset($_GET['search']) && !is_string($_GET['search'])) {
    http_response_code(400);
    exit('Invalid search request.');
  }
  $searchTerm = trim($_GET['search'] ?? '');
  $searchValue = htmlspecialchars($searchTerm, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  $searchQuery = $searchTerm !== '' ? '&search=' . rawurlencode($searchTerm) : '';

  try {
    require_once __DIR__ . '/login.php';
  } catch (Throwable $e) {
    // Configuration errors can contain sensitive paths or settings; log them privately.
    error_log('Publications database configuration failed: ' . $e->getMessage());
    http_response_code(500);
    exit('The database is not configured correctly. Please contact the site administrator.');
  }

  try {
    $pdo = new PDO($attr, $user, $pass, $opts);
  } catch (PDOException $e) {
    // Keep connection details in the server log instead of exposing them to visitors.
    error_log('Publications database connection failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Unable to connect to the database. Please try again later.');
  }

  $message = $_SESSION['flash_message'] ?? '';
  $formError = $_SESSION['flash_error'] ?? '';
  $invalidField = $_SESSION['invalid_field'] ?? '';
  $formValues = $_SESSION['form_values'] ?? [];
  unset(
    $_SESSION['flash_message'],
    $_SESSION['flash_error'],
    $_SESSION['invalid_field'],
    $_SESSION['form_values']
  );

  // Restore submitted values after a validation error so users do not retype everything.
  $authorValue = htmlspecialchars($formValues['author'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  $titleValue = htmlspecialchars($formValues['title'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  $categoryValue = htmlspecialchars($formValues['category'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  $yearValue = htmlspecialchars($formValues['year'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  $isbnValue = htmlspecialchars($formValues['isbn'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

  if (isset($_POST['delete'])) {
    $isbn = trim($_POST['isbn'] ?? '');

    // Accept only the expected delete action and a stored ISBN format.
    if ($_POST['delete'] !== 'yes' ||
        !preg_match('/^(?:[0-9]{9}[0-9X]|[0-9]{13})$/', $isbn)) {
      $formError = 'Invalid delete request. Refresh the page and try again.';
    } else {
      // Uses a prepared statement so the ISBN is treated as a value, not SQL.
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
  }

  if (!isset($_POST['delete']) &&
      (isset($_POST['author']) || isset($_POST['title']) ||
       isset($_POST['category']) || isset($_POST['year']) || isset($_POST['isbn'])))
  {
    $author   = trim($_POST['author'] ?? '');
    $title    = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $yearInput = $_POST['year'] ?? '';
    $isbn = strtoupper(preg_replace('/[\s-]+/', '', trim($_POST['isbn'] ?? '')));

    // Validate submitted values against the classics table's column limits.
    $year = filter_var($yearInput, FILTER_VALIDATE_INT);

    // Accept ISBN-10 or ISBN-13, allowing spaces and hyphens in the form input.
    $validIsbn = false;
    if (preg_match('/^[0-9]{9}[0-9X]$/', $isbn)) {
      $sum = 0;
      for ($i = 0; $i < 10; $i++) {
        $digit = $isbn[$i] === 'X' ? 10 : (int) $isbn[$i];
        $sum += $digit * (10 - $i);
      }
      $validIsbn = $sum % 11 === 0;
    } elseif (preg_match('/^[0-9]{13}$/', $isbn)) {
      $sum = 0;
      for ($i = 0; $i < 13; $i++) {
        $sum += (int) $isbn[$i] * ($i % 2 === 0 ? 1 : 3);
      }
      $validIsbn = $sum % 10 === 0;
    }
    $fieldValues = [
      'author' => $author,
      'title' => $title,
      'category' => $category,
      'isbn' => $isbn,
    ];
    $fieldLimits = [
      'author' => 128,
      'title' => 128,
      'category' => 16,
      'isbn' => 13,
    ];
    $tooLong = false;

    foreach ($fieldLimits as $field => $maxLength) {
      if (strlen($fieldValues[$field]) > $maxLength) {
        $tooLong = true;
        break;
      }
    }

    $invalidField = '';
    if ($author === '') {
      $invalidField = 'author';
      $formError = 'Enter an author.';
    } elseif ($title === '') {
      $invalidField = 'title';
      $formError = 'Enter a title.';
    } elseif ($category === '') {
      $invalidField = 'category';
      $formError = 'Enter a category.';
    } elseif ($yearInput === '') {
      $invalidField = 'year';
      $formError = 'Enter a publication year.';
    } elseif ($isbn === '') {
      $invalidField = 'isbn';
      $formError = 'Enter an ISBN.';
    } elseif ($tooLong) {
      foreach ($fieldLimits as $field => $maxLength) {
        if (strlen($fieldValues[$field]) > $maxLength) {
          $invalidField = $field;
          break;
        }
      }
      $formError = 'Author and title must be 128 characters or fewer, category 16 or fewer, and ISBN 13 or fewer.';
    } elseif (!$validIsbn) {
      $invalidField = 'isbn';
      $formError = 'Enter a valid ISBN-10 or ISBN-13.';
    } elseif ($year === false || $year < -3000 || $year > 2100 || $year === 0) {
      $invalidField = 'year';
      $formError = 'Enter a year from 3000 BCE through 2100 CE. Use a negative number for BCE; year 0 is not valid.';
    } else {
      // Bind form values separately so they cannot change the SQL query.
      $query = "INSERT INTO classics
        (author, title, category, year, isbn)
        VALUES (:author, :title, :category, :year, :isbn)";

      try {
        $statement = $pdo->prepare($query);
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
          $invalidField = 'isbn';
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
    $_SESSION['invalid_field'] = $invalidField ?? '';

    if ($formError !== '' && !isset($_POST['delete'])) {
      $_SESSION['form_values'] = [
        'author' => $_POST['author'] ?? '',
        'title' => $_POST['title'] ?? '',
        'category' => $_POST['category'] ?? '',
        'year' => $_POST['year'] ?? '',
        'isbn' => $_POST['isbn'] ?? '',
      ];
    }

    $returnPage = $_POST['return_page'] ?? '1';
    $returnPage = ctype_digit($returnPage) ? max(1, (int) $returnPage) : 1;
    $returnSearch = trim($_POST['return_search'] ?? '');
    $redirectUrl = 'sqltest.php?page=' . $returnPage;
    if ($returnSearch !== '') {
      $redirectUrl .= '&search=' . rawurlencode($returnSearch);
    }
    header('Location: ' . $redirectUrl);
    exit;
  }

  $csrfToken = htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  $authorInvalid = $invalidField === 'author' ? 'true' : 'false';
  $titleInvalid = $invalidField === 'title' ? 'true' : 'false';
  $categoryInvalid = $invalidField === 'category' ? 'true' : 'false';
  $yearInvalid = $invalidField === 'year' ? 'true' : 'false';
  $isbnInvalid = $invalidField === 'isbn' ? 'true' : 'false';

  echo <<<_END
  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Publications Book Manager</title>
    <link rel="stylesheet" href="sqltest.css">
  </head>
  <body>
    <main>
      <h1>Publications Book Manager</h1>
      <p class="page-intro">Add a book to the Publications database or manage existing records.</p>
      <form class="search-form" action="sqltest.php" method="get" role="search">
        <label for="book-search">Search books</label>
        <div class="search-controls">
          <input id="book-search" type="search" name="search" value="$searchValue" placeholder="Title, author, or ISBN">
          <button type="submit">Search</button>
          <a class="clear-search" href="sqltest.php">Clear</a>
        </div>
      </form>
      <form class="book-form" action="sqltest.php" method="post">
        <input type="hidden" name="csrf_token" value="$csrfToken">
        <input type="hidden" name="return_search" value="$searchValue">
        <div class="form-field">
          <label for="author">Author</label>
          <input id="author" type="text" name="author" maxlength="128" value="$authorValue" required aria-invalid="$authorInvalid">
        </div>
        <div class="form-field">
          <label for="title">Title</label>
          <input id="title" type="text" name="title" maxlength="128" value="$titleValue" required aria-invalid="$titleInvalid">
        </div>
        <div class="form-field">
          <label for="category">Category</label>
          <input id="category" type="text" name="category" maxlength="16" value="$categoryValue" required aria-invalid="$categoryInvalid">
        </div>
        <div class="form-field">
          <label for="year">Year (negative numbers for BCE, e.g. -3000)</label>
          <input id="year" type="number" name="year" min="-3000" max="2100" step="1" value="$yearValue" required aria-invalid="$yearInvalid">
        </div>
        <div class="form-field">
          <label for="isbn">ISBN</label>
          <input id="isbn" type="text" name="isbn" maxlength="17" value="$isbnValue" autocapitalize="characters" spellcheck="false" required aria-invalid="$isbnInvalid">
        </div>
        <input type="submit" value="ADD RECORD">
      </form>
_END;

  if ($message !== '') {
    echo '<p role="status">' . htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
  }
  if ($formError !== '') {
    echo '<p role="alert">' . htmlspecialchars($formError, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
  }

  // Limit each result page so the manager remains responsive as the table grows.
  $booksPerPage = 25;
  $searchPattern = '%' . $searchTerm . '%';
  $requestedPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
  $currentPage = $requestedPage === false || $requestedPage === null
    ? 1
    : max(1, $requestedPage);

  try {
    if ($searchTerm !== '') {
      $countStatement = $pdo->prepare("SELECT COUNT(*) FROM classics
        WHERE title LIKE :title OR author LIKE :author OR isbn LIKE :isbn");
      $countStatement->execute([
        ':title' => $searchPattern,
        ':author' => $searchPattern,
        ':isbn' => $searchPattern,
      ]);
      $totalBooks = (int) $countStatement->fetchColumn();
    } else {
      $totalBooks = (int) $pdo->query("SELECT COUNT(*) FROM classics")->fetchColumn();
    }
    $totalPages = max(1, (int) ceil($totalBooks / $booksPerPage));
    $currentPage = min($currentPage, $totalPages);
    $offset = ($currentPage - 1) * $booksPerPage;

    if ($searchTerm !== '') {
      $query = "SELECT author, title, category, year, isbn FROM classics
        WHERE title LIKE :title OR author LIKE :author OR isbn LIKE :isbn
        ORDER BY title ASC, isbn ASC LIMIT :limit OFFSET :offset";
      $statement = $pdo->prepare($query);
      $statement->bindValue(':title', $searchPattern, PDO::PARAM_STR);
      $statement->bindValue(':author', $searchPattern, PDO::PARAM_STR);
      $statement->bindValue(':isbn', $searchPattern, PDO::PARAM_STR);
    } else {
      $query = "SELECT author, title, category, year, isbn
        FROM classics ORDER BY title ASC, isbn ASC
        LIMIT :limit OFFSET :offset";
      $statement = $pdo->prepare($query);
    }
    $statement->bindValue(':limit', $booksPerPage, PDO::PARAM_INT);
    $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
    $statement->execute();
    $books = $statement->fetchAll(PDO::FETCH_OBJ);
  } catch (PDOException $e) {
    error_log('Publications book list query failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Unable to load the book list. Please try again later.');
  }

  if ($totalBooks === 0) {
    $emptyMessage = $searchTerm !== ''
      ? 'No books matched “' . htmlspecialchars($searchTerm, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '”. Try another search.'
      : 'No books have been added yet. Use the form above to add the first record.';
    echo '<p class="empty-state">' . $emptyMessage . '</p>';
  } else {
    $summaryLabel = $searchTerm !== '' ? ' matching books' : ' books';
    echo '<p class="table-summary">Showing ' . ($offset + 1) . '–' .
      min($offset + $booksPerPage, $totalBooks) . ' of ' . $totalBooks . $summaryLabel . '.</p>';
    echo '<div class="table-wrapper" role="region" aria-label="Scrollable book list" tabindex="0">';
    echo '<table class="book-table">';
    echo '<caption>Books in the Publications database</caption>';
    echo '<thead><tr><th scope="col">Author</th><th scope="col">Title</th>';
    echo '<th scope="col">Category</th><th scope="col">Year</th>';
    echo '<th scope="col">ISBN</th><th scope="col">Action</th></tr></thead><tbody>';

    foreach ($books as $row)
    {
      $author = htmlspecialchars($row->author, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
      $title = htmlspecialchars($row->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
      $category = htmlspecialchars($row->category, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
      $yearValue = (int) $row->year;
      $yearLabel = $yearValue < 0
        ? abs($yearValue) . ' BCE'
        : (string) $yearValue;
      $year = htmlspecialchars($yearLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
      $isbn = htmlspecialchars($row->isbn, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

      echo "<tr><td>$author</td><td>$title</td><td>$category</td>";
      echo "<td>$year</td><td>$isbn</td><td>";
      echo '<form action="sqltest.php" method="post" onsubmit="return confirm(\'Delete this book?\')">';
      echo '<input type="hidden" name="csrf_token" value="' .
        htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">';
      echo '<input type="hidden" name="delete" value="yes">';
      echo '<input type="hidden" name="return_page" value="' . $currentPage . '">';
      echo '<input type="hidden" name="return_search" value="' . $searchValue . '">';
      echo '<input type="hidden" name="isbn" value="' . $isbn . '">';
      echo '<button type="submit" aria-label="Delete ' .
        htmlspecialchars($row->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') .
        ' by ' . htmlspecialchars($row->author, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') .
        '">Delete</button></form></td></tr>';
    }

    echo '</tbody></table>';
    echo '</div>';

    if ($totalPages > 1) {
      echo '<nav class="pagination" aria-label="Book list pages">';

      if ($currentPage > 1) {
        echo '<a href="sqltest.php?page=' . ($currentPage - 1) . $searchQuery . '" aria-label="Go to previous page">Previous</a>';
      }

      $firstPage = max(1, $currentPage - 2);
      $lastPage = min($totalPages, $currentPage + 2);

      if ($firstPage > 1) {
        echo '<a href="sqltest.php?page=1' . $searchQuery . '" aria-label="Go to page 1">1</a>';
        if ($firstPage > 2) {
          echo '<span aria-hidden="true">…</span>';
        }
      }

      for ($page = $firstPage; $page <= $lastPage; $page++) {
        if ($page === $currentPage) {
          echo '<span aria-current="page" aria-label="Current page, page ' . $page . '">' . $page . '</span>';
        } else {
          echo '<a href="sqltest.php?page=' . $page . $searchQuery . '" aria-label="Go to page ' . $page . '">' . $page . '</a>';
        }
      }

      if ($lastPage < $totalPages) {
        if ($lastPage < $totalPages - 1) {
          echo '<span aria-hidden="true">…</span>';
        }
        echo '<a href="sqltest.php?page=' . $totalPages . $searchQuery . '" aria-label="Go to page ' . $totalPages . '">' . $totalPages . '</a>';
      }

      if ($currentPage < $totalPages) {
        echo '<a href="sqltest.php?page=' . ($currentPage + 1) . $searchQuery . '" aria-label="Go to next page">Next</a>';
      }

      echo '</nav>';
    }
  }

  echo '</main></body></html>';
?>