window.emptyTable = function (colspan, title, subtitle) {
    return `
        <tr>

            <td colspan="${colspan}" class="py-16">

                <div class="flex flex-col items-center">

                    <i class="fa-regular fa-folder-open text-6xl text-slate-300 mb-4"></i>

                    <h3 class="font-semibold text-slate-700 text-lg">
                        ${title}
                    </h3>

                    <p class="text-slate-400 mt-1">
                        ${subtitle}
                    </p>

                </div>

            </td>

        </tr>
    `;
};
