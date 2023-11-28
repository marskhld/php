let username = document.getElementById("username");
let password = document.getElementById("password");
let password2 = document.getElementById("password2");
let email = document.getElementById("email");
let dob = document.getElementById("dob");
let signupForm = document.getElementById("signupForm");

username.addEventListener("blur", usernameHandler);
email.addEventListener("blur", emailHandler);
password.addEventListener("blur", passwordHandler);
password2.addEventListener("blur", cpassHandler);
dob.addEventListener("blur", dobHandler);
signupForm.addEventListener("submit", validateSignup);