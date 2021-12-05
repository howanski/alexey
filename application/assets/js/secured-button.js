function clickSecureBtn(event) {
  event.preventDefault();
  var elem = event.currentTarget;
  var data = elem.dataset;

  var url = data.action;
  var question = data.confirmationQuestion;
  var csrf = data.csrf;

  if (window.confirm(question)) {
    var formData = new FormData();
    formData.append("_token", csrf);
    var fetchOptions = {
      method: "POST",
      body: formData,
    };

    fetch(url, fetchOptions)
      .then((response) => {
        if (response.redirected) {
          location.href = response.url;
          return;
        } else {
          window.location.reload(true);
        }
      })
      .catch((error) => {
        console.log(error);
      });
  }
}

function addListener(elem) {
  elem.addEventListener("click", clickSecureBtn);
}

let secureButtons = document.querySelectorAll(".button-secure");
Array.from(secureButtons).map(addListener);
