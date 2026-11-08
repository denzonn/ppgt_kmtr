import {
    Alignment,
    Autoformat,
    AutoLink,
    BlockQuote,
    Bold,
    ClassicEditor,
    Essentials,
    Heading,
    HorizontalLine,
    Image,
    ImageCaption,
    ImageStyle,
    ImageToolbar,
    ImageUpload,
    Indent,
    Italic,
    Link,
    List,
    Paragraph,
    PasteFromOffice,
    RemoveFormat,
    SimpleUploadAdapter,
    Strikethrough,
    Table,
    TableToolbar,
    Underline,
} from 'ckeditor5';
import translations from 'ckeditor5/translations/id.js';

import 'ckeditor5/ckeditor5.css';
import '../css/konten-artikel.css';

// Daftar ini harus sejalan dengan App\Support\KontenArtikel, yang menyaring hasilnya di server.
window.buatEditorArtikel = (elemen, { uploadUrl, csrf }) => ClassicEditor.create(elemen, {
    licenseKey: 'GPL',
    language: 'id',
    translations: [translations],
    placeholder: 'Tulis isi artikel di sini',
    // Toolbar menempel di bawah navbar saat tulisan digulir.
    ui: { viewportOffset: { top: 80 } },
    plugins: [
        Alignment, Autoformat, AutoLink, BlockQuote, Bold, Essentials, Heading, HorizontalLine, Image, ImageCaption,
        ImageStyle, ImageToolbar, ImageUpload, Indent, Italic, Link, List, Paragraph, PasteFromOffice, RemoveFormat,
        SimpleUploadAdapter, Strikethrough, Table, TableToolbar, Underline,
    ],
    toolbar: {
        // Tombol turun baris supaya semua fitur langsung terlihat.
        shouldNotGroupWhenFull: true,
        items: [
            'undo', 'redo', '|', 'heading', '|', 'bold', 'italic', 'underline', 'strikethrough', 'removeFormat', '|',
            'link', 'bulletedList', 'numberedList', 'outdent', 'indent', '|', 'alignment', '|',
            'blockQuote', 'insertTable', 'uploadImage', 'horizontalLine',
        ],
    },
    heading: {
        options: [
            { model: 'paragraph', title: 'Paragraf', class: 'ck-heading_paragraph' },
            { model: 'heading2', view: 'h2', title: 'Judul Besar', class: 'ck-heading_heading2' },
            { model: 'heading3', view: 'h3', title: 'Judul Sedang', class: 'ck-heading_heading3' },
            { model: 'heading4', view: 'h4', title: 'Judul Kecil', class: 'ck-heading_heading4' },
        ],
    },
    // Memakai class, bukan style, karena atribut style dibuang saat disaring.
    alignment: {
        options: [
            { name: 'left', className: 'ta-left' },
            { name: 'center', className: 'ta-center' },
            { name: 'right', className: 'ta-right' },
            { name: 'justify', className: 'ta-justify' },
        ],
    },
    image: {
        toolbar: ['imageStyle:block', 'imageStyle:side', '|', 'toggleImageCaption', 'imageTextAlternative'],
        styles: { options: ['block', 'side'] },
        upload: { types: ['jpeg', 'png', 'gif'] },
    },
    table: {
        contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells'],
    },
    link: {
        addTargetToExternalLinks: true,
        defaultProtocol: 'https://',
    },
    simpleUpload: {
        uploadUrl,
        headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
    },
});
