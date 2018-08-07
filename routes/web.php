<?php
/**
 * Autor: Paula Aguirre Copyright (c) 2018.
 */

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Storage;


Route::get('/home', function () {

    if (Auth::check ())
    {
        if (\Illuminate\Support\Facades\Auth::user ()->role_id == 8)
        {
            return redirect ('expedientes');
        }
        else
        {
            return redirect ('aprobacion_expedientes/expedientes_pendientes');
        }
    }
    else
    {
        return redirect ('login');
    }



});

Route::get('/', function () {
    //return view('welcome');
    if (Auth::check ())
    {
        if (\Illuminate\Support\Facades\Auth::user ()->role_id == 8)
        {
            return redirect ('expedientes');
        }
        elseif (\Illuminate\Support\Facades\Auth::user ()->role_id == 9)
        {
            return redirect ('index_exp');
        }
        else
        {
            return redirect ('aprobacion_expedientes/expedientes_pendientes');
        }
    }
    else
    {
        return redirect ('login');
    }
});


Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');



Auth::routes();


Route::group (['middleware'=>'auth'], function (){
    Route::view('chart.chart', 'chart');
    Route::get('/chart', function (){
        return view ('chart.chart');
    });
    Route::resource ('roles', 'RoleController');
    Route::resource('users', 'UserController');
    Route::resource ('gerencias', 'GerenciaController');
    Route::resource ('departamentos', 'DepartamentoController');
    Route::resource ('funcionarios', 'FuncionarioController');
    Route::resource ('tipoexpedientes', 'TipoexpedienteController');
    Route::resource ('expedientes', 'ExpedienteController');
    Route::resource ('aprobacion_expedientes/expedientes_pendientes', 'HistoryController');
    Route::resource ('expedientes_rechazados/expedientes_rechazados_creador', 'RechazadosController');
    Route::resource ('proveedores', 'ProveedorController');
    Route::resource ('ots', 'OtController');
    Route::resource ('expedientes_por_areas', 'VistaporareaController');
    Route::view('manual', 'manual');
    Route::resource('historial_de_expedientes', 'HistorialController');
    //Route::get ('pdf/pdf', 'ManualController@pdf')->name ('pdf');

    Route::get('media', function () {
        return view('media');
    });


    Route::post('media', function () {
        return request()->file->storeAs('uploads', request()->file->getClientOriginalName());
    });

    Route::get('/uploads/{file}', function ($file) {
        $pdf =  Storage::response("uploads/$file");
        $pdf->stream();
    });

});