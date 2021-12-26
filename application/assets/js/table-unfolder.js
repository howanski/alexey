function showHiddenRow(elem) {
    elem.classList.remove("hidden");
}

function clickUnfoldBtn(event) {
    event.preventDefault();
    let elem = event.currentTarget;

    let parent = elem.closest("tbody");
    let hiddenRows = parent.querySelectorAll("tr.hidden");
    Array.from(hiddenRows).map(showHiddenRow);
    elem.closest("tr").remove();
}

function addListener(elem) {
    elem.addEventListener("click", clickUnfoldBtn);
    elem.classList.remove("table-unfolder");
}

function run(){
    let unfolders = document.querySelectorAll(".table-unfolder");
    Array.from(unfolders).map(addListener);
}

setInterval(run, 2000);
