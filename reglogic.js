const form = document.getElementById("registerForm");

const passwordBox = document.getElementById("password");
const rulesBox = document.getElementById("passwordRules");

/* CLEAN SPACES */
function cleanText(text) {
    return text.replace(/\s+/g, " ").trim();
}

/* PHONE LIMIT */
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
        message += "<span class='rule invalid'>• Minimum 8 characters required</span>";
    }

    if (pass.indexOf("@") === -1 && pass.indexOf("#") === -1 && pass.indexOf("$") === -1) {
        message += "<span class='rule invalid'>• Add a special character (@ # $)</span>";
    }

    let hasNumber = false;
    for (let i = 0; i < pass.length; i++) {
        if (pass[i] >= '0' && pass[i] <= '9') {
            hasNumber = true;
        }
    }

    if (!hasNumber) {
        message += "<span class='rule invalid'>• Add a number</span>";
    }

    if (message === "") {
        message = "<span class='rule valid'>✔ Strong Password</span>";
    }

    rulesBox.innerHTML = message;
});

/* FORM SUBMIT */
form.addEventListener("submit", function (e) {
    e.preventDefault();

    let isValid = true;

    let first = cleanText(document.getElementById("firstName").value);
    let middle = cleanText(document.getElementById("middleName").value);
    let last = cleanText(document.getElementById("lastName").value);
    let email = document.getElementById("email").value;
    let phone = document.getElementById("phone").value;
    let password = document.getElementById("password").value;
    let confirm = document.getElementById("confirmPassword").value;
    let city = cleanText(document.getElementById("city").value);

    // Clear errors
    document.querySelectorAll(".error").forEach(e => e.innerText = "");

    // FIRST NAME
    if (first === "") {
        showError("firstName", "firstNameError", "Enter first name");
        isValid = false;
    }

    // LAST NAME
    if (last === "") {
        showError("lastName", "lastNameError", "Enter last name");
        isValid = false;
    }

    // EMAIL
    if (email.indexOf("@") === -1 || email.indexOf(".") === -1) {
        showError("email", "emailError", "Invalid email");
        isValid = false;
    }

    // PHONE
    if (phone.length !== 10) {
        showError("phone", "phoneError", "Enter 10 digit number");
        isValid = false;
    }

    // PASSWORD
    if (password.length < 8) {
        showError("password", "passwordError", "Weak password");
        isValid = false;
    }

    // CONFIRM
    if (password !== confirm) {
        showError("confirmPassword", "confirmError", "Passwords do not match");
        isValid = false;
    }

    // CITY
    if (city === "") {
        showError("city", "cityError", "Enter city");
        isValid = false;
    }

    // FINAL OUTPUT
    if (isValid) {
        alert(
`Registration Successful 🎉

First Name: ${first}
Middle Name: ${middle}
Last Name: ${last}
Email: ${email}
Phone: ${phone}
City: ${city}`
        );

        form.reset();
        rulesBox.innerHTML = "";
    }
});

/* ERROR FUNCTION */
function showError(inputId, errorId, msg) {
    document.getElementById(inputId).classList.add("input-error");
    document.getElementById(errorId).innerText = msg;
}