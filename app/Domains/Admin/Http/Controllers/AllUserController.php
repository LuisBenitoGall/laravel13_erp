<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Http\Requests\AllUserTableRequest;
use App\Domains\Admin\Http\Resources\DirectoryUserResource;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Table\TableBuilder;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class AllUserController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAnyAllUser');

        return Inertia::render('Admin/AllUsers/Index');
    }

    public function table(AllUserTableRequest $request, TableBuilder $table): JsonResponse
    {
        $this->authorize('viewAnyAllUser');

        return $table->for(User::query())
            ->searchable(['name', 'surname', 'email'])
            ->request($request)
            ->through(DirectoryUserResource::class)
            ->json();
    }
}
