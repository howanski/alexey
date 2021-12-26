function clickUnlinkBtn(event) {
    event.preventDefault();
    let elem = event.currentTarget;
    let data = elem.dataset;

    let parentSelector = data.unlinkParent;
    let parent = elem.closest(parentSelector);
    parent.classList.add("alert-toast-out");

    setTimeout(() => {
        let replace = data.unhideReplacement;
        if (replace == "true") {
            let replacementContainerSelector = data.replacementContainer;
            if (replacementContainerSelector) {
                let replacementContainer = elem.closest(
                    replacementContainerSelector
                );
                if (replacementContainer) {
                    let replacerSelector = data.replacementSelector;
                    if (replacerSelector) {
                        let replacer =
                            replacementContainer.querySelector(
                                replacerSelector
                            );
                        if (replacer) {
                            replacer.classList.remove("hidden");
                        }
                    }
                }
            }
        }
        parent.remove();
    }, 900);

    let url = data.unlinkPath;

    let fetchOptions = {
        method: "POST",
    };

    fetch(url, fetchOptions)
        .then((response) => {})
        .catch((error) => {});
}

function addListener(elem) {
    elem.addEventListener("click", clickUnlinkBtn);
    elem.classList.remove("inline-unlinker");
}

function run() {
    let unlinkers = document.querySelectorAll(".inline-unlinker");
    Array.from(unlinkers).map(addListener);
}

setInterval(run, 2000);
