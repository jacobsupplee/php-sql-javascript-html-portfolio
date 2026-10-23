// Client-side checks improve feedback, but server-validation.php must validate again.
function validate(form) {
    const errors = [
        validateForename(form.elements.forename.value),
        validateSurname(form.elements.surname.value),
        validateUsername(form.elements.username.value),
        validatePassword(form.elements.password.value),
        validateAge(form.elements.age.value),
        validateEmail(form.elements.email.value)
    ].filter(Boolean);

    if (errors.length > 0) {
        alert(errors.join("\n"));
        return false;
    }

    return true;
}

function validateForename(field) {
    const forename = field.trim();

    if (forename === "") return "Enter your first name.";
    if (forename.length > 32) return "First names must be 32 characters or fewer.";
    return "";
}

function validateSurname(field) {
    const surname = field.trim();

    if (surname === "") return "Enter your last name.";
    if (surname.length > 32) return "Last names must be 32 characters or fewer.";
    return "";
}

function validateUsername(field) {
    const username = field.trim();

    if (username === "") {
        return "Enter a username.";
    }
    if (username.length < 5 || username.length > 16) {
        return "Usernames must be between 5 and 16 characters.";
    }
    if (!/^[a-zA-Z0-9_-]+$/.test(username)) {
        return "Usernames may contain only letters, numbers, hyphens, and underscores.";
    }

    return "";
}

function validatePassword(field) {
    // PHP currently trims submitted fields, so match that behavior here.
    const password = field.trim();

    if (password === "") {
        return "Enter a password.";
    }
    if (password.length < 8 || password.length > 128) {
        return "Passwords must be between 8 and 128 characters.";
    }
    if (!/[a-z]/.test(password) ||
        !/[A-Z]/.test(password) ||
        !/[0-9]/.test(password)) {
        return "Passwords must include a lowercase letter, an uppercase letter, and a number.";
    }

    return "";
}

function validateAge(field) {
    const ageText = field.trim();

    if (!/^\d+$/.test(ageText)) {
        return "Enter your age as a whole number.";
    }

    const age = Number(ageText);
    if (age < 18 || age > 110) {
        return "Age must be between 18 and 110.";
    }

    return "";
}

function validateEmail(field) {
    const email = field.trim();

    if (email.length > 254 || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        return "Enter a valid email address.";
    }

    return "";
}
