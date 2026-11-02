window.refreshData = function (
    button = "#btnRefresh",
    search = "#search",
    callback,
) {
    $(document).on("click", button, function () {
        $(search).val("");

        callback();
    });
};
