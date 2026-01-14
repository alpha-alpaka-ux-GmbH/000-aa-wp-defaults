document.addEventListener('DOMContentLoaded', function () {
    var elementsWithin = document.querySelectorAll('.js__trigger_uc a');
    var elementsWithClass = document.querySelectorAll('a.js__trigger_uc');
    var elementsWithHash = document.querySelectorAll('a[href*="#js__trigger_uc"]');
    var ucTriggers = Array.from(elementsWithin).concat(Array.from(elementsWithClass)).concat(Array.from(elementsWithHash));
    ucTriggers.forEach(function (element) {
        element.addEventListener('click', function (e) {
            e.preventDefault();
            if (typeof UC_UI !== 'undefined' && UC_UI) {
                UC_UI.showSecondLayer();
            }
        });
    });
});