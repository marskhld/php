/* Event Handler Functions for Q & A site */

function usernameHandler(event) {
  console.log("screen name handler function start ----------");
  let uname = event.target;
  let uname_err = document.getElementById("uname_err");
  let valid = validateScreenName(uname.value);
  if (!valid) {
    console.log("screen name handler function - not valid");
    //	highlight the input box.	
    uname.classList.add("invalidEntry");
    // show the error message.	
    uname_err.classList.remove("hidden");
  }
  else {
    console.log("screen name handler function - valid");
    // remove the highlights from the input box. 
    uname.classList.remove("invalidEntry");
    // hide the error message.	
    uname_err.classList.add("hidden");
  }
  console.log("screen name handler function done ----------");
}

function validateScreenName(screenName) {
  console.log("validating screen name");
  let reg = /[\s\W]|^$/; // spaces or other non-word characters - /^[a-z ,.'-]+$/i
  if (!reg.test(screenName)) {
    console.log("validateScreenName = returning valid");
    return true; // not empty input, and passes regex
  }
  else {
    console.log("validateScreenName - not valid");
    return false;
  }
}

function emailHandler(event) {
  let email = event.target;
  let email_err = document.getElementById('email_err');
  if (!validateEmail(email.value)) {
    //	highlight the input box.	
    email.classList.add("invalidEntry");
    // show the error message.	
    email_err.classList.remove("hidden");
  }
  else {
    // remove the highlights from the input box. 
    email.classList.remove("invalidEntry");
    // hide the error message.	
    email_err.classList.add("hidden");
  }
}

function validateEmail(email) {
  const reg = /^[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
  // const reg = /^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/;
  // the email validation I took from the lab 5 note 
  if (reg.test(email))
    return true; // passes regex
  else
    return false;
}

function passwordHandler(event) {
  console.log("password handler function start-------------");
  let password = event.target;
  let pwd_err = document.getElementById("pwd_err");
  if (!validatePassword(password.value)) {
    //	highlight the input box.	
    password.classList.add("invalidEntry");
    // show the error message.	
    pwd_err.classList.remove("hidden");
    console.log("password handler function: invalid");
  }
  else {
    // remove the highlights from the input box. 
    password.classList.remove("invalidEntry");
    // hide the error message.	
    pwd_err.classList.add("hidden");
    console.log("password handler function: valid");
  }
  console.log("password handler function end-------------");

}
function validatePassword(password) {
  const reg = /\S{8,}$/;
  // '/S' means non-space
  // '{8,}' means at least 8 characters
  if (reg.test(password))
    return true; // passes regex
  else
    return false;
}
function cpassHandler(event) {
  let cpass = event.target;
  let cpwd_err = document.getElementById("cpwd_err");
  if (!checkPasswordMatch(cpass.value)) { //invalid password
    //	highlight the input box.	
    cpass.classList.add("invalidEntry");
    // show the error message.	
    cpwd_err.classList.remove("hidden");
  }
  else {
    // remove the highlights from the input box. 
    cpass.classList.remove("invalidEntry");
    // hide the error message
    cpwd_err.classList.add("hidden");
  }
}

function checkPasswordMatch(cpwd) {
  let pwd = document.getElementById("password");
  // if check pass does not match pass, or if check pass' length is zero 
  if ((cpwd != pwd.value) || (cpwd.length == 0))
    return false; // not a match
  else
    return true;
}

function dobHandler(event) {
  let dob = event.target;
  let dob_err = document.getElementById("dob_err");
  if (!validateDob(dob.value)) {
    //	highlight the input box.	
    dob.classList.add("invalidEntry");
    // show the error message.	
    dob_err.classList.remove("hidden");
  }
  else {
    // remove the highlights from the input box. 
    dob.classList.remove("invalidEntry");
    // hide the error message
    dob_err.classList.add("hidden");
  }
}

function validateDob(dob) {
  const reg = /^([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))$/;
  //  /\d{4}-\d{2}-\d{2}/; 
  // ^\d{4}\-(0[1-9]|1[012])\-(0[1-9]|[12][0-9]|3[01])$ or ((?:19|20)[0-9][0-9])-(0?[1-9]|1[012])-(0?[1-9]|[12][0-9]|3[01])

  if (dob.match(reg))
    return true;  // valid
  else
    return false;
}

function validateSignup(event) {
  let uname = document.getElementById("username");
  let pwd = document.getElementById("password");
  let cpwd = document.getElementById("password2");
  let email = document.getElementById("email");
  let dob = document.getElementById("dob");

  let formIsValid = true;

  if (!validateScreenName(uname.value)) {
    uname.classList.add("invalid"); // highlight inputbox
    document.getElementById("uname_err").classList.remove("hidden");
    formIsValid = false;
  }
  else { // screen name passes validation
    uname.classList.remove("invalid"); // remove inputbox highlight
    document.getElementById("uname_err").classList.add("hidden"); // hide error
  }
  if (!validateEmail(email.value)) {
    email.classList.add("invalid"); // highlight inputbox
    document.getElementById("email_err").classList.remove("hidden");
    formIsValid = false;
  }
  else { // screen name passes validation
    email.classList.remove("invalid"); // remove inputbox highlight
    document.getElementById("email_err").classList.add("hidden"); // hide error
  }
  if (!validatePassword(pwd.value)) {
    pwd.classList.add("invalid");
    document.getElementById("pwd_err").classList.remove("hidden");
    formIsValid = false;
  }
  else {
    pwd.classList.remove("invalid");
    document.getElementById("pwd_err").classList.add("hidden");
  }
  if (!checkPasswordMatch(cpwd.value)) {
    cpwd.classList.add("invalid");
    document.getElementById("cpwd_err").classList.remove("hidden");
    formIsValid = false;
  }
  else {
    cpwd.classList.remove("invalid");
    document.getElementById("cpwd_err").classList.add("hidden");
  }
  if (!validateDob(dob.value)) {
    dob.classList.add("invalid");
    document.getElementById("dob_err").classList.remove("hidden");
    formIsValid = false;
  }
  else {
    dob.classList.remove("invalid");
    document.getElementById("dob_err").classList.add("hidden");
  }

  if (!formIsValid) {
    event.preventDefault();
  } else {
    console.log("Validation successful, sending data to the server");
  }
}
function validateLogin(event) {
  let email = document.getElementById("username");
  let pwd = document.getElementById("pass");
  let formIsValid = true;

  if (!validateEmail(email.value)) {
    email.classList.add("invalid"); // highlight inputbox
    document.getElementById("email_err").classList.remove("hidden");
    formIsValid = false;
  }
  else { // screen name passes validation
    email.classList.remove("invalid"); // remove inputbox highlight
    document.getElementById("email_err").classList.add("hidden"); // hide error
  }

  if (!validatePassword(pwd.value)) {
    pwd.classList.add("invalid");
    document.getElementById("pwd_err").classList.remove("hidden");
    formIsValid = false;
  }
  else {
    pwd.classList.remove("invalid");
    document.getElementById("pwd_err").classList.add("hidden");
  }

  if (!formIsValid) {
    event.preventDefault();
  } else {
    console.log("Validation successful, sending data to the server");
  }
}

function validateQuestion(question) {
  if (question.length > 0 && question.length < 200) { // if correctly entered...
    return true;
  }
  else {
    return false;
  }
}

function validateQuestionForm(questionForm) {
  let question = document.getElementsByClassName("newQorA")['0'];
  let formIsValid = true;
  console.log("~~~~~~~~~~~ Validating qCreation Page Form! ~~~~~~~~~~~~~~~~~");

  if (!QAValidator(question, 200)) {  // 
    question.classList.add("invalidEntry");
    //console.log("Invalid question, thus form is invalid");
    document.getElementById("charTracker").classList.add("error-text");
    document.getElementById("ans-err").classList.remove("hidden");
    formIsValid = false;
    console.log("Invalid question, thus form is invalid");
  }
  else {
    question.classList.remove("invalidEntry");
    document.getElementById("charTracker").classList.remove("error-text");
    document.getElementById("ans-err").classList.add("hidden");
  }

  if (formIsValid === false) {
    questionForm.preventDefault();
    console.log("parameters entered were invalid");
  }
  else {
    console.log("Validation successful, sending data to the server");
  }
}

function validateAnswer(answer) {
  if (answer.length > 0 && answer.length < 1500) {
    return true;
  }
  else {
    return false;
  }
}

function validateDetailForm(detailForm) {
  let answer = document.getElementsByClassName("newQorA")['0'];
  let formIsValid = true;
  console.log("~~~~~~~~~~~ Validating qDetail Page Form! ~~~~~~~~~~~~~~~~~");

  if (!QAValidator(answer, 1500)) {  // not valid question/answer
    console.log("Invalid answer, thus form is invalid");
    answer.classList.add("invalidEntry");
    document.getElementById("charTracker").classList.add("error-text");
    document.getElementById("ans-err").classList.remove("hidden");
    formIsValid = false;
  }
  else { // valid
    answer.classList.remove("invalidEntry");
    document.getElementById("charTracker").classList.remove("error-text");
    document.getElementById("ans-err").classList.add("hidden");
  }

  if (formIsValid === false) {
    // If any of the validations fail, we need to stop the form submission.
    // Use event.preventDefault() to stop the form submission.
    event.preventDefault();
    console.log("~~~~~~  Adding New Answer Failed - parameters entered were invalid!~~~~~~~~");
  }
  else {
    console.log("Validation successful, sending data to the server");
  }
}

function AnswerHandler(event) {
  QAHandler(event.target, 1500);
}

function QuestionHandler(event) {
  QAHandler(event.target, 200);
}

function QAHandler(event, maxLength) {
  let QorA = event;
  let charCount = document.getElementById("charCount");
  let charTracker = document.getElementById("charTracker");
  let remainingChars = document.getElementById("remainingChars");

  charCount.innerHTML = QorA.value.length;
  remainingChars.innerHTML = maxLength - QorA.value.length;

  if (QAValidator(QorA, maxLength)) {
    QorA.classList.remove("invalidEntry");
    charTracker.classList.remove("error-text");
    document.getElementById("ans-err").classList.add("hidden");
  }
  else {
    QorA.classList.add("invalidEntry");
    charTracker.classList.add("error-text");
  }
}

function QAValidator(QorA, maxLength) {
  let currLength = QorA.value.length;

  if (currLength > maxLength || currLength == 0) {
    return false;
  }
  else {
    return true;
  }
}

