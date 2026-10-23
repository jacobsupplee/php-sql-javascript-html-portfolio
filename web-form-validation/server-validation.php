<?php
// Server-side form validation demonstration.

function escape_output(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function validate_forename(string $field): string
{
    if (trim($field) === '') {
        return 'Enter your first name.';
    }
    if (strlen($field) > 32) {
        return 'First names must be 32 characters or fewer.';
    }
    return '';
}

function validate_surname(string $field): string
{
    if (trim($field) === '') {
        return 'Enter your last name.';
    }
    if (strlen($field) > 32) {
        return 'Last names must be 32 characters or fewer.';
    }
    return '';
}

function validate_username(string $field): string
{
    if ($field === '') {
        return 'Enter a username.';
    }
    if (strlen($field) < 5 || strlen($field) > 16) {
        return 'Usernames must be between 5 and 16 characters.';
    }
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $field)) {
        return 'Usernames may contain only letters, numbers, hyphens, and underscores.';
    }
    return '';
}

function validate_password(string $field): string
{
    if ($field === '') {
        return 'Enter a password.';
    }
    if (strlen($field) < 8 || strlen($field) > 128) {
        return 'Passwords must be between 8 and 128 characters.';
    }
    if (!preg_match('/[a-z]/', $field) ||
        !preg_match('/[A-Z]/', $field) ||
        !preg_match('/[0-9]/', $field)) {
        return 'Passwords must include a lowercase letter, an uppercase letter, and a number.';
    }
    return '';
}

function validate_age(string $field): string
{
    if ($field === '' || filter_var($field, FILTER_VALIDATE_INT) === false) {
        return 'Enter your age as a whole number.';
    }
    $age = (int) $field;
    if ($age < 18 || $age > 110) {
        return 'Age must be between 18 and 110.';
    }
    return '';
}

function validate_email(string $field): string
{
    if (strlen($field) > 254) {
        return 'Email addresses must be 254 characters or fewer.';
    }
    if ($field === '' || filter_var($field, FILTER_VALIDATE_EMAIL) === false) {
        return 'Enter a valid email address.';
    }
    return '';
}

$fields = ['forename', 'surname', 'username', 'password', 'age', 'email'];
$values = array_fill_keys($fields, '');
$errors = [];
$submitted = $_SERVER['REQUEST_METHOD'] === 'POST';
$success = false;

if ($submitted) {
    foreach ($fields as $field) {
        $rawValue = $_POST[$field] ?? '';
        if (!is_string($rawValue)) {
            $errors[] = 'Invalid form submission. Please use the form below.';
            continue;
        }
        $values[$field] = trim($rawValue);
    }

    // Validate only if all submitted fields had the expected scalar type.
    if (!$errors) {
        $validators = [
            'forename' => 'validate_forename',
            'surname' => 'validate_surname',
            'username' => 'validate_username',
            'password' => 'validate_password',
            'age' => 'validate_age',
            'email' => 'validate_email',
        ];

        foreach ($validators as $field => $validator) {
            $error = $validator($values[$field]);
            if ($error !== '') {
                $errors[] = $error;
            }
        }
    }

    if (!$errors) {
        $success = true;
        // This coursework demo validates input only; it does not create an account.
        // Never display, log, or repopulate the submitted password.
        $values['password'] = '';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Server-Side Form Validation</title>
    <link rel="stylesheet" href="../design.css">
    <style>
        .signup-panel {
            max-width: 760px;
            margin: 2rem auto;
        }

        .signup-panel form {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .signup-panel .field {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
            min-width: 0;
        }

        .signup-panel label {
            font-weight: 700;
        }

        .signup-panel input {
            width: 100%;
            min-height: 2.75rem;
            padding: 0.65rem 0.75rem;
            border: 1px solid #b9b3c2;
            border-radius: 0.5rem;
            font: inherit;
        }

        .signup-panel input:focus-visible,
        .signup-panel button:focus-visible {
            outline: 3px solid #2764c5;
            outline-offset: 2px;
        }

        .signup-panel .form-actions {
            grid-column: 1 / -1;
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: center;
        }

        .signup-panel button {
            min-height: 2.75rem;
            padding: 0.65rem 1rem;
            border: 1px solid #667f7b;
            border-radius: 0.5rem;
            color: white;
            background: #667f7b;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }

        .signup-panel button:hover {
            background: #526a66;
        }

        .signup-panel .notice {
            padding: 1rem 1.1rem;
            margin: 1rem 0;
            border-radius: 0.6rem;
        }

        .signup-panel .notice-success {
            color: #185c37;
            background: #edf8f0;
            border: 1px solid #8dc9a1;
        }

        .signup-panel .notice-error {
            color: #8f2020;
            background: #fff0ef;
            border: 1px solid #e7a5a1;
        }

        .signup-panel .notice ul {
            list-style: disc;
            margin: 0.5rem 0 0 1.25rem;
        }

        .signup-panel .hint {
            margin: 0;
            color: #656170;
            font-size: 0.9rem;
        }

        @media (max-width: 640px) {
            .signup-panel form {
                grid-template-columns: minmax(0, 1fr);
            }

            .signup-panel .form-actions {
                grid-column: 1;
            }
        }
    </style>
    <script>
        function validateSignup(form) {
            const errors = [];
            const forename = form.elements.forename.value.trim();
            const surname = form.elements.surname.value.trim();
            const username = form.elements.username.value.trim();
            const password = form.elements.password.value;
            const age = form.elements.age.value.trim();
            const email = form.elements.email.value.trim();

            if (!forename) errors.push('Enter your first name.');
            if (!surname) errors.push('Enter your last name.');
            if (username.length < 5) {
                errors.push('Usernames must be at least 5 characters.');
            } else if (!/^[a-zA-Z0-9_-]+$/.test(username)) {
                errors.push('Usernames may contain only letters, numbers, hyphens, and underscores.');
            }

            if (password.length < 8 ||
                !/[a-z]/.test(password) ||
                !/[A-Z]/.test(password) ||
                !/[0-9]/.test(password)) {
                errors.push('Passwords must be at least 8 characters and include lowercase, uppercase, and numeric characters.');
            }

            if (!/^\d+$/.test(age) || Number(age) < 18 || Number(age) > 110) {
                errors.push('Age must be a whole number between 18 and 110.');
            }

            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                errors.push('Enter a valid email address.');
            }

            if (errors.length) {
                alert(errors.join('\n'));
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <main class="container signup-panel">
        <header>
            <h1>Server-Side Form Validation</h1>
            <p>Server-side PHP validation for submitted form data</p>
        </header>

        <section>
            <h2>Create an Account (Validation Demo)</h2>
            <p>This demonstration validates form input in the browser and again on the server. It does not create or store an account.</p>

            <?php if ($success): ?>
                <div class="notice notice-success" role="status">
                    <strong>Form data successfully validated.</strong>
                    <p>All fields passed server-side validation. The password is intentionally not displayed.</p>
                    <p><strong>Name:</strong> <?= escape_output($values['forename']) ?> <?= escape_output($values['surname']) ?></p>
                    <p><strong>Username:</strong> <?= escape_output($values['username']) ?></p>
                    <p><strong>Age:</strong> <?= escape_output($values['age']) ?></p>
                    <p><strong>Email:</strong> <?= escape_output($values['email']) ?></p>
                </div>
            <?php elseif ($errors): ?>
                <div class="notice notice-error" role="alert">
                    <strong>Please correct the following:</strong>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= escape_output($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
                <form method="post" action="server-validation.php" onsubmit="return validateSignup(this)">
                    <div class="field">
                        <label for="forename">First name</label>
                        <input id="forename" type="text" name="forename" maxlength="32"
                               autocomplete="given-name" required
                               value="<?= escape_output($values['forename']) ?>">
                    </div>
                    <div class="field">
                        <label for="surname">Last name</label>
                        <input id="surname" type="text" name="surname" maxlength="32"
                               autocomplete="family-name" required
                               value="<?= escape_output($values['surname']) ?>">
                    </div>
                    <div class="field">
                        <label for="username">Username</label>
                        <input id="username" type="text" name="username" minlength="5" maxlength="16"
                               pattern="[a-zA-Z0-9_-]+" autocomplete="username" required
                               value="<?= escape_output($values['username']) ?>">
                        <p class="hint">At least 5 characters; letters, numbers, _ and - only.</p>
                    </div>
                    <div class="field">
                        <label for="password">Password</label>
                        <input id="password" type="password" name="password" minlength="8" maxlength="128"
                               autocomplete="new-password" required>
                        <p class="hint">At least 8 characters, including uppercase, lowercase, and a number.</p>
                    </div>
                    <div class="field">
                        <label for="age">Age</label>
                        <input id="age" type="number" name="age" min="18" max="110" step="1"
                               inputmode="numeric" required value="<?= escape_output($values['age']) ?>">
                    </div>
                    <div class="field">
                        <label for="email">Email address</label>
                        <input id="email" type="email" name="email" maxlength="254"
                               autocomplete="email" required value="<?= escape_output($values['email']) ?>">
                    </div>
                    <div class="form-actions">
                        <button type="submit">Validate signup</button>
                    </div>
                </form>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
