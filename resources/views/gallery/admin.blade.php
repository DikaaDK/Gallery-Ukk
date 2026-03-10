<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Gallery</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Nunito:wght@600&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f5f4f2] text-[#1c1f2b]">
    <main class="mx-auto w-full max-w-6xl px-4 py-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-col items-center gap-2 sm:flex-row sm:gap-3">
                <img width="60" height="60" src="https://img.icons8.com/color/48/ios-photos.png" alt="ios-photos"/>
                <div>
                    <h1 class="text-4xl font-semibold tracking-[0.02em] sm:text-5xl" style="font-family: 'Nunito', 'Inter', sans-serif;">Gallery</h1>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#6b7280]">Admin Mode</p>
                </div>
            </div>
            <a href="{{ route('gallery.preview') }}" class="rounded-xl border border-[#d1d5db] bg-white px-4 py-2 text-sm font-semibold text-[#111827]">
                Kembali ke User View
            </a>
        </div>

        @if (session('status'))
            <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="mt-4 text-[0.85rem] uppercase tracking-[0.25em] text-[#777d96]">
            <span class="inline-flex border-b-2 border-[#BFC9D1] pb-1 text-[#1c1f2b]">Manage Photos</span>
            <button id="select-mode-toggle" type="button" class="ml-4 inline-flex border-b-2 border-transparent pb-1 text-[#777d96] hover:text-[#1c1f2b]">Select</button>
        </div>

        <section class="mt-8">
            <form id="bulk-delete-form" action="{{ route('admin.photos.delete-selected') }}" method="POST">
                @csrf
                <div id="bulk-delete-toolbar" class="mb-4 hidden items-center justify-between rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3">
                    <p id="selected-count-text" class="text-sm font-semibold text-rose-700">0 foto dipilih</p>
                    <div class="flex items-center gap-2">
                        <button id="cancel-select" type="button" class="rounded-lg border border-[#d1d5db] bg-white px-3 py-2 text-xs font-semibold text-[#111827]">Batal</button>
                        <button type="submit" class="rounded-lg border border-rose-600 bg-rose-600 px-3 py-2 text-xs font-semibold text-white">Delete Selected</button>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-5 md:grid-cols-3 sm:grid-cols-2">
                @forelse($photos as $photo)
                    <article class="relative flex h-64 flex-col overflow-hidden rounded-lg border border-[#ececf0] bg-white">
                        <input
                            type="checkbox"
                            name="photo_ids[]"
                            value="{{ $photo['id'] }}"
                            class="photo-select-checkbox absolute left-3 top-3 z-20 hidden h-5 w-5 rounded border-[#d1d5db] text-rose-600"
                        >
                        <div class="relative h-60 overflow-hidden bg-[#f0f0f5]">
                            @if($photo['cover'])
                                <img src="{{ $photo['cover'] }}" alt="{{ $photo['title'] }}" class="h-full w-full object-cover">
                            @else
                                <div class="absolute inset-0 bg-gradient-to-br from-[#e4e4ea] to-[#f6f6fb]"></div>
                            @endif
                            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent p-4">
                                <p class="text-sm font-semibold text-white/90">{{ $photo['title'] }}</p>
                            </div>
                        </div>
                        <div class="flex flex-1 flex-col gap-2 px-4 py-3">
                            <p class="line-clamp-1 text-sm font-semibold text-[#1c1f2b]">{{ \Illuminate\Support\Str::limit($photo['description'] ?? 'Belum ada deskripsi', 70) }}</p>
                            <p class="text-xs text-[#6b7280]">{{ $photo['owner'] }} | {{ $photo['album'] }}</p>
                            <div class="flex items-center gap-2">
                                <select class="album-select w-full rounded-lg border border-[#d1d5db] px-2 py-1.5 text-xs" data-photo-id="{{ $photo['id'] }}">
                                    <option value="">Tanpa album</option>
                                    @foreach($albums as $album)
                                        <option value="{{ $album['id'] }}" {{ (string) ($photo['album_id'] ?? '') === (string) $album['id'] ? 'selected' : '' }}>
                                            {{ $album['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" class="save-album rounded-lg border border-[#d1d5db] bg-white px-2 py-1.5 text-xs font-semibold text-[#111827]" data-photo-id="{{ $photo['id'] }}">
                                    Simpan
                                </button>
                            </div>
                            <div class="mt-auto flex items-center justify-between">
                                <span class="text-xs text-[#6b7280]">{{ $photo['uploaded_at'] ? \Illuminate\Support\Carbon::parse($photo['uploaded_at'])->format('d M Y') : '-' }}</span>
                                <form method="POST" action="{{ route('admin.photos.delete', ['photo' => $photo['id']]) }}" onsubmit="return confirm('Hapus foto ini?')">
                                    @csrf
                                    <button type="submit" class="rounded-lg border border-rose-600 bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full rounded-[26px] border border-dashed border-[#ececf0] bg-white/80 p-8 text-center text-sm text-[#777d96]">
                        Belum ada foto untuk dikelola.
                    </div>
                @endforelse
                </div>
            </form>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const updateAlbumBase = "{{ url('/admin/photos') }}";

            const selectModeToggle = document.getElementById('select-mode-toggle');
            const bulkDeleteToolbar = document.getElementById('bulk-delete-toolbar');
            const selectedCountText = document.getElementById('selected-count-text');
            const cancelSelectButton = document.getElementById('cancel-select');
            const bulkDeleteForm = document.getElementById('bulk-delete-form');
            const checkboxes = document.querySelectorAll('.photo-select-checkbox');
            const saveButtons = document.querySelectorAll('.save-album');
            let selectMode = false;

            const savePhotoAlbum = async (photoId, albumId, button) => {
                const prevText = button.textContent;
                button.disabled = true;
                button.textContent = 'Saving...';

                try {
                    const response = await fetch(`${updateAlbumBase}/${photoId}/album`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({ album_id: albumId || null }),
                    });

                    if (!response.ok) {
                        throw new Error('Gagal menyimpan album.');
                    }

                    button.textContent = 'Saved';
                    setTimeout(() => {
                        button.textContent = prevText;
                    }, 900);
                } catch (error) {
                    alert(error.message || 'Terjadi kesalahan saat menyimpan album.');
                    button.textContent = prevText;
                } finally {
                    button.disabled = false;
                }
            };

            const updateSelectedCount = () => {
                const selected = Array.from(checkboxes).filter((checkbox) => checkbox.checked).length;
                if (selectedCountText) {
                    selectedCountText.textContent = `${selected} foto dipilih`;
                }
                return selected;
            };

            const setSelectMode = (enabled) => {
                selectMode = enabled;
                checkboxes.forEach((checkbox) => {
                    checkbox.classList.toggle('hidden', !enabled);
                    if (!enabled) {
                        checkbox.checked = false;
                    }
                });
                bulkDeleteToolbar?.classList.toggle('hidden', !enabled);
                bulkDeleteToolbar?.classList.toggle('flex', enabled);
                updateSelectedCount();
            };

            selectModeToggle?.addEventListener('click', () => setSelectMode(!selectMode));
            cancelSelectButton?.addEventListener('click', () => setSelectMode(false));
            checkboxes.forEach((checkbox) => checkbox.addEventListener('change', updateSelectedCount));

            saveButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const photoId = button.getAttribute('data-photo-id');
                    const select = document.querySelector(`.album-select[data-photo-id="${photoId}"]`);
                    if (!photoId || !select) {
                        return;
                    }

                    savePhotoAlbum(photoId, select.value, button);
                });
            });

            bulkDeleteForm?.addEventListener('submit', (event) => {
                if (!selectMode) {
                    event.preventDefault();
                    return;
                }

                const selected = updateSelectedCount();
                if (selected < 1) {
                    event.preventDefault();
                    alert('Pilih minimal satu foto dulu.');
                    return;
                }

                if (!window.confirm(`Hapus ${selected} foto terpilih?`)) {
                    event.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
