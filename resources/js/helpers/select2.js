window.initSelect2 = function () {
    $(".select2").each(function () {
        const $select = $(this);

        if ($select.hasClass("select2-hidden-accessible")) return;

        const $modal = $select.closest(".modal-box");

        const options = {
            width: "100%",
        };

        if ($modal.length) {
            options.dropdownParent = $modal;
        }

        $select.select2(options);
    });
};
