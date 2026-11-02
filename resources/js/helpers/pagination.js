window.renderPagination = function (
    res,
    callback = "loadData",
    selector = "#pagination",
) {
    let html = "";

    const currentPage = Number(res.current_page) || 1;
    const lastPage = Number(res.last_page) || 1;

    if (lastPage <= 1) {
        $(selector).html("");
        return;
    }

    // =========================================================
    // BUTTON PREVIOUS
    // =========================================================
    html += `
        <button
            type="button"
            onclick="${callback}(${currentPage - 1})"
            ${currentPage === 1 ? "disabled" : ""}
            class="w-10 h-10 shrink-0 rounded-xl
                   border border-slate-200
                   hover:bg-slate-100
                   disabled:opacity-50
                   disabled:cursor-not-allowed">

            <i class="fa-solid fa-angle-left"></i>

        </button>
    `;

    // =========================================================
    // GENERATE PAGE
    // =========================================================
    const pages = [];

    // Kalau halaman sedikit, tampilkan semua
    if (lastPage <= 7) {
        for (let i = 1; i <= lastPage; i++) {
            pages.push(i);
        }
    } else {
        // Selalu tampilkan halaman pertama
        pages.push(1);

        // =============================
        // POSISI DI AWAL
        // 1 2 3 4 5 ... 293
        // =============================
        if (currentPage <= 4) {
            pages.push(2, 3, 4, 5);
            pages.push("...");
            pages.push(lastPage);
        }

        // =============================
        // POSISI DI AKHIR
        // 1 ... 289 290 291 292 293
        // =============================
        else if (currentPage >= lastPage - 3) {
            pages.push("...");

            for (let i = lastPage - 4; i <= lastPage; i++) {
                pages.push(i);
            }
        }

        // =============================
        // POSISI DI TENGAH
        // 1 ... 15 16 17 18 19 ... 293
        // =============================
        else {
            pages.push("...");

            pages.push(currentPage - 2);
            pages.push(currentPage - 1);
            pages.push(currentPage);
            pages.push(currentPage + 1);
            pages.push(currentPage + 2);

            pages.push("...");
            pages.push(lastPage);
        }
    }

    // =========================================================
    // RENDER PAGE
    // =========================================================
    pages.forEach(function (page) {
        // Ellipsis
        if (page === "...") {
            html += `
                <span
                    class="w-8 h-10 shrink-0
                           flex items-center justify-center
                           text-slate-400 select-none">

                    ...

                </span>
            `;

            return;
        }

        // Nomor halaman
        html += `
            <button
                type="button"
                onclick="${callback}(${page})"
                class="w-10 h-10 shrink-0 rounded-xl
                    ${
                        page === currentPage
                            ? "bg-primary text-white"
                            : "border border-slate-200 hover:bg-slate-100"
                    }">

                ${page}

            </button>
        `;
    });

    // =========================================================
    // BUTTON NEXT
    // =========================================================
    html += `
        <button
            type="button"
            onclick="${callback}(${currentPage + 1})"
            ${currentPage === lastPage ? "disabled" : ""}
            class="w-10 h-10 shrink-0 rounded-xl
                   border border-slate-200
                   hover:bg-slate-100
                   disabled:opacity-50
                   disabled:cursor-not-allowed">

            <i class="fa-solid fa-angle-right"></i>

        </button>
    `;

    $(selector).html(html);
};
