<?php
$name = "Jacob Supplee";
$city = "Rockville";
$state = "Maryland";
$temperature = 75;
$currentDate = date("m/d/y");

$pi = 3.14;
$earthRadius = 3959;
$earthVolume = (4 / 3) * $pi * $earthRadius ** 3;

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
    <title>Assignment 1: Personal Information and Earth's Volume</title>
    <link rel="stylesheet" href="../design.css">
</head>
<body>
    <main class="container coursework-page assignment-one">
        <header>
            <h1>Assignment 1</h1>
            <p>Personal information and a calculation of Earth's volume</p>
        </header>

        <section>
            <h2>About Me</h2>
            <dl class="profile-details">
                <div><dt>Name</dt><dd><?= escapeHtml($name) ?></dd></div>
                <div><dt>City</dt><dd><?= escapeHtml($city) ?></dd></div>
                <div><dt>State</dt><dd><?= escapeHtml($state) ?></dd></div>
                <div><dt>Current temperature</dt><dd><?= (int) $temperature ?>°F</dd></div>
                <div><dt>Today's date</dt><dd><?= escapeHtml($currentDate) ?></dd></div>
            </dl>
        </section>

        <section>
            <h2>Earth's Volume</h2>
            <p class="calculation-label">Sphere volume formula</p>
            <p class="formula">(4 / 3) × π × radius³</p>
            <div class="calculation-result">
                <span>Radius used</span>
                <strong><?= number_format($earthRadius) ?> miles</strong>
            </div>
            <div class="calculation-result">
                <span>Calculated volume</span>
                <strong><?= number_format($earthVolume, 2) ?> cubic miles</strong>
            </div>
            <p class="section-intro">This estimate uses π = 3.14 and an Earth radius of 3,959 miles.</p>
        </section>

        <footer>CMSC 340 · Web Programming</footer>
    </main>
</body>
</html>
