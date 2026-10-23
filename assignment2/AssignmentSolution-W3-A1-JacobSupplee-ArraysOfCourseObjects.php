<?php
// A Course object stores the information for one course.
class Course
{
    private $courseCode;
    private $courseTitle;
    private $creditHours;

    public function __construct($courseCode, $courseTitle, $creditHours)
    {
        $this->courseCode = $courseCode;
        $this->courseTitle = $courseTitle;
        $this->creditHours = $creditHours;
    }

    public function getCourseCode()
    {
        return $this->courseCode;
    }

    public function getCourseTitle()
    {
        return $this->courseTitle;
    }

    public function getCreditHours()
    {
        return $this->creditHours;
    }
}

// My courses from the last three terms.
$arrayOfCourseObjects = array(
    new Course("CMIT 265", "Fundamentals of Networking", 3),
    new Course("CMSC 105", "Intro to Problem Solving and Algorithm Design", 3),
    new Course("PACE 111T", "Program & Career Exploration in Technology", 3),
    new Course("CSC 205", "Computer Organization", 3),
    new Course("GOL 106", "Historical Geology", 4),
    new Course("GOL 105", "Physical Geology", 4),
    new Course("CSC 223", "Data Structures & Analysis of Algorithms", 4),
    new Course("CSC 222", "Object Oriented Programming", 4),
    new Course("MTH 264", "Calculus II", 4),
    new Course("MTH 288", "Discrete Mathematics", 3),
    new Course("MTH 263", "Calculus I", 4)
);

function escapeHtml($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

$totalCreditHours = 0;
foreach ($arrayOfCourseObjects as $course) {
    $totalCreditHours += $course->getCreditHours();
}

$cmsc115Taken = false;
foreach ($arrayOfCourseObjects as $course) {
    if ($course->getCourseCode() === "CMSC 115") {
        $cmsc115Taken = true;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Assignment 2: PHP Array of Course Objects</title>
    <link rel="stylesheet" href="../design.css">
</head>
<body>
    <main class="container coursework-page assignment-two">
        <header>
            <h1>Assignment 2</h1>
            <p>PHP array of course objects</p>
        </header>

        <section>
            <h2>Course Summary</h2>
            <div class="assignment-two-summary">
                <div>
                    <span>Courses</span>
                    <strong><?= count($arrayOfCourseObjects) ?></strong>
                </div>
                <div>
                    <span>Total credit hours</span>
                    <strong><?= $totalCreditHours ?></strong>
                </div>
            </div>
        </section>

        <section>
            <h2>My Courses</h2>
            <div class="assignment-two-table-wrap">
                <table class="assignment-two-table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Course Code</th>
                            <th scope="col">Course Title</th>
                            <th scope="col">Credits</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($arrayOfCourseObjects as $index => $course): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= escapeHtml($course->getCourseCode()) ?></td>
                                <td><?= escapeHtml($course->getCourseTitle()) ?></td>
                                <td><?= (int) $course->getCreditHours() ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section>
            <h2>CMSC 115 Requirement</h2>
            <p class="assignment-two-answer">
                <?= $cmsc115Taken
                    ? "Yes, I have taken the CMSC 115 course."
                    : "No, I have not taken the CMSC 115 course." ?>
            </p>
        </section>

        <footer>CMSC 340 · Web Programming</footer>
    </main>
</body>
</html>
