window.renderInfo = function (selector, res) {
    $(selector).html(`
        Menampilkan
        <b>${res.from ?? 0}</b>
        -
        <b>${res.to ?? 0}</b>
        dari
        <b>${res.total}</b>
        data
    `);
};
