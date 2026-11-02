window.initDateRangePicker = function (
    selector,
    callback = null,
    options = {},
) {
    let config = {
        mode: "range",

        dateFormat: "d-m-Y",

        rangeSeparator: " s.d ",

        allowInput: false,

        locale: {
            rangeSeparator: " s.d ",
        },

        onChange(selectedDates, dateStr, instance) {
            let start = "";
            let end = "";

            if (selectedDates.length === 2) {
                start = instance.formatDate(selectedDates[0], "Y-m-d");
                end = instance.formatDate(selectedDates[1], "Y-m-d");
            }

            if (typeof callback === "function") {
                callback(start, end, selectedDates, instance);
            }
        },
    };

    Object.assign(config, options);

    return flatpickr(selector, config);
};
