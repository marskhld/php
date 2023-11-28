let answer = document.getElementsByClassName("newQorA")['0']; //points to form element
let detailForm = document.getElementById("detailForm");

answer.addEventListener("keyup", AnswerHandler); // sends form element to validation function

detailForm.addEventListener("submit", validateDetailForm);
//answer.addEventListener("keyup", updateAnswerCharCount);
