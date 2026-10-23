<?php

// Define the User class.
class User
{
    private $name;
    private $city;
    private $state;
    private $temperature;
    private $todayDate;

    public function __construct($name, $city, $state, $temperature, $todayDate)
    {
        $this->name = $name;
        $this->city = $city;
        $this->state = $state;
        $this->temperature = $temperature;
        $this->todayDate = $todayDate;
    }

    private function escape($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public function printUser()
    {
        echo '<article class="profile-card">';
        echo '<h2>' . $this->escape($this->name) . '</h2>';
        echo '<dl class="profile-details">';
        echo '<div><dt>City</dt><dd>' . $this->escape($this->city) . '</dd></div>';
        echo '<div><dt>State</dt><dd>' . $this->escape($this->state) . '</dd></div>';
        echo '<div><dt>Temperature</dt><dd>' . $this->escape($this->temperature) . '</dd></div>';
        echo '<div><dt>Date</dt><dd>' . $this->escape($this->todayDate) . '</dd></div>';
        echo '</dl>';
        echo '</article>';
    }
}

// Two distinct User objects demonstrate the class.
$user1 = new User("Jacob", "Rockville", "Maryland", "79°F", "August 24, 2026");
$user2 = new User("Wilma", "Leesburg", "Virginia", "76°F", "August 24, 2026");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PHP User Profile Objects</title>
    <link rel="stylesheet" href="../design.css">
</head>
<body>
    <main class="container coursework-page discussion-two">
        <header>
            <h1>PHP User Profile Objects</h1>
            <p>Using a PHP class to store and display profile information</p>
        </header>
        <section>
            <h2>User Profiles</h2>
            <div class="profile-grid">
                <?php $user1->printUser(); ?>
                <?php $user2->printUser(); ?>
            </div>
        </section>
        <footer>CMSC 340 · Web Programming</footer>
    </main>
</body>
</html>
