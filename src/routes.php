<?php

use Faed\Doc\controller\DocController;
use Illuminate\Support\Facades\Route;

if (config('doc.laravle_versions') == 8){
    Route::post('/doc/save',[DocController::class,'save'])->name('doc.save');

    Route::get('/doc/{id}/{groupId?}',[DocController::class,'index'])->name('doc.index');
    Route::get('/doc',[DocController::class,'nav'])->name('doc.nav');
}else{
    Route::namespace('\Faed\Doc\controller')->post('/doc/save','DocController@save')->name('doc.save');
    Route::namespace('\Faed\Doc\controller')->get('/doc/{id}','DocController@index')->name('doc.index');
}

