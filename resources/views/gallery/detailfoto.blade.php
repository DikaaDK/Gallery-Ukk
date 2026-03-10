<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $photo['title'] }} - Detail Foto</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f5f4f2] text-[#1c1f2b]" style="font-family: 'Inter', sans-serif;">
    <main class="mx-auto w-full max-w-6xl px-4 py-8">
        <a href="{{ route('gallery.preview') }}" class="items-center gap-2 px-4 py-2 text-xs font-semibold inline-flex pb-1 border-b-2 text-[#1c1f2b]">
            Kembali ke Gallery
        </a>

        @if (session('success'))
            <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        <section class="mt-6 grid gap-6 lg:grid-cols-[1.15fr_0.85fr]">
            <article class="overflow-hidden rounded-[30px] border border-[#ececf0] bg-white shadow-[0_20px_40px_rgba(15,23,42,0.08)]">
                <div class="h-[420px] bg-[#ececf2]">
                    @if($photo['cover'])
                        <img src="{{ $photo['cover'] }}" alt="{{ $photo['title'] }}" class="h-full w-full object-cover">
                    @else
                        <div class="h-full w-full bg-gradient-to-br from-[#dadbe3] to-[#f3f4f8]"></div>
                    @endif
                </div>
            </article>

            <article class="rounded-[30px] border border-[#ececf0] bg-white p-6 shadow-[0_20px_40px_rgba(15,23,42,0.08)]">
                <p class="text-xs text-[#6b7280]">Detail Foto</p>
                <h1 class="mt-2 text-3xl font-bold leading-tight text-[#111827]">{{ $photo['title'] }}</h1>
                <p class="mt-2 text-sm text-[#4b5563]">{{ $photo['description'] ?: 'Tidak ada deskripsi.' }}</p>

                <div class="mt-5 space-y-1 text-xs font-semibold text-[#6b7280]">
                    <p>Uploader: <span class="text-[#111827]">{{ $photo['owner'] }}</span></p>
                    <p>Tanggal: <span class="text-[#111827]">{{ $photo['uploaded_at'] ? \Illuminate\Support\Carbon::parse($photo['uploaded_at'])->format('d M Y') : '-' }}</span></p>
                    <p>{{ $photo['likes'] }} likes · {{ $photo['comments'] }} comments</p>
                </div>

                <div class="mt-6 flex items-center gap-3">
                    <form method="POST" action="{{ route('gallery.like') }}">
                        @csrf
                        <input type="hidden" name="photo_id" value="{{ $photo['id'] }}">
                        <button
                            type="submit"
                            aria-label="{{ $photo['liked'] ? 'Batal like foto' : 'Like foto' }}"
                            class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-[#d1d5db] {{ $photo['liked'] ? 'bg-[#ff000d] border-[#111827]' : 'bg-white' }}"
                        >
                            <img
                                src="{{ $photo['liked'] ? 'https://img.icons8.com/fluency-systems-filled/24/ffffff/like.png' : 'https://img.icons8.com/ios/24/1c1f2b/like--v1.png' }}"
                                alt="Like"
                                class="h-5 w-5"
                            >
                        </button>
                    </form>
                </div>

                @if(!empty($photo['can_manage_album']))
                    <form method="POST" action="{{ route('gallery.photo.album.update', ['photo' => $photo['id']]) }}" class="mt-6 space-y-2">
                        @csrf
                        <label class="text-xs font-semibold text-[#1c1f2b]">Pindahkan ke Album</label>
                        <div class="flex items-center gap-2">
                            <select name="album_id" class="w-full rounded-2xl border border-[#d1d5eb] px-4 py-2 text-sm">
                                <option value="">Tanpa album</option>
                                @foreach(($albums ?? []) as $album)
                                    <option value="{{ $album['id'] }}" {{ (string) ($photo['current_album_id'] ?? '') === (string) $album['id'] ? 'selected' : '' }}>
                                        {{ $album['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="rounded-2xl bg-[#111827] px-4 py-2 text-xs font-semibold text-white">Simpan</button>
                        </div>
                    </form>
                @endif

                <form method="POST" action="{{ route('gallery.photo.comment', ['photo' => $photo['id']]) }}" class="mt-6 space-y-3">
                    @csrf
                    <label class="text-xs font-semibold text-[#1c1f2b]">Tambah Komentar</label>
                    <textarea name="IsiKomentar" rows="4" class="w-full rounded-2xl border border-[#d1d5eb] px-4 py-3 text-sm" placeholder="Tulis komentar..." required></textarea>
                    <button type="submit" class="rounded-2xl bg-[#111827] px-5 py-3 text-xs font-semibold text-white">Kirim Komentar</button>
                </form>
            </article>
        </section>

        <section class="mt-8 rounded-[30px] border border-[#ececf0] bg-white p-6 shadow-[0_20px_40px_rgba(15,23,42,0.08)]">
            <h2 class="text-lg font-semibold text-[#111827]">Komentar</h2>
            <div class="mt-4 space-y-3">
                @forelse($comments as $comment)
                    <article class="rounded-2xl bg-[#f9fafb] px-4 py-3">
                        <div class="flex items-center justify-between text-[#6b7280]">
                            <span class="font-semibold text-[#111827]">{{ $comment->NamaLengkap }}</span>
                            <span class="text-sm">{{ $comment->TanggalKomentar ? \Illuminate\Support\Carbon::parse($comment->TanggalKomentar)->format('d M Y') : '-' }}</span>
                        </div>
                        <p class="mt-2 text-sm text-[#1f2937]">{{ $comment->IsiKomentar }}</p>
                    </article>
                @empty
                    <p class="text-sm text-[#6b7280]">Belum ada komentar pada foto ini.</p>
                @endforelse
            </div>
        </section>
    </main>
</body>
</html>
