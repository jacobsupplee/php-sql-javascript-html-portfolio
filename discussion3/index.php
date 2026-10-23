<?php
// Associative array of course codes and credit hours.
$associativeCourses = [
    "CSC 223" => 4,
    "CSC 222" => 4,
    "MTH 264" => 4,
    "MTH 288" => 3,
    "MTH 263" => 4,
    "CSC 205" => 3,
    "GOL 106" => 4,
    "GOL 105" => 4,
    "CMIT 265" => 3,
    "CMSC 105" => 3,
    "PACE 111T" => 3,
];

$totalCredits = array_sum($associativeCourses);

class Course
{
    public $courseCode;
    public $creditHours;

    public function __construct($courseCode, $creditHours)
    {
        $this->courseCode = $courseCode;
        $this->creditHours = $creditHours;
    }
}

// A second array stores equivalent data as Course objects.
$objectCourses = [
    new Course("CSC 223", 4),
    new Course("CSC 222", 4),
    new Course("MTH 264", 4),
    new Course("MTH 288", 3),
    new Course("MTH 263", 4),
    new Course("CSC 205", 3),
    new Course("GOL 106", 4),
    new Course("GOL 105", 4),
    new Course("CMIT 265", 3),
    new Course("CMSC 105", 3),
    new Course("PACE 111T", 3),
];

$objectTotalCredits = 0;
foreach ($objectCourses as $course) {
    $objectTotalCredits += $course->creditHours;
}

function escapeHtml($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Discussion 3: Transcript Generator</title>
    <link rel="stylesheet" href="../design.css">
</head>
<body>
    <main class="container coursework-page transcript-page">
        <header>
            <h1>Transcript Generator</h1>
            <p>Comparing associative arrays with arrays of Course objects</p>
        </header>

        <section>
            <h2>Course List: Associative Array</h2>
            <p class="section-intro">Each course code maps to its credit-hour value.</p>
            <div class="course-list">
                <?php foreach ($associativeCourses as $courseCode => $creditHours): ?>
                    <div class="course-row">
                        <span class="course-code"><?= escapeHtml($courseCode) ?></span>
                        <span class="credit-value"><?= (int) $creditHours ?> credits</span>
                    </div>
                <?php endforeach; ?>
            </div>
            <p class="credit-total">Total credit hours <strong><?= (int) $totalCredits ?></strong></p>
        </section>

        <section>
            <h2>Course List: Course Objects</h2>
            <p class="section-intro">Each Course object stores a course code and its credit hours.</p>
            <div class="course-list">
                <?php foreach ($objectCourses as $course): ?>
                    <div class="course-row">
                        <span class="course-code"><?= escapeHtml($course->courseCode) ?></span>
                        <span class="credit-value"><?= (int) $course->creditHours ?> credits</span>
                    </div>
                <?php endforeach; ?>
            </div>
            <p class="credit-total">Total credit hours <strong><?= (int) $objectTotalCredits ?></strong></p>
        </section>

        <footer>CMSC 340 · Web Programming</footer>
    </main>
</body>
</html>
