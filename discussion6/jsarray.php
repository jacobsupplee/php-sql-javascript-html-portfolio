<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discussion 6 - JavaScript Arrays</title>
    <link rel="stylesheet" href="../design.css">
</head>

<body>
    <div class="container">
        <header>
            <h1>Discussion 6: JavaScript Arrays</h1>
            <p>Storing and Retrieving Course Information</p>
        </header>

        <main>
            <section>
                <h2>JavaScript Object (Associative Array)</h2>
                <div id="associativeArray"></div>
            </section>

            <section>
                <h2>JavaScript Array of Course Objects</h2>
                <div id="courseObjects"></div>
            </section>
        </main>

        <footer>
            CMSC 340 · Web Programming
        </footer>
    </div>

    <script>
        // Reusable functions keep the rendering code consistent.
        function addCourseRow(container, courseCode, creditHours) {
            const courseRow = document.createElement("p");
            courseRow.textContent = courseCode + " : " + creditHours;
            container.appendChild(courseRow);
        }

        function addCreditTotal(container, total) {
            const totalRow = document.createElement("p");
            totalRow.textContent =
                "Total credit hours over the last 3 terms = " + total;
            container.appendChild(totalRow);
        }

        // Each course code is a property; its credit hours are the value.
        const courses = {
            "CSC223": 4,
            "CSC222": 4,
            "MTH264": 4,
            "MTH288": 3,
            "MTH263": 4,
            "CSC205": 3,
            "GOL106": 4,
            "GOL105": 4,
            "CMIT265": 3,
            "CMSC105": 3,
            "PACE111T": 3
        };

        const associativeArraySection = document.getElementById("associativeArray");
        let totalCredits = 0;

        // for...in loops through an object's property names.
        for (const courseCode in courses) {
            addCourseRow(associativeArraySection, courseCode, courses[courseCode]);
            totalCredits += courses[courseCode];
        }

        addCreditTotal(associativeArraySection, totalCredits);

        // A Course object stores a course code and its credit hours together.
        class Course {
            constructor(courseCode, creditHours) {
                this.courseCode = courseCode;
                this.creditHours = creditHours;
            }
        }

        // An array containing Course objects.
        const courseObjArray = [
            new Course("CSC223", 4),
            new Course("CSC222", 4),
            new Course("MTH264", 4),
            new Course("MTH288", 3),
            new Course("MTH263", 4),
            new Course("CSC205", 3),
            new Course("GOL106", 4),
            new Course("GOL105", 4),
            new Course("CMIT265", 3),
            new Course("CMSC105", 3),
            new Course("PACE111T", 3)
        ];

        const courseObjectsSection = document.getElementById("courseObjects");
        let totalObjCredits = 0;

        // for...of loops through the array's values (each value is a Course object).
        for (const course of courseObjArray) {
            addCourseRow(courseObjectsSection, course.courseCode, course.creditHours);
            totalObjCredits += course.creditHours;
        }

        addCreditTotal(courseObjectsSection, totalObjCredits);
    </script>
</body>
</html>
