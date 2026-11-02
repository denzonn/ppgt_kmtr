<div id="modalAnggota"
    class="modal-overlay fixed inset-0 z-[1000] hidden overflow-y-auto bg-slate-950/50 p-4 opacity-0 transition-opacity duration-300">

    <div
        class="modal-content mx-auto my-6 w-full max-w-4xl scale-95 -translate-y-6 rounded-2xl bg-white shadow-2xl transition-all duration-300">

        <form id="formAnggota">
            @csrf

            <input type="hidden" name="_method" id="methodAnggota" value="POST">

            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary/10 text-primary">
                        <i id="iconModalAnggota" class="fas fa-user-plus text-lg"></i>
                    </div>
                    <div>
                        <h3 id="judulModalAnggota" class="text-lg font-bold text-slate-800">Tambah Anggota</h3>
                        <p id="deskripsiModalAnggota" class="mt-0.5 text-sm text-slate-500">
                            Lengkapi informasi data anggota PPGT.
                        </p>
                    </div>
                </div>

                <button type="button" onclick="closeModal('modalAnggota')"
                    class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-6 p-6">
                <div>
                    <div class="mb-4 flex items-center gap-2">
                        <i class="fas fa-id-card text-primary"></i>
                        <h4 class="font-bold text-slate-800">Identitas Anggota</h4>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <div class="flex items-center gap-3 rounded-xl border border-primary/10 bg-primary/5 p-3.5">
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white text-primary">
                                    <i class="fas fa-hashtag"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-700">Nomor Anggota</p>
                                    <p class="mt-0.5 text-xs text-slate-500">
                                        Nomor anggota dibuat otomatis setelah data disimpan.
                                    </p>
                                </div>
                                <span id="nomorAnggotaInfo"
                                    class="ml-auto rounded-lg bg-white px-3 py-1.5 text-xs font-bold text-primary">
                                    Otomatis
                                </span>
                            </div>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">NIK</label>
                            <input type="text" name="nik" maxlength="16" inputmode="numeric"
                                placeholder="Masukkan 16 digit NIK"
                                class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10">
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nama" required placeholder="Masukkan nama lengkap"
                                class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10">
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                                Jenis Kelamin <span class="text-red-500">*</span>
                            </label>
                            <select name="jenis_kelamin" required
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10">
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                                Jemaat <span class="text-red-500">*</span>
                            </label>
                            <select name="jemaat_id" required
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10">
                                <option value="">Pilih Jemaat</option>
                                @foreach ($jemaat as $item)
                                    <option value="{{ $item->id }}">{{ $item->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" placeholder="Contoh: Makassar"
                                class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10">
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir"
                                class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10">
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-100"></div>

                <div>
                    <div class="mb-4 flex items-center gap-2">
                        <i class="fas fa-address-book text-primary"></i>
                        <h4 class="font-bold text-slate-800">Kontak dan Alamat</h4>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">Nomor Telepon /
                                WhatsApp</label>
                            <input type="text" name="telepon" placeholder="Contoh: 08xxxxxxxxxx"
                                class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10">
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">Email</label>
                            <input type="email" name="email" placeholder="nama@email.com"
                                class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">Alamat</label>
                            <textarea name="alamat" rows="3" placeholder="Masukkan alamat lengkap anggota..."
                                class="w-full resize-none rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10"></textarea>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-100"></div>

                <div>
                    <div class="mb-4 flex items-center gap-2">
                        <i class="fas fa-clipboard-list text-primary"></i>
                        <h4 class="font-bold text-slate-800">Data Tambahan</h4>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                                Status Wilayah <span class="text-red-500">*</span>
                            </label>
                            <select name="status_wilayah" required
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10">
                                <option value="">Pilih Status Wilayah</option>
                                <option value="dalam_wilayah">Dalam Wilayah Pelayanan</option>
                                <option value="luar_wilayah">Luar Wilayah</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">Golongan Darah</label>
                            <select name="golongan_darah"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10">
                                <option value="">Pilih Golongan Darah</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="AB">AB</option>
                                <option value="O">O</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">Rhesus</label>
                            <select name="golongan_darah_rhesus"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10">
                                <option value="">Pilih Rhesus</option>
                                <option value="Positif">Positif (+)</option>
                                <option value="Negatif">Negatif (-)</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">Pendidikan
                                Terakhir</label>
                            <select name="pendidikan"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10">
                                <option value="">Pilih Pendidikan</option>
                                <option value="SD">SD</option>
                                <option value="SMP">SMP</option>
                                <option value="SMA/SMK">SMA/SMK</option>
                                <option value="Diploma">Diploma</option>
                                <option value="S1">S1</option>
                                <option value="S2">S2</option>
                                <option value="S3">S3</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                                Status Anggota <span class="text-red-500">*</span>
                            </label>
                            <select name="status_anggota" required
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10">
                                <option value="Aktif">Aktif</option>
                                <option value="Tidak Aktif">Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div id="anggotaError"
                    class="hidden rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-600"></div>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 px-6 py-5 sm:flex-row sm:justify-end">
                <button type="button" onclick="closeModal('modalAnggota')"
                    class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                    Batal
                </button>

                <button type="submit" id="btnSimpanAnggota"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-60">
                    <i class="fas fa-save"></i>
                    Simpan Anggota
                </button>
            </div>
        </form>
    </div>
</div>
