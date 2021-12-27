const scrollBtn = document.querySelector(".scrollTopBtn");
const scrollQuarterBtn = document.querySelector(".scrollQuarterBtn");
const showOnScrollPixels = 1000;

function scrollToTop() {
    window.scrollTo({
        top: 0,
        left: 0,
        behavior: "smooth",
    });
}

function scrollQuarter() {
    let position = document.body.scrollTop;
    if (document.documentElement.scrollTop > position) {
        position = document.documentElement.scrollTop;
    }
    let targetPosition = (position * 3) / 4;
    window.scrollTo({
        top: targetPosition,
        left: 0,
        behavior: "smooth",
    });
}

function showOrHideAuto() {
    if (
        document.body.scrollTop > showOnScrollPixels ||
        document.documentElement.scrollTop > showOnScrollPixels
    ) {
        scrollBtn.closest("div").style.display = "block";
    } else {
        scrollBtn.closest("div").style.display = "none";
    }
}

window.addEventListener("load", (event) => {
    showOrHideAuto();
    window.onscroll = function () {
        showOrHideAuto();
    };
    scrollBtn.addEventListener("click", scrollToTop);
    scrollQuarterBtn.addEventListener("click", scrollQuarter);
});
