<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GalleryController extends Controller
{
    private function ensureAdmin(): void
    {
        if (!Auth::check() || strtolower((string) (Auth::user()->role ?? 'user')) !== 'admin') {
            abort(403);
        }
    }

    public function adminDashboard()
    {
        $this->ensureAdmin();

        $photos = DB::table('foto as f')
            ->leftJoin('users', 'f.UserID', '=', 'users.UserID')
            ->leftJoin('album as a', 'f.AlbumID', '=', 'a.AlbumID')
            ->orderByDesc('f.TanggalUnggah')
            ->get([
                'f.FotoID',
                'f.AlbumID',
                'f.JudulFoto',
                'f.DeskripsiFoto',
                'f.TanggalUnggah',
                'f.LokasiFile',
                'users.NamaLengkap',
                'a.NamaAlbum',
            ])
            ->map(function ($photo) {
                return [
                    'id' => $photo->FotoID,
                    'album_id' => $photo->AlbumID,
                    'title' => $photo->JudulFoto,
                    'description' => $photo->DeskripsiFoto,
                    'uploaded_at' => $photo->TanggalUnggah,
                    'cover' => $this->normalizeCover($photo->LokasiFile),
                    'owner' => $photo->NamaLengkap ?: 'Gallery User',
                    'album' => $photo->NamaAlbum ?: '-',
                ];
            })
            ->values();

        $albums = DB::table('album')
            ->orderBy('NamaAlbum')
            ->get(['AlbumID', 'NamaAlbum'])
            ->map(function ($album) {
                return [
                    'id' => $album->AlbumID,
                    'name' => $album->NamaAlbum,
                ];
            })
            ->values();

        return view('gallery.admin', [
            'photos' => $photos,
            'albums' => $albums,
        ]);
    }

    public function adminUpdatePhotoAlbum(Request $request, $photo)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'album_id' => 'nullable|integer|exists:album,AlbumID',
        ]);

        $updated = DB::table('foto')
            ->where('FotoID', (int) $photo)
            ->update([
                'AlbumID' => $validated['album_id'] ?? null,
            ]);

        if (!$updated && !DB::table('foto')->where('FotoID', (int) $photo)->exists()) {
            abort(404);
        }

        $payload = ['message' => 'Album foto berhasil diperbarui.'];

        if ($request->expectsJson()) {
            return response()->json($payload);
        }

        return redirect()->route('admin.dashboard')->with('status', $payload['message']);
    }

    public function adminDeletePhoto($photo)
    {
        $this->ensureAdmin();

        DB::table('foto')->where('FotoID', $photo)->delete();

        return redirect()->route('admin.dashboard')->with('status', 'Foto berhasil dihapus oleh admin.');
    }

    public function adminDeleteSelectedPhotos(Request $request)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'photo_ids' => 'required|array|min:1',
            'photo_ids.*' => 'integer|exists:foto,FotoID',
        ]);

        $photoIds = array_values(array_unique($validated['photo_ids']));
        $deleted = DB::table('foto')->whereIn('FotoID', $photoIds)->delete();

        return redirect()->route('admin.dashboard')->with('status', $deleted . ' foto berhasil dihapus.');
    }

    public function preview()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $userId = Auth::id();

        $photos = DB::table('foto')
            ->orderByDesc('TanggalUnggah')
            ->get(['FotoID', 'AlbumID', 'JudulFoto', 'DeskripsiFoto', 'TanggalUnggah', 'LokasiFile']);

        $likeCounts = DB::table('likefoto')
            ->select('FotoID', DB::raw('COUNT(*) as total'))
            ->groupBy('FotoID')
            ->pluck('total', 'FotoID');

        $commentCounts = DB::table('komentarfoto')
            ->select('FotoID', DB::raw('COUNT(*) as total'))
            ->groupBy('FotoID')
            ->pluck('total', 'FotoID');

        $userLikes = $userId
            ? DB::table('likefoto')->where('UserID', $userId)->pluck('FotoID')->toArray()
            : [];

        $photos = $photos->map(function ($photo) use ($likeCounts, $commentCounts, $userLikes) {
            return [
                'id' => $photo->FotoID,
                'album_id' => $photo->AlbumID,
                'title' => $photo->JudulFoto,
                'description' => $photo->DeskripsiFoto,
                'uploaded_at' => $photo->TanggalUnggah,
                'cover' => $this->normalizeCover($photo->LokasiFile),
                'likes' => $likeCounts[$photo->FotoID] ?? 0,
                'comments' => $commentCounts[$photo->FotoID] ?? 0,
                'liked' => in_array($photo->FotoID, $userLikes),
            ];
        });

        $albums = DB::table('album as a')
            ->leftJoin('users', 'a.UserID', '=', 'users.UserID')
            ->orderByDesc('a.TanggalDibuat')
            ->get([
                'a.AlbumID',
                'a.NamaAlbum',
                'a.Deskripsi',
                'a.TanggalDibuat',
                'users.NamaLengkap',
            ])
            ->map(function ($album) {
                return [
                    'id' => $album->AlbumID,
                    'name' => $album->NamaAlbum,
                    'description' => $album->Deskripsi,
                    'owner' => $album->NamaLengkap ?: 'Gallery User',
                    'created_at' => $album->TanggalDibuat,
                ];
            });

        return view('gallery.preview', [
            'photos' => $photos->toArray(),
            'albums' => $albums->values()->toArray(),
            'hasAlbum' => $albums->isNotEmpty(),
        ]);
    }

    public function storeAlbum(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        $userId = Auth::id() ?? DB::table('users')->value('UserID') ?? 1;

        DB::table('album')->insertGetId([
            'NamaAlbum' => $request->input('nama'),
            'Deskripsi' => $request->input('deskripsi') ?? '',
            'TanggalDibuat' => now()->toDateString(),
            'UserID' => $userId,
        ], 'AlbumID');

        return redirect()->route('gallery.preview')->with('status', 'Album berhasil dibuat.');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|max:5120',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'album_id' => 'nullable|integer|exists:album,AlbumID',
        ]);

        $userId = Auth::id() ?? DB::table('users')->value('UserID') ?? 1;

        $photoPath = $request->file('photo')->store('gallery', 'public');

        DB::table('foto')->insert([
            'JudulFoto' => $request->input('judul'),
            'DeskripsiFoto' => $request->input('deskripsi'),
            'TanggalUnggah' => now()->toDateString(),
            'LokasiFile' => 'storage/' . $photoPath,
            'AlbumID' => $request->filled('album_id') ? (int) $request->input('album_id') : null,
            'UserID' => $userId,
        ]);

        return redirect()->route('gallery.preview')->with('status', 'Foto berhasil ditambahkan.');
    }

    public function showPhoto($photo)
    {
        $photoId = (int) $photo;

        $photoData = DB::table('foto as f')
            ->leftJoin('users', 'f.UserID', '=', 'users.UserID')
            ->where('f.FotoID', $photoId)
            ->first([
                'f.FotoID',
                'f.UserID',
                'f.AlbumID',
                'f.JudulFoto',
                'f.DeskripsiFoto',
                'f.TanggalUnggah',
                'f.LokasiFile',
                'users.NamaLengkap',
            ]);

        if (!$photoData) {
            abort(404);
        }

        $userId = Auth::id();
        $isAdmin = strtolower((string) (Auth::user()->role ?? 'user')) === 'admin';
        $isOwner = (int) $photoData->UserID === (int) $userId;
        $likes = DB::table('likefoto')->where('FotoID', $photoId)->count();
        $liked = DB::table('likefoto')
            ->where('FotoID', $photoId)
            ->where('UserID', $userId)
            ->exists();

        $albumsQuery = DB::table('album')
            ->orderBy('NamaAlbum');

        if (!$isAdmin) {
            $albumsQuery->where('UserID', $userId);
        }

        $albums = $albumsQuery
            ->get(['AlbumID', 'NamaAlbum'])
            ->map(function ($album) {
                return [
                    'id' => $album->AlbumID,
                    'name' => $album->NamaAlbum,
                ];
            })
            ->values();

        $comments = DB::table('komentarfoto as k')
            ->join('users', 'k.UserID', '=', 'users.UserID')
            ->where('k.FotoID', $photoId)
            ->orderByDesc('k.KomentarID')
            ->get([
                'k.KomentarID',
                'k.IsiKomentar',
                'k.TanggalKomentar',
                'users.NamaLengkap',
            ]);

        return view('gallery.detailfoto', [
            'photo' => [
                'id' => $photoData->FotoID,
                'title' => $photoData->JudulFoto,
                'description' => $photoData->DeskripsiFoto,
                'uploaded_at' => $photoData->TanggalUnggah,
                'cover' => $this->normalizeCover($photoData->LokasiFile),
                'owner' => $photoData->NamaLengkap ?: 'Gallery User',
                'likes' => $likes,
                'liked' => $liked,
                'comments' => $comments->count(),
                'current_album_id' => $photoData->AlbumID,
                'can_manage_album' => $isOwner || $isAdmin,
            ],
            'comments' => $comments,
            'albums' => $albums,
        ]);
    }

    public function updatePhotoAlbum(Request $request, $photo)
    {
        $photoId = (int) $photo;
        $userId = (int) Auth::id();
        $isAdmin = strtolower((string) (Auth::user()->role ?? 'user')) === 'admin';

        $existingPhoto = DB::table('foto')
            ->where('FotoID', $photoId)
            ->first(['FotoID', 'UserID']);

        if (!$existingPhoto) {
            abort(404);
        }

        if (!$isAdmin && (int) $existingPhoto->UserID !== $userId) {
            abort(403);
        }

        $validated = $request->validate([
            'album_id' => 'nullable|integer|exists:album,AlbumID',
        ]);

        if (!empty($validated['album_id']) && !$isAdmin) {
            $canUseAlbum = DB::table('album')
                ->where('AlbumID', (int) $validated['album_id'])
                ->where('UserID', $userId)
                ->exists();

            if (!$canUseAlbum) {
                return back()->with('success', 'Album tidak valid untuk akun ini.');
            }
        }

        DB::table('foto')
            ->where('FotoID', $photoId)
            ->update([
                'AlbumID' => $validated['album_id'] ?? null,
            ]);

        return back()->with('success', 'Album foto berhasil diperbarui.');
    }

    public function photoDetail($photo)
    {
        $photo = DB::table('foto')
            ->where('FotoID', $photo)
            ->first(['FotoID', 'JudulFoto', 'DeskripsiFoto', 'TanggalUnggah', 'LokasiFile']);

        if (!$photo) {
            abort(404);
        }

        $userId = Auth::id();
        $likes = DB::table('likefoto')->where('FotoID', $photo->FotoID)->count();
        $liked = $userId
            ? DB::table('likefoto')->where('FotoID', $photo->FotoID)->where('UserID', $userId)->exists()
            : false;

        $comments = DB::table('komentarfoto as k')
            ->join('users', 'k.UserID', '=', 'users.UserID')
            ->where('k.FotoID', $photo->FotoID)
            ->orderByDesc('k.KomentarID')
            ->get([
                'k.KomentarID',
                'k.IsiKomentar',
                'k.TanggalKomentar',
                'users.NamaLengkap',
            ])
            ->map(function ($comment) {
                return [
                    'id' => $comment->KomentarID,
                    'text' => $comment->IsiKomentar,
                    'created_at' => $comment->TanggalKomentar,
                    'author' => $comment->NamaLengkap,
                ];
            })
            ->values()
            ->toArray();

        return response()->json([
            'photo' => [
                'id' => $photo->FotoID,
                'title' => $photo->JudulFoto,
                'description' => $photo->DeskripsiFoto,
                'uploaded_at' => $photo->TanggalUnggah,
                'cover' => $this->normalizeCover($photo->LokasiFile),
                'likes' => $likes,
                'count' => $likes,
                'comments' => count($comments),
                'liked' => $liked,
            ],
            'comments' => $comments,
        ]);
    }

    public function toggleLike(Request $request)
    {
        $validated = $request->validate([
            'photo_id' => 'required|integer|exists:foto,FotoID',
            'like' => 'nullable|boolean',
        ]);

        $photoId = $validated['photo_id'];
        $userId = Auth::id();

        $liked = DB::table('likefoto')
            ->where('FotoID', $photoId)
            ->where('UserID', $userId)
            ->exists();

        $forceLike = null;
        if ($request->has('like')) {
            $forceLike = $request->boolean('like');
        }

        if ($forceLike === true && !$liked) {
            DB::table('likefoto')->insert([
                'FotoID' => $photoId,
                'UserID' => $userId,
                'TanggalLike' => now()->toDateString(),
            ]);
            $liked = true;
        } elseif ($forceLike === false && $liked) {
            DB::table('likefoto')
                ->where('FotoID', $photoId)
                ->where('UserID', $userId)
                ->delete();
            $liked = false;
        } elseif ($forceLike === null) {
            if ($liked) {
                DB::table('likefoto')
                    ->where('FotoID', $photoId)
                    ->where('UserID', $userId)
                    ->delete();
                $liked = false;
            } else {
                DB::table('likefoto')->insert([
                    'FotoID' => $photoId,
                    'UserID' => $userId,
                    'TanggalLike' => now()->toDateString(),
                ]);
                $liked = true;
            }
        }

        $totalLikes = DB::table('likefoto')
            ->where('FotoID', $photoId)
            ->count();

        $payload = [
            'liked' => $liked,
            'likes' => $totalLikes,
            'count' => $totalLikes,
        ];

        if ($request->expectsJson()) {
            return response()->json($payload);
        }

        return back()->with('success', $liked ? 'Foto disukai.' : 'Like dibatalkan.');
    }

    public function comment(Request $request, $photo)
    {
        $request->validate([
            'IsiKomentar' => 'required|string|max:1000',
        ]);

        $existingPhoto = DB::table('foto')->where('FotoID', $photo)->first();
        if (!$existingPhoto) {
            abort(404);
        }

        $userId = Auth::id();

        $commentId = DB::table('komentarfoto')->insertGetId([
            'IsiKomentar' => $request->input('IsiKomentar'),
            'TanggalKomentar' => now()->toDateString(),
            'FotoID' => $photo,
            'UserID' => $userId,
        ], 'KomentarID');

        $comment = DB::table('komentarfoto as k')
            ->join('users', 'k.UserID', '=', 'users.UserID')
            ->where('k.KomentarID', $commentId)
            ->select('k.KomentarID', 'k.IsiKomentar', 'k.TanggalKomentar', 'users.NamaLengkap')
            ->first();

        $totalComments = DB::table('komentarfoto')->where('FotoID', $photo)->count();

        $payload = [
            'comment' => [
                'id' => $comment->KomentarID,
                'text' => $comment->IsiKomentar,
                'created_at' => $comment->TanggalKomentar,
                'author' => $comment->NamaLengkap,
            ],
            'total_comments' => $totalComments,
            'comments' => $totalComments,
        ];

        if ($request->expectsJson()) {
            return response()->json($payload);
        }

        return redirect()->route('gallery.photo.show', ['photo' => $photo])->with('success', 'Komentar ditambahkan.');
    }

    private function normalizeCover(?string $path): ?string
    {
        if ($path) {
            if (Str::startsWith($path, ['http://', 'https://'])) {
                return $path;
            }

            return asset($path);
        }

        return null;
    }
}
