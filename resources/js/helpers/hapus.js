window.hapusData = function ({
    url,
    name,
    title = "Hapus Data?",
    reload = null,
}) {
    Swal.fire({
        title: title,
        html: `Yakin ingin menghapus <b>${name}</b>?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc2626",
        cancelButtonColor: "#64748b",
        confirmButtonText: "Ya, Hapus",
        cancelButtonText: "Batal",
    }).then((result) => {
        if (!result.isConfirmed) return;

        $.ajax({
            url: url,
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr("content"),
                _method: "DELETE",
            },

            success(res) {
                Swal.fire({
                    icon: "success",
                    title: "Berhasil",
                    text: res.message,
                    timer: 1500,
                    showConfirmButton: false,
                });

                if (typeof reload === "function") {
                    reload();
                }
            },

            error(xhr) {
                Swal.fire({
                    icon: "error",
                    title: "Gagal",
                    text: xhr.responseJSON?.message ?? "Terjadi kesalahan.",
                });
            },
        });
    });
};
