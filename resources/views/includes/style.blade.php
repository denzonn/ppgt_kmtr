{{-- Google Fonts --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

{{-- Font Awesome --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />

<style>
    .swal2-popup {
        border-radius: 18px !important;
        padding: 1.5rem !important;
        font-family: 'Poppins', sans-serif;
    }

    .swal2-title {
        font-size: 1.4rem !important;
        font-weight: 700 !important;
        color: #1e293b !important;
    }

    .swal2-html-container {
        color: #64748b !important;
        font-size: .95rem !important;
    }

    .swal2-confirm {
        background: #2458D3 !important;
        border-radius: 10px !important;
        padding: .7rem 1.6rem !important;
        font-weight: 600;
    }

    .swal2-cancel {
        border-radius: 10px !important;
        padding: .7rem 1.6rem !important;
        font-weight: 600;
    }

    .swal2-icon {
        margin-top: 1rem !important;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Vite --}}
@vite(['resources/css/app.css', 'resources/js/app.js'])
