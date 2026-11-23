<?php

use App\Domains\HR\Http\Controllers\ContractTypeController;
use App\Domains\HR\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;

// HR (RRHH) — Module Optional `rrhh` (V-21). El endpoint table va antes del resource.
Route::middleware(['auth', 'company', 'module:rrhh'])->prefix('hr')->name('hr.')->group(function () {
    Route::get('contract-types/table', [ContractTypeController::class, 'table'])->name('contract-types.table');
    Route::resource('contract-types', ContractTypeController::class)->except(['show']);

    Route::get('employees/table', [EmployeeController::class, 'table'])->name('employees.table');
    Route::get('employees/{employee}/renew', [EmployeeController::class, 'renewForm'])->name('employees.renew.form');
    Route::post('employees/{employee}/renew', [EmployeeController::class, 'renew'])->name('employees.renew');
    Route::post('employees/{employee}/end', [EmployeeController::class, 'end'])->name('employees.end');
    Route::resource('employees', EmployeeController::class)->except(['show']);
});
