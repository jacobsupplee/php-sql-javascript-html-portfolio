<?php
// CMSC 340: PHP PDO MyCourses Table
// Database credentials are stored in the ignored local config file.
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
    exit('Database connection failed. Check your local database configuration.');
}

// Add a course using a prepared statement.
if (isset($_POST['add_course'])) {
    $courseCode = trim($_POST['course_code'] ?? '');
    $courseTitle = trim($_POST['course_title'] ?? '');
    $creditHoursInput = $_POST['course_credit_hours'] ?? '';

    $creditHours = filter_var($creditHoursInput, FILTER_VALIDATE_INT);

    if ($courseCode !== '' && $courseTitle !== '' &&
        $creditHours !== false && $creditHours > 0) {
        $sql = "INSERT INTO mycourses
                (course_code, course_title, course_credit_hours)
                VALUES (:course_code, :course_title, :course_credit_hours)";
        $statement = $pdo->prepare($sql);
        $statement->execute([
            ':course_code' => $courseCode,
            ':course_title' => $courseTitle,
            ':course_credit_hours' => $creditHours,
        ]);
    } else {
        $formError = 'Enter a course code, course title, and positive whole-number credit hours.';
    }
}

$statement = $pdo->query(
    "SELECT course_code, course_title, course_credit_hours FROM mycourses"
);
$courses = $statement->fetchAll();

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
    <title>My UMGC Courses</title>
</head>
<body>
<h1>My UMGC Courses</h1>

<?php if (!empty($formError)): ?>
    <p role="alert"><?php echo htmlspecialchars($formError, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>

<form method="post">
    <label for="course_code">Course Code:</label>
    <input type="text" id="course_code" name="course_code" required>

    <br><br>

    <label for="course_title">Course Title:</label>
    <input type="text" id="course_title" name="course_title" required>

    <br><br>

    <label for="course_credit_hours">Course Credit Hours:</label>
    <input type="number" id="course_credit_hours" name="course_credit_hours"
           min="1" step="1" required>

    <br><br>

    <button type="submit" name="add_course" value="1">Add Course</button>
</form>

<h2>My Courses</h2>
<table border="1">
    <thead>
        <tr>
            <th>Course Code</th>
            <th>Course Title</th>
            <th>Course Credit Hours</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($courses as $course): ?>
        <tr>
            <td><?php echo htmlspecialchars($course['course_code'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($course['course_title'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo (int) $course['course_credit_hours']; ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<p>
    Total credit hours of my UMGC courses over the last 3 terms =
    <?php echo $totalCreditHours; ?>
</p>
</body>
</html>
