<?php

use Illuminate\Support\Facades\Route;
// routes/web.php

use App\Http\Controllers\ImageDownloadController;


Route::get('/download-image/{apiId}', [ImageDownloadController::class, 'download'])->name('image.download');


Route::get('/', function () {
    return view('welcome');
});
use App\Http\Controllers\MyImagesController;

Route::middleware(['auth'])->group(function () {
    Route::get('/my-images', [MyImagesController::class, 'index'])->name('my-images.index');
    Route::post('/my-images', [MyImagesController::class, 'store'])->name('my-images.store');
    Route::delete('/my-images/{image}', [MyImagesController::class, 'destroy'])->name('my-images.destroy');
    Route::get('/my-images/{image}/download', [MyImagesController::class, 'download'])->name('my-images.download');
    Route::post('/my-images/{image}/favorite', [MyImagesController::class, 'toggleFavorite'])->name('my-images.favorite');
}); 
use App\Http\Controllers\FavoriteController;

Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
Route::delete('/favorites/{id}', [FavoriteController::class, 'unfavorite'])->name('favorites.unfavorite');
 

