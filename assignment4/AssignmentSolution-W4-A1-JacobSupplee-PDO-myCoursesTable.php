<?php
// CMSC 340: PHP PDO MyCourses Table
// Jacob Supplee
// Uses local database settings stored in config.local.php

//Loads the connection settings for the CMSC 340 database
$config = require __DIR__ . '/../config.local.php';
$database = $config['cmsc340'];

//Stores the database connection information
$host = $database['host'];
$port = $database['port'];
$dbName = $database['database'];
$username = $database['username'];
$password = $database['password'];

try {
    //Creates the PDO connection to the CMSC 340 database
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbName;charset=utf8mb4",
        $username,
        $password,
        [
            //Enables error handling and associative array results
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    //Log details for troubleshooting; show them only during local development.
    error_log('CMSC 340 database connection failed: ' . $e->getMessage());
    http_response_code(500);

    $serverName = $_SERVER['SERVER_NAME'] ?? '';
    if (in_array($serverName, ['localhost', '127.0.0.1'], true)) {
        exit('Local database connection failed: ' .
            htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
    }

    exit('Database connection failed. Check your local database configuration.');
}

//Adds a course when the form is submitted
if (isset($_POST['add_course'])) {
    //Trims extra spaces and reads the submitted form values
    $courseCode = trim($_POST['course_code'] ?? '');
    $courseTitle = trim($_POST['course_title'] ?? '');
    $creditHoursInput = $_POST['course_credit_hours'] ?? '';

    //Checks that credit hours are entered as a whole number
    $creditHours = filter_var($creditHoursInput, FILTER_VALIDATE_INT);

    //Validates all fields before inserting a new course
    if ($courseCode !== '' && $courseTitle !== '' &&
        $creditHours !== false && $creditHours > 0) {

        //Uses placeholders so submitted values are not treated as SQL code
        $sql = "INSERT INTO mycourses
                (course_code, course_title, course_credit_hours)
                VALUES (:course_code, :course_title, :course_credit_hours)";
        $statement = $pdo->prepare($sql);

        //Executes the insert with the values from the form
        $statement->execute([
            ':course_code' => $courseCode,
            ':course_title' => $courseTitle,
            ':course_credit_hours' => $creditHours,
        ]);
    } else {
        //Displays a validation message if any field is invalid
        $formError = 'Enter a course code, course title, and positive whole-number credit hours.';
    }
}

//Retrieves the course records to display in the table
$statement = $pdo->query(
    "SELECT course_code, course_title, course_credit_hours FROM mycourses"
);
$courses = $statement->fetchAll();

//Calculates the total credit hours across the retrieved courses
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

<?php
//Shows the validation message when submitted form data is invalid
if (!empty($formError)):
?>
    <p role="alert"><?php echo htmlspecialchars($formError, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>

<!-- Form for entering a new course -->
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

<!-- Displays the courses returned by the database query -->
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
            <!-- Escapes text before displaying it in the page -->
            <td><?php echo htmlspecialchars($course['course_code'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($course['course_title'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo (int) $course['course_credit_hours']; ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<!-- Displays the total credit hours calculated above -->
<p>
    Total credit hours of my UMGC courses over the last 3 terms =
    <?php echo $totalCreditHours; ?>
</p>
</body>
</html>
