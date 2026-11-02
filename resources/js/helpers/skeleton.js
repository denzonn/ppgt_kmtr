window.loadingSkeleton = function (
    selector = "#tableData",
    rows = 8,
    template = "",
) {
    if (!template) return;

    let html = "";

    for (let i = 0; i < rows; i++) {
        html += template;
    }

    $(selector).html(html);
};

window.skeleton = {
    table: `
        <tr class="animate-pulse">

            <td class="px-6 py-5">
                <div class="h-4 w-6 rounded bg-slate-200"></div>
            </td>

            <td class="px-6 py-5">
                <div class="flex items-center gap-3">

                    <div class="w-11 h-11 rounded-full bg-slate-200"></div>

                    <div>
                        <div class="h-4 w-40 rounded bg-slate-200 mb-2"></div>
                        <div class="h-3 w-52 rounded bg-slate-100"></div>
                    </div>

                </div>
            </td>

            <td class="px-6 py-5">
                <div class="h-7 w-20 rounded-full bg-slate-200"></div>
            </td>

            <td class="px-6 py-5">
                <div class="h-4 w-24 rounded bg-slate-200"></div>
            </td>

            <td class="px-6 py-5">
                <div class="flex items-center justify-center gap-2">
                    <div class="w-10 h-10 rounded-xl bg-slate-200"></div>
                    <div class="w-10 h-10 rounded-xl bg-slate-200"></div>
                    <div class="w-10 h-10 rounded-xl bg-slate-200"></div>
                </div>
            </td>

        </tr>
    `,
};
