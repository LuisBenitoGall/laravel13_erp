<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Http\Requests\AllUserTableRequest;
use App\Domains\Admin\Http\Resources\DirectoryUserResource;
use App\Domains\Admin\Support\UserQuickViewData;
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

        return $table->for(User::query()->where('is_public', true))
            ->searchable(['name', 'surname', 'email'])
            ->request($request)
            ->through(DirectoryUserResource::class)
            ->json();
    }

    public function quick(User $user, UserQuickViewData $quickView): JsonResponse
    {
        $this->authorize('viewAnyAllUser');
        abort_unless((bool) $user->is_public, 404);

        return response()->json($quickView->build($user, false, 'directory'));
    }
}
