import "./helpers/info";
import "./helpers/pagination";
import "./helpers/hapus";
import "./helpers/skeleton";
import "./helpers/search";
import "./helpers/refresh";
import "./helpers/emptyTable";
import "./helpers/tableError";
import "./helpers/ajax";
import "./helpers/flatPicker";
import "./helpers/swal";

window.formatAngka = function (value) {
    if (value === null || value === undefined || value === "") {
        return "0";
    }

    return new Intl.NumberFormat("id-ID").format(Number(value) || 0);
};

window.getNominal = function (value) {
    if (!value) return 0;

    return parseInt(value.toString().replace(/\./g, "")) || 0;
};

window.formatDurasi = function (menit) {
    menit = Math.abs(parseInt(menit) || 0);

    if (menit === 0) {
        return "-";
    }

    const jam = Math.floor(menit / 60);
    const sisaMenit = menit % 60;

    if (jam > 0 && sisaMenit > 0) {
        return `${jam} Jam ${sisaMenit} Menit`;
    }

    if (jam > 0) {
        return `${jam} Jam`;
    }

    return `${sisaMenit} Menit`;
};

window.formatJam = function (value) {
    if (!value) {
        return "-";
    }

    const date = new Date(value);

    return date.toLocaleTimeString("id-ID", {
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
    });
};

window.capitalize = function (value) {
    return value.charAt(0).toUpperCase() + value.slice(1);
};

window.toNumber = function (value) {
    if (value === null || value === undefined || value === "") {
        return 0;
    }

    if (typeof value === "number") {
        return value;
    }

    return (
        Number(
            String(value)
                .replace(/Rp/gi, "")
                .replace(/\./g, "")
                .replace(/,/g, "")
                .replace(/[^\d-]/g, ""),
        ) || 0
    );
};

window.formatRupiah = function (value, prefix = true) {
    if (value === null || value === undefined || value === "") {
        return prefix ? "Rp. 0" : "0";
    }

    let angka = Number(String(value).replace(/[^\d-]/g, ""));

    if (isNaN(angka)) angka = 0;

    const hasil = new Intl.NumberFormat("id-ID").format(Math.abs(angka));

    return prefix
        ? angka < 0
            ? `- Rp. ${hasil}`
            : `Rp. ${hasil}`
        : angka < 0
          ? `- ${hasil}`
          : hasil;
};

window.formatRupiahDecimal = function (value, prefix = true) {
    if (value === null || value === undefined || value === "") {
        return prefix ? "Rp. 0" : "0";
    }

    let angka = parseFloat(value);

    if (isNaN(angka)) {
        angka = parseFloat(String(value).replace(/\./g, "").replace(",", "."));
    }

    const hasil = new Intl.NumberFormat("id-ID", {
        maximumFractionDigits: 0,
    }).format(angka);

    return prefix ? `Rp. ${hasil}` : hasil;
};

window.formatRupiahFull = function (value, prefix = true) {
    const angka = Number(value) || 0;

    const hasil = new Intl.NumberFormat("id-ID", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(angka);

    return prefix ? `Rp. ${hasil}` : hasil;
};

window.formatRupiahTanpaRp = function (value, prefix = true) {
    if (value === null || value === undefined || value === "") {
        return prefix ? "Rp. 0" : "0";
    }

    const angka = Number(String(value).replace(/[^\d]/g, ""));

    const hasil = new Intl.NumberFormat("id-ID").format(angka);

    return prefix ? `${hasil}` : hasil;
};

window.formatTanggal = function (tanggal, locale = "id-ID", options = {}) {
    if (!tanggal) return "-";

    const date = new Date(tanggal);

    if (isNaN(date.getTime())) return "-";

    const defaultOption = {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
    };

    return new Intl.DateTimeFormat(locale, {
        ...defaultOption,
        ...options,
    }).format(date);
};

window.formatTanggalIndo = function (tanggal) {
    if (!tanggal) return "-";

    const hari = [
        "Minggu",
        "Senin",
        "Selasa",
        "Rabu",
        "Kamis",
        "Jumat",
        "Sabtu",
    ];

    const bulan = [
        "Jan",
        "Feb",
        "Mar",
        "Apr",
        "Mei",
        "Jun",
        "Jul",
        "Ags",
        "Sep",
        "Okt",
        "Nov",
        "Des",
    ];

    const date = new Date(tanggal);

    if (isNaN(date.getTime())) return "-";

    return `${hari[date.getDay()]}, ${date.getDate()} ${bulan[date.getMonth()]} ${date.getFullYear()}`;
};

window.formatTanggalJam = function (tanggal, locale = "id-ID") {
    if (!tanggal) return "-";

    const date = new Date(tanggal);

    if (isNaN(date.getTime())) return "-";

    return new Intl.DateTimeFormat(locale, {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",

        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",

        hour12: false,
    }).format(date);
};

window.showModal = function (id) {
    const modal = document.getElementById(id);
    if (!modal) return;

    const content = modal.querySelector(".modal-content");
    if (!content) return;

    // KUNCI SCROLL HALAMAN UTAMA
    document.documentElement.classList.add("overflow-hidden");
    document.body.classList.add("overflow-hidden");

    modal.classList.remove("hidden");
    modal.classList.add("flex", "justify-center");

    modal.classList.remove("items-center", "items-start");
    content.classList.remove("mt-[5vh]", "mt-[10vh]");

    function adjustModalPosition() {
        if (modal.classList.contains("hidden")) return;

        const viewportHeight = window.innerHeight;
        const modalHeight = content.scrollHeight;

        modal.classList.remove("items-center", "items-start");
        content.classList.remove("mt-[5vh]", "mt-[10vh]");

        if (modalHeight + 40 <= viewportHeight) {
            modal.classList.add("items-center");
        } else {
            modal.classList.add("items-start");
            content.classList.add("mt-[5vh]");
        }
    }

    requestAnimationFrame(() => {
        adjustModalPosition();

        modal.classList.remove("opacity-0");
        modal.classList.add("opacity-100");

        content.classList.remove("scale-95", "-translate-y-6");
        content.classList.add("scale-100", "translate-y-0");
    });

    // PANTAU PERUBAHAN TINGGI MODAL
    if (window.ResizeObserver) {
        if (modal._modalResizeObserver) {
            modal._modalResizeObserver.disconnect();
        }

        modal._modalResizeObserver = new ResizeObserver(() => {
            adjustModalPosition();
        });

        modal._modalResizeObserver.observe(content);
    }

    // RESPONSIVE
    if (modal._modalResizeHandler) {
        window.removeEventListener("resize", modal._modalResizeHandler);
    }

    modal._modalResizeHandler = adjustModalPosition;
    window.addEventListener("resize", modal._modalResizeHandler);
};

window.closeModal = function (id) {
    const modal = document.getElementById(id);
    if (!modal) return;

    const content = modal.querySelector(".modal-content");

    modal.classList.remove("opacity-100");
    modal.classList.add("opacity-0");

    if (content) {
        content.classList.remove("scale-100", "translate-y-0");
        content.classList.add("scale-95", "-translate-y-6");
    }

    // BERSIHKAN OBSERVER
    if (modal._modalResizeObserver) {
        modal._modalResizeObserver.disconnect();
        modal._modalResizeObserver = null;
    }

    // BERSIHKAN EVENT RESIZE
    if (modal._modalResizeHandler) {
        window.removeEventListener("resize", modal._modalResizeHandler);
        modal._modalResizeHandler = null;
    }

    setTimeout(() => {
        modal.classList.remove(
            "flex",
            "items-center",
            "items-start",
            "justify-center",
        );

        content?.classList.remove("mt-[5vh]", "mt-[10vh]");

        modal.classList.add("hidden");

        // BUKA KEMBALI SCROLL HALAMAN UTAMA
        const activeModal = document.querySelector(
            ".modal-overlay:not(.hidden)",
        );

        if (!activeModal) {
            document.documentElement.classList.remove("overflow-hidden");
            document.body.classList.remove("overflow-hidden");
        }
    }, 300);
};

document.addEventListener("click", function (e) {
    if (e.target.classList.contains("modal-overlay")) {
        closeModal(e.target.id);
    }
});

document.addEventListener("keydown", function (e) {
    if (e.key !== "Escape") return;

    document.querySelectorAll(".modal-overlay").forEach(function (modal) {
        if (!modal.classList.contains("hidden")) {
            closeModal(modal.id);
        }
    });
});

window.showToast = function (message, type = "success") {
    let isError = type === "error";
    let bgColor = isError ? "#b91c1c" : "#057a37";
    let icon = isError
        ? '<i class="fa-solid fa-circle-xmark" style="font-size: 16px;"></i>'
        : '<i class="fa-solid fa-circle-check" style="font-size: 16px;"></i>';

    let $container = $("#toastContainer");
    if ($container.length === 0) {
        if (typeof $ !== "undefined" && $("body").length > 0) {
            $("body").append(`
                <div id="toastContainer" style="position: fixed; bottom: 24px; right: 24px; z-index: 999999; display: flex; flex-direction: column; gap: 10px; pointer-events: none;"></div>
            `);
            $container = $("#toastContainer");
        }
    }

    let toastId =
        "toast_" + Date.now() + "_" + Math.random().toString(36).substr(2, 4);
    let toastHtml = `
        <div id="${toastId}" style="display: flex; align-items: center; gap: 12px; background-color: ${bgColor}; color: #ffffff; padding: 12px 20px; border-radius: 6px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3); font-size: 13px; font-weight: 600; opacity: 0; transform: translateY(20px); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); pointer-events: auto;">
            ${icon}
            <span>${message}</span>
        </div>
    `;

    if (typeof $ !== "undefined" && $container.length > 0) {
        let $toast = $(toastHtml);
        $container.append($toast);

        setTimeout(() => {
            $toast.css({ opacity: "1", transform: "translateY(0)" });
        }, 10);

        setTimeout(() => {
            $toast.css({ opacity: "0", transform: "translateY(20px)" });
            setTimeout(() => $toast.remove(), 300);
        }, 3500);
    }
};

window.formatTanggal = function (dateStr) {
    if (!dateStr) return "-";
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;
    const day = String(d.getDate()).padStart(2, "0");
    const month = String(d.getMonth() + 1).padStart(2, "0");
    const year = d.getFullYear();
    return `${day}-${month}-${year}`;
};

window.formatBadgeStatus = function (status) {
    if (!status)
        return `<span class="inline-block px-3 py-1 rounded-lg bg-slate-100 text-slate-600 text-[11px] font-semibold">-</span>`;

    let label = status;
    let badgeClass = "bg-primary text-white";
    let statusLower = String(status).toLowerCase();

    if (
        statusLower.includes("ditolak") ||
        statusLower.includes("batal") ||
        statusLower === "reject"
    ) {
        badgeClass = "bg-red-600 text-white";
    } else if (
        statusLower === "diterima" ||
        statusLower === "selesai" ||
        statusLower === "approved"
    ) {
        badgeClass = "bg-emerald-600 text-white";
    } else if (
        statusLower.includes("wawancara") ||
        statusLower === "proses" ||
        statusLower === "pengecekan" ||
        statusLower === "pemasangan"
    ) {
        badgeClass = "bg-amber-500 text-white";
    } else if (
        statusLower === "ditinjau" ||
        statusLower === "lolos administrasi" ||
        statusLower === "baru" ||
        statusLower === "pending"
    ) {
        badgeClass = "bg-blue-600 text-white";
    }

    return `<span class="inline-block px-3 py-1 rounded-lg ${badgeClass} text-[11px] font-semibold">${label}</span>`;
};
