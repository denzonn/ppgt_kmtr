window.debounceSearch = function (selector, callback, delay = 400) {
    let typingTimer;

    $(document).on("keyup", selector, function () {
        clearTimeout(typingTimer);

        typingTimer = setTimeout(callback, delay);
    });
};
