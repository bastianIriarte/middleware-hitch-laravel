(function () {
    'use strict'

    document.addEventListener("DOMContentLoaded", function () {
        var genericExamples = document.querySelectorAll("[data-trigger]");
        for (let i = 0; i < genericExamples.length; ++i) {
        var element = genericExamples[i];
        new Choices(element, {
            allowHTML: true,
            placeholderValue: "This is a placeholder set in the config",
            searchPlaceholderValue: "Search",
        });
        }
    });

})();