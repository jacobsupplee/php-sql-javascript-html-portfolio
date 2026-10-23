<?php
$name = "Jacob Supplee";
$city = "Rockville";
$state = "Maryland";

// Sample value for the assignment; this is not live weather data.
$temperature = 75;
$currentDate = date("F j, Y");

// Approximate Earth's mean radius and the volume of a sphere.
$pi = M_PI;
$earthRadius = 3959; // miles
$earthVolume = (4 / 3) * $pi * ($earthRadius ** 3);

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
                <div><dt>Example temperature</dt><dd><?= (int) $temperature ?>°F <span class="section-intro">(sample value, not live weather)</span></dd></div>
                <div><dt>Date generated</dt><dd><?= escapeHtml($currentDate) ?></dd></div>
            </dl>
        </section>

        <section>
            <h2>Earth's Volume</h2>
            <p class="calculation-label">Volume of a sphere</p>
            <p class="formula">V = (4 / 3) × π × r³</p>
            <p class="section-intro">Earth is not a perfect sphere, so this is an estimate using its approximate mean radius.</p>
            <div class="calculation-result">
                <span>Radius used</span>
                <strong><?= number_format($earthRadius) ?> miles</strong>
            </div>
            <div class="calculation-result">
                <span>Calculated volume</span>
                <strong><?= number_format($earthVolume, 2) ?> cubic miles</strong>
            </div>
            <p class="section-intro">The calculation uses PHP's built-in M_PI constant and the formula for the volume of a sphere.</p>
        </section>

        <footer>CMSC 340 · Web Programming</footer>
    </main>
</body>
</html>
