<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Actions\DeleteUserDoc;
use App\Domains\Admin\Actions\UploadUserDocs;
use App\Domains\Admin\Http\Requests\StoreUserDocsRequest;
use App\Domains\Partners\Models\Company;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDoc;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserDocController extends Controller
{
    public function storeForProfile(
        StoreUserDocsRequest $request,
        UploadUserDocs $action,
    ): RedirectResponse {
        /** @var User $owner */
        $owner = $request->user();
        $this->authorize('upload', [UserDoc::class, $owner]);

        $action->handle($owner, $this->uploadedFiles($request));

        return back()->with('success', __('docs_subidos'));
    }

    public function downloadForProfile(
        Request $request,
        UserDoc $userDoc,
    ): StreamedResponse {
        /** @var User $actor */
        $actor = $request->user();
        abort_unless($userDoc->user_id === $actor->id, 403);
        $this->authorize('download', $userDoc);

        return $this->streamDownload($userDoc);
    }

    public function destroyForProfile(
        Request $request,
        UserDoc $userDoc,
        DeleteUserDoc $action,
    ): RedirectResponse {
        /** @var User $actor */
        $actor = $request->user();
        abort_unless($userDoc->user_id === $actor->id, 403);
        $this->authorize('delete', $userDoc);

        $action->handle($userDoc);

        return back()->with('success', __('doc_eliminado'));
    }

    public function storeForCompanyUser(
        StoreUserDocsRequest $request,
        User $user,
        UploadUserDocs $action,
    ): RedirectResponse {
        $this->authorize('upload', [UserDoc::class, $user]);
        $company = $this->currentCompany();
        abort_unless(
            $user->companies()->where('companies.id', $company->id)->exists(),
            403,
        );

        $action->handle($user, $this->uploadedFiles($request), $company);

        return back()->with('success', __('docs_subidos'));
    }

    public function downloadForCompanyUser(
        User $user,
        UserDoc $userDoc,
    ): StreamedResponse {
        abort_unless($userDoc->user_id === $user->id, 404);
        $this->authorize('download', $userDoc);
        $company = $this->currentCompany();
        abort_unless(
            $user->companies()->where('companies.id', $company->id)->exists(),
            403,
        );

        return $this->streamDownload($userDoc);
    }

    public function destroyForCompanyUser(
        User $user,
        UserDoc $userDoc,
        DeleteUserDoc $action,
    ): RedirectResponse {
        abort_unless($userDoc->user_id === $user->id, 404);
        $this->authorize('delete', $userDoc);
        $company = $this->currentCompany();
        abort_unless(
            $user->companies()->where('companies.id', $company->id)->exists(),
            403,
        );

        $action->handle($userDoc, $company);

        return back()->with('success', __('doc_eliminado'));
    }

    private function streamDownload(UserDoc $doc): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($doc->path), 404);

        return Storage::disk('local')->download(
            $doc->path,
            $doc->original_name,
            ['Content-Type' => $doc->mime],
        );
    }

    /** @return list<UploadedFile> */
    private function uploadedFiles(StoreUserDocsRequest $request): array
    {
        /** @var list<UploadedFile> $files */
        $files = array_values($request->file('docs', []));

        return $files;
    }

    private function currentCompany(): Company
    {
        $companyId = app(CurrentCompany::class)->id();
        abort_if($companyId === null, 404);

        return Company::query()->findOrFail($companyId);
    }
}
