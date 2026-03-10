<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GalleryController;

Route::redirect('/', '/login');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::middleware('auth')->group(function () {
	Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
	Route::get('/admin', [GalleryController::class, 'adminDashboard'])->name('admin.dashboard');
	Route::post('/admin/photos/{photo}/album', [GalleryController::class, 'adminUpdatePhotoAlbum'])->name('admin.photos.album.update');
	Route::post('/admin/photos/{photo}/delete', [GalleryController::class, 'adminDeletePhoto'])->name('admin.photos.delete');
	Route::post('/admin/photos/delete-selected', [GalleryController::class, 'adminDeleteSelectedPhotos'])->name('admin.photos.delete-selected');

	Route::get('/preview', [GalleryController::class, 'preview'])->name('gallery.preview');
	Route::post('/gallery/album', [GalleryController::class, 'storeAlbum'])->name('gallery.album.store');
	Route::post('/gallery/upload', [GalleryController::class, 'upload'])->name('gallery.upload');
	Route::get('/gallery/detail/{photo}', [GalleryController::class, 'showPhoto'])->name('gallery.photo.show');
	Route::post('/gallery/photo/{photo}/album', [GalleryController::class, 'updatePhotoAlbum'])->name('gallery.photo.album.update');
	Route::get('/gallery/photo/{photo}', [GalleryController::class, 'photoDetail'])->name('gallery.photo.detail');
	Route::post('/gallery/photo/{photo}/comment', [GalleryController::class, 'comment'])->name('gallery.photo.comment');
	Route::post('/gallery/like', [GalleryController::class, 'toggleLike'])->name('gallery.like');
});

Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
