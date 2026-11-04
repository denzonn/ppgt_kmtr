// Helper SweetAlert2 yang dipakai bersama seluruh halaman.

window.swalError = function (message) {
    Swal.fire({
        icon: "error",
        title: "Gagal!",
        text: message,
        customClass: {
            popup: "rounded-2xl",
        },
    });
};

window.swalSuccess = function (message) {
    Swal.fire({
        icon: "success",
        title: "Berhasil!",
        text: message,
        timer: 1800,
        showConfirmButton: false,
        customClass: {
            popup: "rounded-2xl",
        },
    });
};

window.swalWarning = function (message) {
    Swal.fire({
        icon: "warning",
        title: "Perhatian!",
        text: message,
        customClass: {
            popup: "rounded-2xl",
        },
    });
};

/**
 * Dialog konfirmasi. Jalankan callback saat pengguna menyetujui.
 *
 * options: { icon, confirmButtonText, confirmButtonColor, cancelButtonText }
 */
window.swalConfirm = function (title, text, callback, options = {}) {
    Swal.fire({
        icon: options.icon || "warning",
        title: title,
        text: text,
        showCancelButton: true,
        confirmButtonText: options.confirmButtonText || "Ya, Hapus",
        cancelButtonText: options.cancelButtonText || "Batal",
        confirmButtonColor: options.confirmButtonColor || "#dc2626",
        cancelButtonColor: "#64748b",
        reverseButtons: true,
        customClass: {
            popup: "rounded-2xl",
        },
    }).then((result) => {
        if (result.isConfirmed) callback();
    });
};
