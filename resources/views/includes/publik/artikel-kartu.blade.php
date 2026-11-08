<article class="group relative flex flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white transition hover:border-primary hover:shadow-xl hover:shadow-blue-950/10">
    <img src="{{ $artikel->foto ?: asset('images/no-image.svg') }}" alt="" loading="lazy" class="aspect-video w-full bg-slate-100 object-cover">

    <div class="flex flex-1 flex-col p-6">
        <p class="pil-publik self-start py-0.5 normal-case tracking-normal">
            <span class="h-1.5 w-1.5 rounded-full bg-blue-600" aria-hidden="true"></span>{{ $artikel->jemaat?->nama ? 'Jemaat '.$artikel->jemaat->nama : 'PPGT KMTr' }}
        </p>
        <h3 class="mt-3 text-lg font-bold leading-snug text-slate-900 group-hover:text-primary">
            {{-- Tautan melebar ke seluruh kartu lewat after:inset-0. --}}
            <a href="{{ $artikel->urlBaca() }}" class="after:absolute after:inset-0">{{ $artikel->judul }}</a>
        </h3>
        <p class="mt-2 flex-1 text-sm leading-6 text-slate-500">{{ \App\Support\KontenArtikel::ringkasan($artikel->isi) }}</p>
        <p class="mt-4 flex flex-wrap justify-between gap-x-4 gap-y-1 border-t border-slate-100 pt-4 text-xs text-slate-500">
            <span><i class="fa-regular fa-user mr-1" aria-hidden="true"></i>{{ $artikel->penulis }}</span>
            <span><i class="fa-regular fa-calendar mr-1" aria-hidden="true"></i>{{ $artikel->tanggalTayang() }}</span>
        </p>
    </div>
</article>
