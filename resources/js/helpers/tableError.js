window.tableError = function (colspan, text = "Gagal mengambil data.") {
    return `
        <tr>

            <td colspan="${colspan}"
                class="py-14 text-center text-red-500">

                ${text}

            </td>

        </tr>
    `;
};
