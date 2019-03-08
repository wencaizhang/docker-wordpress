(function () {
    "use strict";

    var element,
        button = document.querySelector('#scrollup-master');

    if (!button) return;

    var distance = parseInt(button.getAttribute('data-distance'));

    function showOrHideButton() {
        if (document.body.scrollTop > distance || document.documentElement.scrollTop > distance) {
            button.style.display = 'block';
        } else {
            button.style.display = 'none';
        }
    }

    function scrollToTop(element, duration) {

        if (duration <= 0) return;
        var difference = 0 - element.scrollTop;
        var perTick = difference / duration * 10;

        setTimeout(function () {
            element.scrollTop = element.scrollTop + perTick;
            if (element.scrollTop === 0) return;
            scrollToTop(element, duration - 10);
        }, 10);
    }

    document.addEventListener("DOMContentLoaded", function () {
        window.addEventListener("scroll", function () {
            showOrHideButton();
        });
    });

    button.addEventListener("click", function () {
        if (document.body.scrollTop) {
            // For Safari
            element = document.body;
        } else if (document.documentElement.scrollTop) {
            // For Chrome, Firefox, IE and Opera
            element = document.documentElement;
        }

        scrollToTop(element, 300);
    });

})();