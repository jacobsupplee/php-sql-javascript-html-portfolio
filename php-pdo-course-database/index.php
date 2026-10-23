<?php
// PDO Course Database demonstration
// Uses local database settings stored in config.local.php.

session_start();

// Create a token that helps protect the form against cross-site request forgery.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function escapeHtml($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Load the local database connection settings.
$config = require __DIR__ . '/../config.local.php';
$database = $config['cmsc340'];

$host = $database['host'];
$port = $database['port'];
$dbName = $database['database'];
$username = $database['username'];
$password = $database['password'];

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbName;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    error_log('CMSC 340 database connection failed: ' . $e->getMessage());
    http_response_code(500);

    $serverName = $_SERVER['SERVER_NAME'] ?? '';
    if (in_array($serverName, ['localhost', '127.0.0.1'], true)) {
        exit('Local database connection failed: ' .
            escapeHtml($e->getMessage()));
    }

    exit('Database connection failed. Check your local database configuration.');
}

$formError = '';
$courseCode = '';
$courseTitle = '';
$creditHoursInput = '';

// Show a one-time message saved before a successful redirect.
$statusMessage = $_SESSION['course_status'] ?? '';
unset($_SESSION['course_status']);

// Process the form only when it is submitted using POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course'])) {
    $submittedToken = $_POST['csrf_token'] ?? '';

    if (!is_string($submittedToken) ||
        !hash_equals($_SESSION['csrf_token'], $submittedToken)) {
        $formError = 'Your form session expired or could not be verified. Please try again.';
    } else {
        $courseCode = trim(is_string($_POST['course_code'] ?? null) ? $_POST['course_code'] : '');
        $courseTitle = trim(is_string($_POST['course_title'] ?? null) ? $_POST['course_title'] : '');
        $creditHoursInput = is_string($_POST['course_credit_hours'] ?? null)
            ? $_POST['course_credit_hours']
            : '';

        $creditHours = filter_var($creditHoursInput, FILTER_VALIDATE_INT);

        // Validate input on the server; browser-side rules can be bypassed.
        if ($courseCode === '' || strlen($courseCode) > 20) {
            $formError = 'Enter a course code that is no more than 20 characters.';
        } elseif ($courseTitle === '' || strlen($courseTitle) > 120) {
            $formError = 'Enter a course title that is no more than 120 characters.';
        } elseif ($creditHours === false || $creditHours < 1 || $creditHours > 10) {
            $formError = 'Credit hours must be a whole number from 1 to 10.';
        } else {
            try {
                $sql = "INSERT INTO mycourses
                        (course_code, course_title, course_credit_hours)
                        VALUES (:course_code, :course_title, :course_credit_hours)";
                $statement = $pdo->prepare($sql);
                $statement->execute([
                    ':course_code' => $courseCode,
                    ':course_title' => $courseTitle,
                    ':course_credit_hours' => $creditHours,
                ]);

                $_SESSION['course_status'] = 'Course added successfully.';
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;
            } catch (PDOException $e) {
                error_log('CMSC 340 course insert failed: ' . $e->getMessage());
                $formError = 'The course could not be added. Check the details and try again.';
            }
        }
    }
}

// Retrieve courses in a consistent order.
$statement = $pdo->query(
    "SELECT course_code, course_title, course_credit_hours
     FROM mycourses
     ORDER BY course_code ASC"
);
$courses = $statement->fetchAll();

// Calculate the total credit hours from the database results.
$totalCreditHours = 0;
foreach ($courses as $course) {
    $totalCreditHours += (int) $course['course_credit_hours'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PDO Course Database</title>
    <link rel="stylesheet" href="../design.css">
</head>
<body>
    <main class="container coursework-page assignment-four">
        <header>
            <h1>PDO Course Database</h1>
            <p>Add a course and review the courses stored in the database.</p>
        </header>

        <?php if ($statusMessage !== ''): ?>
            <p class="assignment-four-message success" role="status">
                <?= escapeHtml($statusMessage) ?>
            </p>
        <?php endif; ?>

        <?php if ($formError !== ''): ?>
            <p class="assignment-four-message error" role="alert">
                <?= escapeHtml($formError) ?>
            </p>
        <?php endif; ?>

        <section>
            <h2>Add a Course</h2>
            <form class="assignment-four-form" method="post">
                <input type="hidden" name="csrf_token" value="<?= escapeHtml($_SESSION['csrf_token']) ?>">

                <div class="assignment-four-field">
                    <label for="course_code">Course code</label>
                    <input
                        type="text"
                        id="course_code"
                        name="course_code"
                        maxlength="20"
                        value="<?= escapeHtml($courseCode) ?>"
                        autocomplete="off"
                        required
                    >
                </div>

                <div class="assignment-four-field">
                    <label for="course_title">Course title</label>
                    <input
                        type="text"
                        id="course_title"
                        name="course_title"
                        maxlength="120"
                        value="<?= escapeHtml($courseTitle) ?>"
                        required
                    >
                </div>

                <div class="assignment-four-field">
                    <label for="course_credit_hours">Credit hours</label>
                    <input
                        type="number"
                        id="course_credit_hours"
                        name="course_credit_hours"
                        min="1"
                        max="10"
                        step="1"
                        value="<?= escapeHtml($creditHoursInput) ?>"
                        required
                    >
                </div>

                <button type="submit" name="add_course" value="1">Add Course</button>
            </form>
        </section>

        <section>
            <h2>Course List</h2>
            <p class="assignment-four-count">
                <?= count($courses) ?> <?= count($courses) === 1 ? 'course' : 'courses' ?> in the database
            </p>

            <?php if ($courses === []): ?>
                <p class="assignment-four-empty">No courses have been added yet.</p>
            <?php else: ?>
                <div class="assignment-four-table-wrap">
                    <table class="assignment-four-table">
                        <thead>
                            <tr>
                                <th scope="col">Course Code</th>
                                <th scope="col">Course Title</th>
                                <th scope="col">Credit Hours</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td><?= escapeHtml($course['course_code']) ?></td>
                                    <td><?= escapeHtml($course['course_title']) ?></td>
                                    <td><?= (int) $course['course_credit_hours'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th scope="row" colspan="2">Total credit hours</th>
                                <td><?= $totalCreditHours ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <footer>CMSC 340 · Web Programming</footer>
    </main>
</body>
</html>
