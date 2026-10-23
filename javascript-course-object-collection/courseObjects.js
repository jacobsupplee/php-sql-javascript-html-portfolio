// Defines the Course class.
class Course {
    constructor(courseCode, courseTitle, creditHours) {
        this.courseCode = courseCode;
        this.courseTitle = courseTitle;
        this.creditHours = creditHours;
    }
}

// Create an array of course objects.
const arrayOfCourseObjects = [
    new Course("CSC 223", "Data Structures & Analysis Of Algorithms", 4),
    new Course("CSC 222", "Object-Oriented Programming", 4),
    new Course("MTH 264", "Calculus II", 4),
    new Course("MTH 288", "Discrete Mathematics", 3),
    new Course("MTH 263", "Calculus I", 4),
    new Course("CSC 205", "Computer Organization", 3),
    new Course("GOL 106", "Historical Geology", 4),
    new Course("GOL 105", "Physical Geology", 4),
    new Course("CMIT 265", "Fundamentals Of Networking", 3),
    new Course("CMSC 105", "Introduction To Problem Solving And Algorithm Design", 3),
    new Course("PACE 111T", "Programming & Career Exploration In Technology", 3)
];

// Display the number of courses.
document.getElementById("course-count").textContent =
    "The number of UMGC/NOVA courses I took over the last 3 terms = " +
    arrayOfCourseObjects.length;

// Build the table rows with DOM methods.
const courseTableBody = document.getElementById("course-table-body");

arrayOfCourseObjects.forEach((course, index) => {
    const row = document.createElement("tr");

    const numberCell = document.createElement("td");
    numberCell.textContent = index + 1;
    row.appendChild(numberCell);

    const codeCell = document.createElement("td");
    codeCell.textContent = course.courseCode;
    row.appendChild(codeCell);

    const titleCell = document.createElement("td");
    titleCell.textContent = course.courseTitle;
    row.appendChild(titleCell);

    const creditsCell = document.createElement("td");
    creditsCell.textContent = course.creditHours;
    row.appendChild(creditsCell);

    courseTableBody.appendChild(row);
});

// Calculate total credit hours from the course objects.
const totalCreditHours = arrayOfCourseObjects.reduce(
    (total, course) => total + course.creditHours,
    0
);
document.getElementById("total-credits").textContent = totalCreditHours;

// Check whether any course has the code CMSC 115.
const cmsc115Taken = arrayOfCourseObjects.some(
    course => course.courseCode === "CMSC 115"
);

document.getElementById("cmsc115-result").textContent = cmsc115Taken
    ? "Yes, I have taken the CMSC 115 course."
    : "No, I have not taken the CMSC 115 course.";
