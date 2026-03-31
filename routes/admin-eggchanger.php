<?php

use Illuminate\Support\Facades\Route;
use Pterodactyl\Http\Controllers\Admin;

Route::group(['prefix' => 'extensions/eggchanger'], function () {
	Route::get('/', [Admin\Extensions\eggchanger\eggchangerExtensionController::class, 'index'])->name('admin.extensions.eggchanger.index');
	Route::patch('/', [Admin\Extensions\eggchanger\eggchangerExtensionController::class, 'update'])->name('admin.extensions.eggchanger.patch');
	Route::post('/', [Admin\Extensions\eggchanger\eggchangerExtensionController::class, 'post'])->name('admin.extensions.eggchanger.post');
	Route::put('/', [Admin\Extensions\eggchanger\eggchangerExtensionController::class, 'put'])->name('admin.extensions.eggchanger.put');
	Route::delete('/{target}/{id}', [Admin\Extensions\eggchanger\eggchangerExtensionController::class, 'delete'])->name('admin.extensions.eggchanger.delete');
});