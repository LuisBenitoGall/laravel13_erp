<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\DeleteUserImage;
use App\Domains\Admin\Actions\MarkUserImageFeatured;
use App\Domains\Admin\Actions\UpdateUserImagePublic;
use App\Domains\Admin\Actions\UploadUserImages;
use App\Domains\Admin\Http\Requests\StoreUserImagesRequest;
use App\Domains\Admin\Http\Requests\UpdateUserImagePublicRequest;
use App\Domains\Admin\Models\Company;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserImage;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class UserImageController extends Controller
{
    public function storeForProfile(
        StoreUserImagesRequest $request,
        UploadUserImages $action,
    ): RedirectResponse {
        /** @var User $owner */
        $owner = $request->user();
        $this->authorize('upload', [UserImage::class, $owner]);

        $action->handle($owner, $this->uploadedFiles($request));

        return back()->with('success', __('imagenes_subidas'));
    }

    public function destroyForProfile(
        Request $request,
        UserImage $userImage,
        DeleteUserImage $action,
    ): RedirectResponse {
        /** @var User $actor */
        $actor = $request->user();
        abort_unless($userImage->user_id === $actor->id, 403);
        $this->authorize('delete', $userImage);

        $action->handle($userImage);

        return back()->with('success', __('imagen_eliminada'));
    }

    public function markFeaturedForProfile(
        Request $request,
        UserImage $userImage,
        MarkUserImageFeatured $action,
    ): RedirectResponse {
        /** @var User $actor */
        $actor = $request->user();
        abort_unless($userImage->user_id === $actor->id, 403);
        $this->authorize('markFeatured', $userImage);

        $action->handle($userImage);

        return back()->with('success', __('imagen_marcada_featured'));
    }

    public function updatePublicForProfile(
        UpdateUserImagePublicRequest $request,
        UserImage $userImage,
        UpdateUserImagePublic $action,
    ): RedirectResponse {
        /** @var User $actor */
        $actor = $request->user();
        abort_unless($userImage->user_id === $actor->id, 403);
        $this->authorize('updatePublic', $userImage);

        $action->handle($userImage, (bool) $request->validated('public'));

        return back()->with('success', __('imagen_public_actualizada'));
    }

    public function storeForCompanyUser(
        StoreUserImagesRequest $request,
        User $user,
        UploadUserImages $action,
    ): RedirectResponse {
        $this->authorize('upload', [UserImage::class, $user]);
        $company = $this->currentCompany();
        abort_unless(
            $user->companies()->where('companies.id', $company->id)->exists(),
            403,
        );

        $action->handle($user, $this->uploadedFiles($request), $company);

        return back()->with('success', __('imagenes_subidas'));
    }

    public function destroyForCompanyUser(
        User $user,
        UserImage $userImage,
        DeleteUserImage $action,
    ): RedirectResponse {
        abort_unless($userImage->user_id === $user->id, 404);
        $this->authorize('delete', $userImage);
        $company = $this->currentCompany();
        abort_unless(
            $user->companies()->where('companies.id', $company->id)->exists(),
            403,
        );

        $action->handle($userImage, $company);

        return back()->with('success', __('imagen_eliminada'));
    }

    public function markFeaturedForCompanyUser(
        User $user,
        UserImage $userImage,
        MarkUserImageFeatured $action,
    ): RedirectResponse {
        abort_unless($userImage->user_id === $user->id, 404);
        $this->authorize('markFeatured', $userImage);
        $company = $this->currentCompany();
        abort_unless(
            $user->companies()->where('companies.id', $company->id)->exists(),
            403,
        );

        $action->handle($userImage, $company);

        return back()->with('success', __('imagen_marcada_featured'));
    }

    public function updatePublicForCompanyUser(
        UpdateUserImagePublicRequest $request,
        User $user,
        UserImage $userImage,
        UpdateUserImagePublic $action,
    ): RedirectResponse {
        abort_unless($userImage->user_id === $user->id, 404);
        $this->authorize('updatePublic', $userImage);
        $company = $this->currentCompany();
        abort_unless(
            $user->companies()->where('companies.id', $company->id)->exists(),
            403,
        );

        $action->handle($userImage, (bool) $request->validated('public'), $company);

        return back()->with('success', __('imagen_public_actualizada'));
    }

    /** @return list<UploadedFile> */
    private function uploadedFiles(StoreUserImagesRequest $request): array
    {
        /** @var list<UploadedFile> $files */
        $files = array_values($request->file('images', []));

        return $files;
    }

    private function currentCompany(): Company
    {
        $companyId = app(CurrentCompany::class)->id();
        abort_if($companyId === null, 404);

        return Company::query()->findOrFail($companyId);
    }
}
