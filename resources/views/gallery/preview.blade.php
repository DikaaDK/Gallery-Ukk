<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery Preview</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Nunito:wght@600&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .like-badge {
            position: absolute;
            right: 1rem;
            top: 1rem;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.65rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #111827;
            background: rgba(249, 115, 22, 0.9);
            opacity: 0;
            transform: translateY(6px) scale(0.95);
            transition: opacity 0.25s ease, transform 0.25s ease;
            pointer-events: none;
        }

        .like-badge--visible {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    </style>
</head>
<body class="min-h-screen bg-[#f5f4f2] text-[#1c1f2b]">
    <div id="gallery-root" class="min-h-screen flex items-start justify-center px-4 pt-8" data-has-album="{{ $hasAlbum ? '1' : '0' }}">
        @php
            $likeOutlinedIcon = 'https://img.icons8.com/ios/24/1c1f2b/like--v1.png';
            $likeFilledIcon = 'https://img.icons8.com/fluency-systems-filled/24/1c1f2b/like.png';
        @endphp
        <div class="w-full max-w-5xl">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-col items-center gap-2 sm:flex-row sm:gap-3">
                    <img width="60" height="60" src="https://img.icons8.com/color/48/ios-photos.png" alt="ios-photos"/>
                    <h1 class="text-4xl font-semibold tracking-[0.02em] sm:text-5xl" style="font-family: 'Nunito', 'Inter', sans-serif;">Gallery</h1>
                </div>
                <div class="flex items-center gap-3">
                    @if(strtolower((string) (auth()->user()->role ?? 'user')) === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center rounded-xl border border-[#d1d5db] bg-white px-3 py-2 text-xs font-semibold text-[#111827]">
                            Admin View
                        </a>
                    @endif
                        <button id="add-photo-button" type="button" class="flex h-12 w-12 items-center justify-center" title="Unggah foto baru" data-add-mode="pictures">
                        <img src="https://img.icons8.com/fluency-systems-regular/48/add--v1.png" alt="Add album" class="h-6 w-6" />
                    </button>
                    <div class="relative">
                        <button id="profile-menu-button" type="button" class="flex h-12 w-12 items-center justify-center" aria-haspopup="true" aria-expanded="false" aria-controls="profile-menu">
                            <img src="https://img.icons8.com/fluency-systems-regular/48/user-male-circle--v1.png" alt="Profile" class="h-6 w-6" />
                        </button>
                        <div id="profile-menu" class="absolute right-0 top-12 z-30 hidden min-w-[140px] rounded-xl border border-[#e5e7eb] bg-white p-2 shadow-[0_12px_30px_rgba(15,23,42,0.15)]">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full rounded-lg px-3 py-2 text-left text-xs font-semibold text-[#111827] hover:bg-[#f3f4f6]">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            @if(!$hasAlbum)
                <div class="mt-4 flex flex-col gap-2 rounded-2xl border border-[#e2e8f0] bg-white/80 px-6 py-4 text-sm text-[#4b5563] shadow-sm sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-[#111827]">Belum ada album</p>
                        <p class="text-xs text-[#6b7280]">Foto tetap bisa diunggah. Anda bisa memindahkannya ke album nanti.</p>
                    </div>
                    <button id="add-album-button" type="button" class="inline-flex items-center gap-2 rounded-[20px] border border-[#d1d5db] bg-[#0f172a] px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-white shadow-[0_15px_30px_rgba(15,23,42,0.25)]">Buat album</button>
                </div>
            @endif

            <div class="mt-4 flex flex-wrap gap-4 text-[0.85rem] text-[#777d96]">
                <button type="button" data-category-toggle="pictures" class="category-tab inline-flex pb-1 border-b-2 text-[#1c1f2b]">Pictures</button>
                <button type="button" data-category-toggle="albums" class="category-tab inline-flex pb-1 border-b-2  text-[#777d96]">Albums</button>
            </div>

            <div class="mt-8">
                <div id="gallery-pictures" class="grid gap-6 lg:grid-cols-4 md:grid-cols-3 sm:grid-cols-2">
                    @forelse($photos as $photo)
                        <article
                            class="photo-card group relative flex h-76 flex-col overflow-hidden rounded-lg border border-[#ececf0] bg-white"
                            data-photo-id="{{ $photo['id'] }}"
                            data-album-id="{{ $photo['album_id'] ?? '' }}"
                            data-detail-url="{{ route('gallery.photo.show', ['photo' => $photo['id']]) }}"
                        >
                            <a
                                href="{{ route('gallery.photo.show', ['photo' => $photo['id']]) }}"
                                class="absolute inset-0 z-10"
                                aria-label="Buka detail {{ $photo['title'] }}"
                            ></a>
                            <div class="photo-frame relative h-60 overflow-hidden bg-[#f0f0f5]" role="button" aria-label="Lihat detail {{ $photo['title'] }}">
                                @if($photo['cover'])
                                    <img src="{{ $photo['cover'] }}" alt="{{ $photo['title'] }}" class="h-full w-full object-cover">
                                @else
                                    <div class="absolute inset-0 bg-gradient-to-br from-[#e4e4ea] to-[#f6f6fb]"></div>
                                @endif
                                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent p-4">
                                    <p class="text-sm font-semibold text-white/90">{{ $photo['title'] }}</p>
                                </div>
                                <span class="like-badge">Disukai!</span>
                            </div>
                            <div class="relative flex flex-1 flex-col gap-3 px-4 py-3">
                                <p class="text-sm font-semibold text-[#1c1f2b]">{{ \Illuminate\Support\Str::limit($photo['description'] ?? 'Belum ada deskripsi', 90) }}</p>
                                <div class="flex items-center justify-between text-[#6b7280]">
                                    <span>{{ $photo['uploaded_at'] ? \Illuminate\Support\Carbon::parse($photo['uploaded_at'])->format('d M Y') : '-' }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="text-[0.72rem] font-semibold text-[#4b5563]">
                                        <span data-like-count-display>{{ $photo['likes'] }} likes</span>
                                        <span class="mx-1 text-[#9ca3af]">•</span>
                                        <span data-comment-count-display>{{ $photo['comments'] }} comments</span>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="col-span-full text-center text-sm text-[#777d96]">Belum ada foto untuk ditampilkan.</div>
                    @endforelse
                </div>

                <div id="album-filter-bar" class="mt-4 hidden items-center justify-between rounded-xl border border-[#dbeafe] bg-[#eff6ff] px-4 py-3 text-sm text-[#1e3a8a]">
                    <p id="album-filter-text" class="font-semibold">Menampilkan foto berdasarkan album.</p>
                    <button id="album-filter-reset" type="button" class="rounded-lg border border-[#93c5fd] bg-white px-3 py-1.5 text-xs font-semibold text-[#1d4ed8]">
                        Tampilkan semua foto
                    </button>
                </div>

                <div id="gallery-albums" class="mt-6 hidden grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @forelse($albums as $album)
                        <article class="album-card flex h-76 flex-col gap-3 border rounded-lg border-[#ececf0] bg-white shadow-[0_12px_35px_rgba(15,23,42,0.08)] p-5 cursor-pointer" data-album-id="{{ $album['id'] }}" data-album-name="{{ $album['name'] }}">
                            <div class="h-28 w-full rounded-[20px]">
                                <div class="items-center justify-center rounded-[20px] flex h-full w-full">
                                    <img width="64" height="64" src="https://img.icons8.com/fluency-systems-regular/48/stack-of-photos--v1.png" alt="stack-of-photos--v1"/>
                                </div>
                            </div>
                            <p class="text-lg font-semibold leading-tight text-[#111827]">{{ $album['name'] }}</p>
                            <p class="text-[0.85rem] text-[#4b5563]">{{ \Illuminate\Support\Str::limit($album['description'] ?? 'Tidak ada deskripsi.', 100) }}</p>
                            <p class="text-[0.55rem] text-[#1c1f2b]">{{ $album['created_at'] ?? '-' }}</p>
                        </article>
                    @empty
                        <div class="col-span-full rounded-[26px] border border-dashed border-[#ececf0] bg-white/80 p-8 text-center text-sm text-[#777d96]">Belum ada album untuk ditampilkan.</div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    <div id="photo-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4">
        <div id="photo-modal-backdrop" class="absolute inset-0 bg-black/60"></div>
        <div class="relative w-full max-w-md rounded-3xl bg-white p-6 shadow-[0_25px_60px_rgba(15,23,42,0.25)]">
            <button id="photo-modal-close" class="absolute right-4 top-4 text-xl font-semibold text-[#777d96]">&times;</button>
            <h2 class="text-lg font-semibold">Tambah foto</h2>
            <p class="mt-1 text-sm text-[#6b7280]">Isi detail sebelum mengunggah.</p>
            <form id="upload-form" action="{{ route('gallery.upload') }}" method="POST" enctype="multipart/form-data" class="mt-5 flex flex-col gap-4">
                @csrf
                <label class="text-sm font-medium text-[#111827]">
                    Judul Foto
                    <input name="judul" type="text" class="mt-1 w-full rounded-2xl border border-[#e5e7eb] px-4 py-3 text-sm" placeholder="Misalnya: Liburan di Bali" required>
                </label>
                <label class="text-sm font-medium text-[#111827]">
                    Deskripsi Foto
                    <textarea name="deskripsi" rows="3" class="mt-1 w-full rounded-2xl border border-[#e5e7eb] px-4 py-3 text-sm" placeholder="Deskripsikan momen dalam foto"></textarea>
                </label>
                <label class="text-sm font-medium text-[#111827]">
                    Masukkan ke album
                    <select name="album_id" class="mt-1 w-full rounded-2xl border border-[#e5e7eb] px-4 py-3 text-sm">
                        <option value="">Tanpa album</option>
                        @foreach($albums as $album)
                            <option value="{{ $album['id'] }}">{{ $album['name'] }}</option>
                        @endforeach
                    </select>
                </label>
                <input id="file-input" name="photo" type="file" accept="image/*" class="hidden">
                <button id="select-file" type="button" class="rounded-2xl border border-transparent bg-[#1f2937] py-3 text-sm font-semibold text-white">Pilih dari file</button>
            </form>
        </div>
    </div>

    <div id="album-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4">
        <div id="album-modal-backdrop" class="absolute inset-0 bg-black/60"></div>
        <div class="relative w-full max-w-md rounded-3xl bg-white p-6 shadow-[0_25px_60px_rgba(15,23,42,0.25)]">
            <button id="album-modal-close" class="absolute right-4 top-4 text-xl font-semibold text-[#777d96]">&times;</button>
            <h2 class="text-lg font-semibold">Buat album</h2>
            <p class="mt-1 text-sm text-[#6b7280]">Masukkan nama dan deskripsi untuk album baru Anda.</p>
            <form id="album-form" action="{{ route('gallery.album.store') }}" method="POST" class="mt-5 flex flex-col gap-4">
                @csrf
                <label class="text-sm font-medium text-[#111827]">
                    Nama Album
                    <input name="nama" type="text" class="mt-1 w-full rounded-2xl border border-[#e5e7eb] px-4 py-3 text-sm" placeholder="Misalnya: Liburan keluarga" required>
                </label>
                <label class="text-sm font-medium text-[#111827]">
                    Deskripsi Album
                    <textarea name="deskripsi" rows="3" class="mt-1 w-full rounded-2xl border border-[#e5e7eb] px-4 py-3 text-sm" placeholder="Tulis catatan untuk album ini"></textarea>
                </label>
                <button type="submit" class="rounded-2xl border border-transparent bg-[#1f2937] py-3 text-sm font-semibold text-white">Buat album</button>
            </form>
        </div>
    </div>

    <div id="detail-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4 py-6">
        <div id="detail-modal-backdrop" class="absolute inset-0 bg-black/60"></div>
        <div class="relative z-10 w-full max-w-5xl overflow-hidden rounded-[32px] bg-white p-6 shadow-[0_25px_60px_rgba(15,23,42,0.25)]">
            <button id="detail-modal-close" class="absolute right-4 top-4 text-2xl font-semibold text-[#777d96]">&times;</button>
            <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                <div class="rounded-[30px] bg-[#f5f5fa]">
                    <img id="detail-photo-image" src="" alt="Detail foto" class="h-full w-full rounded-[30px] object-cover">
                </div>
                <div class="flex flex-col gap-4">
                    <div>
                        <p id="detail-photo-title" class="text-2xl font-semibold text-[#111827]"></p>
                        <p id="detail-photo-date" class="text-[0.65rem] uppercase tracking-[0.4em] text-[#6b7280]"></p>
                    </div>
                    <p id="detail-photo-description" class="text-sm text-[#4b5563]"></p>
                    <div class="flex flex-wrap items-center gap-4">
                        <button id="detail-like-button" type="button" class="like-trigger inline-flex items-center gap-2 rounded-full border border-[#d1d5db] bg-white px-4 py-2 text-[0.65rem] font-semibold uppercase tracking-[0.3em] text-[#1c1f2b]">
                            <img id="detail-like-icon" class="like-icon" src="{{ $likeOutlinedIcon ?? 'https://img.icons8.com/ios/24/1c1f2b/like--v1.png' }}" data-outlined="{{ $likeOutlinedIcon ?? 'https://img.icons8.com/ios/24/1c1f2b/like--v1.png' }}" data-filled="{{ $likeFilledIcon ?? 'https://img.icons8.com/fluency-systems-filled/24/1c1f2b/like.png' }}" alt="Like icon">
                            <span>Like</span>
                        </button>
                        <div>
                            <p id="detail-like-count" class="text-[0.7rem] uppercase tracking-[0.3em] text-[#6b7280]"></p>
                            <p id="detail-comment-count" class="text-[0.65rem] uppercase tracking-[0.3em] text-[#6b7280]"></p>
                        </div>
                    </div>
                    <div class="flex-1 overflow-y-auto rounded-2xl border border-[#e5e7eb] p-4" style="max-height: 280px;">
                        <div id="detail-comments-list" class="flex flex-col gap-3 text-sm text-[#1c1f2b]"></div>
                    </div>
                    <form id="detail-comment-form" class="flex flex-col gap-3">
                        <label class="text-[0.65rem] font-semibold uppercase tracking-[0.35em] text-[#1c1f2b]">Tambahkan komentar</label>
                        <textarea name="IsiKomentar" rows="3" class="w-full rounded-2xl border border-[#d1d5eb] px-4 py-3 text-sm" placeholder="Tulis komentar..." required></textarea>
                        <button type="submit" class="rounded-2xl bg-[#111827] py-3 text-[0.75rem] font-semibold uppercase tracking-[0.3em] text-white">Kirim komentar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const galleryRoot = document.getElementById('gallery-root');
            const hasAlbum = galleryRoot?.dataset.hasAlbum === '1';

            const photoModal = document.getElementById('photo-modal');
            const photoBackdrop = document.getElementById('photo-modal-backdrop');
            const addButton = document.getElementById('add-photo-button');
            const photoCloseButton = document.getElementById('photo-modal-close');
            const selectButton = document.getElementById('select-file');
            const fileInput = document.getElementById('file-input');
            const uploadForm = document.getElementById('upload-form');

            const hidePhotoModal = () => photoModal?.classList.add('hidden');
            const showPhotoModal = () => photoModal?.classList.remove('hidden');

            photoCloseButton?.addEventListener('click', hidePhotoModal);
            photoModal?.addEventListener('click', function (event) {
                if (event.target === photoModal || event.target === photoBackdrop) {
                    hidePhotoModal();
                }
            });

            selectButton?.addEventListener('click', () => fileInput?.click());
            fileInput?.addEventListener('change', () => {
                if (uploadForm && uploadForm.checkValidity && !uploadForm.checkValidity()) {
                    uploadForm.reportValidity();
                    return;
                }

                hidePhotoModal();
                uploadForm?.submit();
            });

            const albumModal = document.getElementById('album-modal');
            const albumBackdrop = document.getElementById('album-modal-backdrop');
            const albumOpenButton = document.getElementById('add-album-button');
            const albumCloseButton = document.getElementById('album-modal-close');
            const profileMenuButton = document.getElementById('profile-menu-button');
            const profileMenu = document.getElementById('profile-menu');

            const hideProfileMenu = () => {
                profileMenu?.classList.add('hidden');
                profileMenuButton?.setAttribute('aria-expanded', 'false');
            };

            const toggleProfileMenu = () => {
                if (!profileMenu || !profileMenuButton) {
                    return;
                }

                const willOpen = profileMenu.classList.contains('hidden');
                profileMenu.classList.toggle('hidden', !willOpen);
                profileMenuButton.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            };

            profileMenuButton?.addEventListener('click', (event) => {
                event.stopPropagation();
                toggleProfileMenu();
            });

            document.addEventListener('click', (event) => {
                if (!profileMenu || !profileMenuButton) {
                    return;
                }

                if (!profileMenu.contains(event.target) && !profileMenuButton.contains(event.target)) {
                    hideProfileMenu();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    hideProfileMenu();
                }
            });

            const showAlbumModal = () => albumModal?.classList.remove('hidden');
            const hideAlbumModal = () => albumModal?.classList.add('hidden');

            albumOpenButton?.addEventListener('click', showAlbumModal);
            albumCloseButton?.addEventListener('click', hideAlbumModal);
            albumModal?.addEventListener('click', (event) => {
                if (event.target === albumModal || event.target === albumBackdrop) {
                    hideAlbumModal();
                }
            });

            const handleAddAction = () => {
                const mode = addButton?.dataset.addMode ?? 'pictures';
                if (mode === 'albums') {
                    showAlbumModal();
                    return;
                }

                showPhotoModal();
            };

            const categoryButtons = document.querySelectorAll('[data-category-toggle]');
            const photoCards = document.querySelectorAll('.photo-card');
            const albumCards = document.querySelectorAll('.album-card');
            const albumFilterBar = document.getElementById('album-filter-bar');
            const albumFilterText = document.getElementById('album-filter-text');
            const albumFilterReset = document.getElementById('album-filter-reset');
            const gallerySections = {
                pictures: document.getElementById('gallery-pictures'),
                albums: document.getElementById('gallery-albums'),
            };
            let activeAlbumFilter = null;

            const applyAlbumFilter = (albumId, albumName) => {
                activeAlbumFilter = albumId;
                let visibleCount = 0;

                photoCards.forEach((card) => {
                    const isMatch = String(card.dataset.albumId || '') === String(albumId || '');
                    card.classList.toggle('hidden', !isMatch);
                    if (isMatch) {
                        visibleCount += 1;
                    }
                });

                if (albumFilterText) {
                    albumFilterText.textContent = visibleCount > 0
                        ? `Album ${albumName}: ${visibleCount} foto ditemukan.`
                        : `Album ${albumName}: belum ada foto.`;
                }

                albumFilterBar?.classList.remove('hidden');
                albumFilterBar?.classList.add('flex');
                setActiveCategory('pictures');
            };

            const resetAlbumFilter = () => {
                activeAlbumFilter = null;
                photoCards.forEach((card) => card.classList.remove('hidden'));
                albumFilterBar?.classList.add('hidden');
                albumFilterBar?.classList.remove('flex');
            };

            const updateAddButtonState = (category) => {
                if (!addButton) {
                    return;
                }

                const needsDisable = false;
                addButton.dataset.addMode = category;
                addButton.disabled = needsDisable;
                addButton.classList.toggle('cursor-not-allowed opacity-70', needsDisable);
                addButton.title = category === 'albums'
                    ? 'Buat album baru'
                    : 'Unggah foto baru';
            };

            const setActiveCategory = (category) => {
                categoryButtons.forEach((button) => {
                    const isActive = button.dataset.categoryToggle === category;
                    button.classList.toggle('border-[#BFC9D1]', isActive);
                    button.classList.toggle('border-transparent', !isActive);
                    button.classList.toggle('text-[#1c1f2b]', isActive);
                    button.classList.toggle('text-[#777d96]', !isActive);
                });

                Object.entries(gallerySections).forEach(([key, section]) => {
                    if (!section) {
                        return;
                    }
                    section.classList.toggle('hidden', key !== category);
                });

                updateAddButtonState(category);
            };

            categoryButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const category = button.dataset.categoryToggle;
                    if (category === 'pictures' && !activeAlbumFilter) {
                        resetAlbumFilter();
                    }
                    setActiveCategory(category);
                });
            });

            albumCards.forEach((card) => {
                card.addEventListener('click', () => {
                    applyAlbumFilter(card.dataset.albumId, card.dataset.albumName || '-');
                });
            });

            albumFilterReset?.addEventListener('click', () => {
                resetAlbumFilter();
                setActiveCategory('pictures');
            });

            addButton?.addEventListener('click', handleAddAction);
            setActiveCategory('pictures');

            const detailModal = document.getElementById('detail-modal');
            const detailBackdrop = document.getElementById('detail-modal-backdrop');
            const detailCloseButton = document.getElementById('detail-modal-close');
            const detailImage = document.getElementById('detail-photo-image');
            const detailTitle = document.getElementById('detail-photo-title');
            const detailDate = document.getElementById('detail-photo-date');
            const detailDescription = document.getElementById('detail-photo-description');
            const detailLikeButton = document.getElementById('detail-like-button');
            const detailLikeIcon = document.getElementById('detail-like-icon');
            const detailLikeCount = document.getElementById('detail-like-count');
            const detailCommentCount = document.getElementById('detail-comment-count');
            const detailCommentsList = document.getElementById('detail-comments-list');
            const detailCommentForm = document.getElementById('detail-comment-form');
            const detailCommentInput = detailCommentForm?.querySelector('[name="IsiKomentar"]');
            let currentPhotoId = null;
            let detailComments = [];

            const showDetailModal = () => detailModal?.classList.remove('hidden');
            const hideDetailModal = () => detailModal?.classList.add('hidden');

            detailCloseButton?.addEventListener('click', hideDetailModal);
            detailModal?.addEventListener('click', (event) => {
                if (event.target === detailModal || event.target === detailBackdrop) {
                    hideDetailModal();
                }
            });

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const likeEndpoint = "{{ route('gallery.like') }}";
            const detailEndpoint = "{{ url('/gallery/photo') }}";

            const pluralize = (count, singular, plural) => `${count} ${count === 1 ? singular : plural}`;
            const animateBadge = (badge) => {
                if (!badge) {
                    return;
                }

                badge.classList.remove('like-badge--visible');
                void badge.offsetWidth;
                badge.classList.add('like-badge--visible');
                setTimeout(() => badge.classList.remove('like-badge--visible'), 650);
            };

            const formatCommentDate = (value) => {
                if (!value) {
                    return '';
                }

                const parsed = new Date(`${value}T00:00:00`);
                return parsed.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
            };

            const renderDetailComments = (items) => {
                if (!detailCommentsList) {
                    return;
                }

                detailCommentsList.innerHTML = '';

                if (!items.length) {
                    const empty = document.createElement('p');
                    empty.className = 'text-sm text-[#6b7280]';
                    empty.textContent = 'Belum ada komentar.';
                    detailCommentsList.appendChild(empty);
                    return;
                }

                items.forEach((comment) => {
                    const article = document.createElement('article');
                    article.className = 'rounded-2xl bg-white p-3 shadow-[0_1px_3px_rgba(15,23,42,0.1)]';

                    const header = document.createElement('div');
                    header.className = 'flex items-center justify-between gap-2 text-[0.65rem] uppercase tracking-[0.3em] text-[#6b7280]';
                    const author = document.createElement('span');
                    author.className = 'font-semibold text-[#111827]';
                    author.textContent = comment.author;
                    const date = document.createElement('span');
                    date.textContent = formatCommentDate(comment.created_at);
                    header.appendChild(author);
                    header.appendChild(date);

                    const body = document.createElement('p');
                    body.className = 'mt-2 text-[0.85rem] text-[#1c1f2b]';
                    body.textContent = comment.text;

                    article.appendChild(header);
                    article.appendChild(body);
                    detailCommentsList.appendChild(article);
                });
            };

            const updateCardLikeState = (photoId, likes, liked) => {
                const card = document.querySelector(`[data-photo-id="${photoId}"]`);
                if (!card) {
                    return;
                }

                card.dataset.liked = liked ? 'true' : 'false';
                card.dataset.likes = likes;

                const likeButton = card.querySelector('.like-trigger');
                const likeIcon = likeButton?.querySelector('.like-icon');
                const likeCounter = card.querySelector('[data-like-count-display]');

                if (likeButton) {
                    likeButton.dataset.liked = liked ? 'true' : 'false';
                    likeButton.dataset.count = likes;
                    likeButton.setAttribute('aria-pressed', liked ? 'true' : 'false');
                }

                if (likeIcon) {
                    likeIcon.src = liked ? likeIcon.dataset.filled : likeIcon.dataset.outlined;
                }

                if (likeCounter) {
                    likeCounter.textContent = pluralize(likes, 'like', 'likes');
                }
            };

            const updateCardComments = (photoId, totalComments) => {
                const card = document.querySelector(`[data-photo-id="${photoId}"]`);
                if (!card) {
                    return;
                }

                card.dataset.comments = totalComments;
                const commentCounter = card.querySelector('[data-comment-count-display]');
                if (commentCounter) {
                    commentCounter.textContent = pluralize(totalComments, 'comment', 'comments');
                }
            };

            const updateDetailLikeState = (likes, liked) => {
                if (!detailLikeButton) {
                    return;
                }

                detailLikeButton.dataset.liked = liked ? 'true' : 'false';
                detailLikeButton.dataset.count = likes;
                detailLikeButton.setAttribute('aria-pressed', liked ? 'true' : 'false');

                if (detailLikeIcon) {
                    detailLikeIcon.src = liked ? detailLikeIcon.dataset.filled : detailLikeIcon.dataset.outlined;
                }

                if (detailLikeCount) {
                    detailLikeCount.textContent = pluralize(likes, 'like', 'likes');
                }
            };

            const updateDetailCommentCount = (count) => {
                if (!detailCommentCount) {
                    return;
                }

                detailCommentCount.textContent = pluralize(count, 'comment', 'comments');
            };

            const sendLikeRequest = async (photoId, forceOn) => {
                if (!csrfToken) {
                    throw new Error('CSRF token tidak ditemukan');
                }

                const payload = { photo_id: photoId };
                if (typeof forceOn === 'boolean') {
                    payload.like = forceOn;
                }

                const response = await fetch(likeEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        Accept: 'application/json',
                    },
                    body: JSON.stringify(payload),
                });

                if (!response.ok) {
                    const message = await response.text();
                    throw new Error(`Gagal menyimpan like (${response.status}): ${message}`);
                }

                return response.json();
            };

            const openDetail = async (photoId) => {
                if (!photoId) {
                    return;
                }

                try {
                    const response = await fetch(`${detailEndpoint}/${photoId}`, {
                        headers: {
                            Accept: 'application/json',
                        },
                    });

                    if (!response.ok) {
                        const message = await response.text();
                        throw new Error(`Gagal memuat detail foto (${response.status}): ${message}`);
                    }

                    const payload = await response.json();
                    currentPhotoId = photoId;
                    detailComments = payload.comments ?? [];

                    if (detailImage) {
                        detailImage.src = payload.photo.cover ?? '';
                        detailImage.alt = payload.photo.title ?? 'Foto';
                    }

                    if (detailTitle) {
                        detailTitle.textContent = payload.photo.title ?? 'Foto';
                    }

                    if (detailDate) {
                        detailDate.textContent = payload.photo.uploaded_at
                            ? new Date(`${payload.photo.uploaded_at}T00:00:00`).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
                            : '-';
                    }

                    if (detailDescription) {
                        detailDescription.textContent = payload.photo.description ?? 'Tidak ada deskripsi.';
                    }

                    updateDetailLikeState(payload.photo.likes, payload.photo.liked);
                    updateDetailCommentCount(payload.photo.comments);
                    renderDetailComments(detailComments);

                    detailLikeButton?.setAttribute('data-photo-id', photoId);
                    showDetailModal();
                } catch (error) {
                    console.error(error);
                    alert('Detail foto gagal dimuat. Coba refresh halaman lalu ulangi.');
                }
            };

            photoCards.forEach((card) => {
                card.addEventListener('click', (event) => {
                    if (event.target.closest('.like-trigger')) {
                        return;
                    }

                    const detailUrl = card.dataset.detailUrl;
                    if (detailUrl) {
                        window.location.href = detailUrl;
                    }
                });
            });

            const photoCardLikeButtons = document.querySelectorAll('.photo-card .like-trigger');
            photoCardLikeButtons.forEach((button) => {
                button.addEventListener('click', async (event) => {
                    event.stopPropagation();
                    const photoId = button.dataset.photoId;
                    if (!photoId) {
                        return;
                    }

                    const currentlyLiked = button.dataset.liked === 'true';
                    const shouldLike = !currentlyLiked;

                    try {
                        const data = await sendLikeRequest(photoId, shouldLike);
                        const totalLikes = Number(data.likes ?? data.count ?? 0);
                        const isLiked = Boolean(data.liked);
                        const badge = button.closest('.photo-card')?.querySelector('.like-badge');
                        if (isLiked) {
                            animateBadge(badge);
                        }
                        updateCardLikeState(photoId, totalLikes, isLiked);
                        if (currentPhotoId === photoId) {
                            updateDetailLikeState(totalLikes, isLiked);
                        }
                    } catch (error) {
                        console.error(error);
                        alert('Like gagal diproses. Silakan coba lagi.');
                    }
                });
            });

            detailLikeButton?.addEventListener('click', async (event) => {
                event.stopPropagation();
                if (!currentPhotoId) {
                    return;
                }

                const currentlyLiked = detailLikeButton.dataset.liked === 'true';
                const shouldLike = !currentlyLiked;

                try {
                    const data = await sendLikeRequest(currentPhotoId, shouldLike);
                    const totalLikes = Number(data.likes ?? data.count ?? 0);
                    const isLiked = Boolean(data.liked);
                    const badge = document.querySelector(`[data-photo-id="${currentPhotoId}"]`)?.querySelector('.like-badge');
                    if (isLiked) {
                        animateBadge(badge);
                    }
                    updateCardLikeState(currentPhotoId, totalLikes, isLiked);
                    updateDetailLikeState(totalLikes, isLiked);
                } catch (error) {
                    console.error(error);
                    alert('Like gagal diproses. Silakan coba lagi.');
                }
            });

            detailCommentForm?.addEventListener('submit', async (event) => {
                event.preventDefault();
                if (!currentPhotoId) {
                    return;
                }

                const bodyText = detailCommentInput?.value.trim();
                if (!bodyText) {
                    detailCommentInput?.focus();
                    return;
                }

                if (!csrfToken) {
                    console.warn('CSRF token tidak ditemukan');
                    return;
                }

                try {
                    const response = await fetch(`${detailEndpoint}/${currentPhotoId}/comment`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            Accept: 'application/json',
                        },
                        body: JSON.stringify({ IsiKomentar: bodyText }),
                    });

                    if (!response.ok) {
                        const message = await response.text();
                        throw new Error(`Gagal menambahkan komentar (${response.status}): ${message}`);
                    }

                    const payload = await response.json();
                    const totalComments = Number(payload.total_comments ?? payload.comments ?? detailComments.length + 1);
                    detailComments = [payload.comment, ...detailComments];
                    renderDetailComments(detailComments);
                    updateDetailCommentCount(totalComments);
                    updateCardComments(currentPhotoId, totalComments);
                    detailCommentInput.value = '';
                } catch (error) {
                    console.error(error);
                    alert('Komentar gagal dikirim. Silakan coba lagi.');
                }
            });
        });
    </script>
</body>
</html>
