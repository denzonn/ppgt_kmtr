@push('addon-style')
    <style>
        /* Motif hanya di sudut atas dan bawah, tengah halaman tetap polos. */
        .motif-latar {
            position: relative;
            overflow: hidden;
            background-image:
                radial-gradient(circle at 100% 0, rgba(30, 58, 138, .10) 0 150px, transparent 151px),
                radial-gradient(circle at 0 100%, rgba(37, 99, 235, .08) 0 190px, transparent 191px);
        }

        .motif-latar::before,
        .motif-latar::after {
            content: "";
            position: absolute;
            width: 300px;
            height: 300px;
            pointer-events: none;
            background-image: radial-gradient(rgba(30, 58, 138, .30) 1.5px, transparent 1.5px);
            background-size: 18px 18px;
            -webkit-mask-image: radial-gradient(circle, #000 25%, transparent 70%);
            mask-image: radial-gradient(circle, #000 25%, transparent 70%);
        }

        .motif-latar::before {
            top: -40px;
            left: -60px;
        }

        .motif-latar::after {
            right: -60px;
            bottom: -40px;
        }

        .motif-latar > * {
            position: relative;
            z-index: 1;
        }
    </style>
@endpush
