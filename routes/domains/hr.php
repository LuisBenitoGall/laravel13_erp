<?php

use App\Domains\Calendar\Http\Controllers\DayTypeController;
use App\Domains\Calendar\Http\Controllers\WorkCalendarController;
use App\Domains\HR\Http\Controllers\ContractTypeController;
use App\Domains\HR\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;

// HR (RRHH) — Module Optional `rrhh` (V-21). El endpoint table va antes del resource.
Route::middleware(['auth', 'company', 'module:rrhh'])->prefix('rrhh')->name('rrhh.')->group(function () {
    Route::get('contract-types/table', [ContractTypeController::class, 'table'])->name('contract-types.table');
    Route::resource('contract-types', ContractTypeController::class)->except(['show']);

    Route::get('employees/table', [EmployeeController::class, 'table'])->name('employees.table');
    Route::get('employees/{employee}/renew', [EmployeeController::class, 'renewForm'])->name('employees.renew.form');
    Route::post('employees/{employee}/renew', [EmployeeController::class, 'renew'])->name('employees.renew');
    Route::post('employees/{employee}/end', [EmployeeController::class, 'end'])->name('employees.end');
    Route::resource('employees', EmployeeController::class)->except(['show']);

    Route::get('day-types/table', [DayTypeController::class, 'table'])->name('day-types.table');
    Route::resource('day-types', DayTypeController::class)->except(['show']);

    Route::get('calendars', [WorkCalendarController::class, 'index'])->name('calendars.index');
    Route::post('calendars/{work_calendar}/days', [WorkCalendarController::class, 'upsertDay'])->name('calendars.days.upsert');
    Route::delete('calendars/{work_calendar}/days/{day}', [WorkCalendarController::class, 'destroyDay'])->name('calendars.days.destroy');
    Route::post('calendars/{work_calendar}/inherit', [WorkCalendarController::class, 'inherit'])->name('calendars.inherit');
    Route::post('calendars/{work_calendar}/copy', [WorkCalendarController::class, 'copy'])->name('calendars.copy');
});
