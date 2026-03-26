const form = document.getElementById("registerForm");

const passwordBox = document.getElementById("password");
const rulesBox = document.getElementById("passwordRules");

/* CLEAN SPACES */
function cleanText(text) {
    return text.replace(/\s+/g, " ").trim();
}

/* PHONE INPUT (ONLY DIGITS, MAX 10) */
document.getElementById("phone").addEventListener("input", function () {
    this.value = this.value.replace(/[^0-9]/g, "");
    if (this.value.length > 10) {
        this.value = this.value.slice(0, 10);
    }
});

/* PASSWORD LIVE CHECK */
passwordBox.addEventListener("input", function () {

    let pass = passwordBox.value;
    let message = "";

    if (pass.length < 8) {
        message += "<span class='rule invalid'>• Minimum 8 characters required</span><br>";
    }

    if (!/[#@$]/.test(pass)) {
        message += "<span class='rule invalid'>• Add a special character (@ # $)</span><br>";
    }

    if (!/[0-9]/.test(pass)) {
        message += "<span class='rule invalid'>• Add a number</span><br>";
    }

    if (message === "") {
        message = "<span class='rule valid'>✔ Strong Password</span>";
    }

    rulesBox.innerHTML = message;
});

/* FORM SUBMIT */
form.addEventListener("submit", function (e) {

    let isValid = true;

    let first = cleanText(document.getElementById("firstName").value);
    let middle = cleanText(document.getElementById("middleName").value);
    let last = cleanText(document.getElementById("lastName").value);
    let email = document.getElementById("email").value.trim();
    let phone = document.getElementById("phone").value;
    let password = document.getElementById("password").value;
    let confirm = document.getElementById("confirmPassword").value;
    let city = cleanText(document.getElementById("city").value);

    /* CLEAR OLD ERRORS */
    document.querySelectorAll(".error").forEach(e => e.innerText = "");
    document.querySelectorAll("input").forEach(i => i.classList.remove("input-error"));

    /* VALIDATIONS */

    if (first === "") {
        showError("firstName", "firstNameError", "Enter first name");
        isValid = false;
    }

    if (last === "") {
        showError("lastName", "lastNameError", "Enter last name");
        isValid = false;
    }

    if (!email.includes("@") || !email.includes(".")) {
        showError("email", "emailError", "Invalid email");
        isValid = false;
    }

    if (phone.length !== 10) {
        showError("phone", "phoneError", "Enter 10 digit number");
        isValid = false;
    }

    if (password.length < 8) {
        showError("password", "passwordError", "Weak password");
        isValid = false;
    }

    if (password !== confirm) {
        showError("confirmPassword", "confirmError", "Passwords do not match");
        isValid = false;
    }

    if (city === "") {
        showError("city", "cityError", "Enter city");
        isValid = false;
    }

    /* FINAL SUBMIT CONTROL */
    if (!isValid) {
        e.preventDefault(); // stop submission ONLY if invalid
    }
    // else → allow normal submit to PHP ✅
});

/* ERROR FUNCTION */
function showError(inputId, errorId, msg) {
    document.getElementById(inputId).classList.add("input-error");
    document.getElementById(errorId).innerText = msg;
}
