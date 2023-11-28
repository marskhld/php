/* Event Register for Login Page */
let username = document.getElementById("username");
let password = document.getElementById("pass");
let loginForm = document.getElementById("loginMain");

username.addEventListener("blur", emailHandler);
password.addEventListener("blur", passwordHandler);
loginForm.addEventListener("submit", validateLogin);