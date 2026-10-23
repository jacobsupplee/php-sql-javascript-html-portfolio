<?php
// Edit an existing Publications record.
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

function escapeHtml(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function validIsbn(string $isbn): bool {
    if (preg_match('/^[0-9]{9}[0-9X]$/', $isbn)) {
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += ($isbn[$i] === 'X' ? 10 : (int) $isbn[$i]) * (10 - $i);
        }
        return $sum % 11 === 0;
    }
    if (preg_match('/^[0-9]{13}$/', $isbn)) {
        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum += (int) $isbn[$i] * ($i % 2 === 0 ? 1 : 3);
        }
        return $sum % 10 === 0;
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        exit('Invalid request. Please return to the library and try again.');
    }
}

$originalIsbn = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? ($_POST['original_isbn'] ?? '')
    : ($_GET['isbn'] ?? '');
if (!is_string($originalIsbn) ||
    !preg_match('/^(?:[0-9]{9}[0-9X]|[0-9]{13})$/', $originalIsbn)) {
    http_response_code(400);
    exit('Invalid book identifier.');
}

$returnPage = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? ($_POST['return_page'] ?? '1')
    : ($_GET['return_page'] ?? '1');
$returnPage = is_string($returnPage) && ctype_digit($returnPage)
    ? max(1, (int) $returnPage)
    : 1;
$returnSearch = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? ($_POST['return_search'] ?? '')
    : ($_GET['search'] ?? '');
if (!is_string($returnSearch)) {
    $returnSearch = '';
}

try {
    require_once __DIR__ . '/database.php';
    $pdo = new PDO($attr, $user, $pass, $opts);
} catch (Throwable $e) {
    error_log('Publications edit database setup failed: ' . $e->getMessage());
    http_response_code(500);
    exit('The database is unavailable. Please try again later.');
}

$error = '';
$book = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['author', 'title', 'category', 'year', 'isbn'] as $field) {
        if (isset($_POST[$field]) && !is_string($_POST[$field])) {
            http_response_code(400);
            exit('Invalid form submission.');
        }
    }

    $book = [
        'author' => trim($_POST['author'] ?? ''),
        'title' => trim($_POST['title'] ?? ''),
        'category' => trim($_POST['category'] ?? ''),
        'year' => trim($_POST['year'] ?? ''),
        'isbn' => strtoupper(preg_replace('/[\\s-]+/', '', trim($_POST['isbn'] ?? ''))),
    ];
    $year = filter_var($book['year'], FILTER_VALIDATE_INT);

    if ($book['author'] === '' || $book['title'] === '' || $book['category'] === '' ||
        $book['year'] === '' || $book['isbn'] === '') {
        $error = 'Complete all fields before saving.';
    } elseif (strlen($book['author']) > 128 || strlen($book['title']) > 128 ||
        strlen($book['category']) > 16 || strlen($book['isbn']) > 13) {
        $error = 'Author and title must be 128 characters or fewer, category 16 or fewer, and ISBN 13 or fewer.';
    } elseif (!validIsbn($book['isbn'])) {
        $error = 'Enter a valid ISBN-10 or ISBN-13.';
    } elseif ($year === false || $year < -3000 || $year > 2100 || $year === 0) {
        $error = 'Enter a year from 3000 BCE through 2100 CE. Use a negative number for BCE; year 0 is not valid.';
    } else {
        try {
            $statement = $pdo->prepare(
                'UPDATE classics SET author = :author, title = :title, category = :category,
                 year = :year, isbn = :isbn WHERE isbn = :original_isbn'
            );
            $statement->execute([
                ':author' => $book['author'],
                ':title' => $book['title'],
                ':category' => $book['category'],
                ':year' => $year,
                ':isbn' => $book['isbn'],
                ':original_isbn' => $originalIsbn,
            ]);

            if ($statement->rowCount() === 0) {
                $check = $pdo->prepare('SELECT 1 FROM classics WHERE isbn = :isbn');
                $check->execute([':isbn' => $book['isbn']]);
                if (!$check->fetchColumn()) {
                    $error = 'The book could not be found. It may have been deleted.';
                }
            }

            if ($error === '') {
                $_SESSION['flash_message'] = 'Book updated successfully.';
                $redirect = 'index.php?page=' . $returnPage;
                if ($returnSearch !== '') {
                    $redirect .= '&search=' . rawurlencode($returnSearch);
                }
                header('Location: ' . $redirect);
                exit;
            }
        } catch (PDOException $e) {
            if (($e->errorInfo[1] ?? null) === 1062) {
                $error = 'A different book already uses that ISBN.';
            } else {
                error_log('Publications book update failed: ' . $e->getMessage());
                $error = 'The book could not be updated. Please try again.';
            }
        }
    }
} else {
    try {
        $statement = $pdo->prepare(
            'SELECT author, title, category, year, isbn FROM classics WHERE isbn = :isbn'
        );
        $statement->execute([':isbn' => $originalIsbn]);
        $book = $statement->fetch(PDO::FETCH_ASSOC);
        if (!$book) {
            http_response_code(404);
            exit('That book was not found. It may have been deleted.');
        }
        $book['year'] = (string) $book['year'];
    } catch (PDOException $e) {
        error_log('Publications book edit lookup failed: ' . $e->getMessage());
        http_response_code(500);
        exit('The selected book could not be loaded for editing.');
    }
}

$csrfToken = escapeHtml($_SESSION['csrf_token']);
$originalIsbnValue = escapeHtml($originalIsbn);
$returnSearchValue = escapeHtml($returnSearch);
$backUrl = 'index.php?page=' . $returnPage;
if ($returnSearch !== '') {
    $backUrl .= '&search=' . rawurlencode($returnSearch);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Book | Publications Library Manager</title>
    <link rel="stylesheet" href="library-manager.css">
</head>
<body>
<main>
    <h1>Edit Book</h1>
    <p class="page-intro">Update the selected book's details, then save your changes.</p>

    <?php if ($error !== ''): ?>
        <p role="alert"><?= escapeHtml($error) ?></p>
    <?php endif; ?>

    <form class="book-form" action="edit.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
        <input type="hidden" name="original_isbn" value="<?= $originalIsbnValue ?>">
        <input type="hidden" name="return_page" value="<?= $returnPage ?>">
        <input type="hidden" name="return_search" value="<?= $returnSearchValue ?>">

        <div class="form-field">
            <label for="author">Author</label>
            <input id="author" name="author" type="text" maxlength="128" required value="<?= escapeHtml($book['author'] ?? '') ?>">
        </div>
        <div class="form-field">
            <label for="title">Title</label>
            <input id="title" name="title" type="text" maxlength="128" required value="<?= escapeHtml($book['title'] ?? '') ?>">
        </div>
        <div class="form-field">
            <label for="category">Category</label>
            <input id="category" name="category" type="text" maxlength="16" required value="<?= escapeHtml($book['category'] ?? '') ?>">
        </div>
        <div class="form-field">
            <label for="year">Year (negative numbers for BCE, e.g. -3000)</label>
            <input id="year" name="year" type="number" min="-3000" max="2100" step="1" required value="<?= escapeHtml($book['year'] ?? '') ?>">
        </div>
        <div class="form-field">
            <label for="isbn">ISBN</label>
            <input id="isbn" name="isbn" type="text" maxlength="17" autocapitalize="characters" spellcheck="false" required value="<?= escapeHtml($book['isbn'] ?? '') ?>">
        </div>
        <input type="submit" value="SAVE CHANGES">
        <a class="clear-search" href="<?= escapeHtml($backUrl) ?>">Cancel and return to library</a>
    </form>
</main>
</body>
</html>
