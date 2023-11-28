let question = document.getElementsByClassName("newQorA")['0'];
let questionForm = document.getElementById("questionForm");

question.addEventListener("keyup", QuestionHandler);
questionForm.addEventListener("submit", validateQuestionForm);

//question.addEventListener("keyup", updateQuestionCharCount);
