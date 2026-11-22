<?php

use App\Domains\Admin\Models\MediaAsset;
use App\Models\User;
use App\Models\UserImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('erp:migrate-public-media-to-local copia idempotente public→local', function () {
    Storage::fake('public');
    Storage::fake('local');

    $user = User::factory()->create();
    $imagePath = "users/{$user->id}/images/seed.jpg";
    Storage::disk('public')->put($imagePath, 'user-bytes');
    UserImage::factory()->create([
        'user_id' => $user->id,
        'image' => $imagePath,
    ]);

    $galleryPath = 'gallery/seed-asset.png';
    Storage::disk('public')->put($galleryPath, 'gallery-bytes');
    MediaAsset::factory()->create([
        'path' => $galleryPath,
    ]);
    // Factory also writes to local; overwrite: ensure only public has the seed for gallery.
    Storage::disk('local')->delete($galleryPath);
    Storage::disk('public')->put($galleryPath, 'gallery-bytes');

    $this->artisan('erp:migrate-public-media-to-local --dry-run')
        ->assertSuccessful();

    expect(Storage::disk('local')->exists($imagePath))->toBeFalse();

    $this->artisan('erp:migrate-public-media-to-local --delete-public')
        ->assertSuccessful();

    expect(Storage::disk('local')->exists($imagePath))->toBeTrue()
        ->and(Storage::disk('local')->get($imagePath))->toBe('user-bytes')
        ->and(Storage::disk('public')->exists($imagePath))->toBeFalse()
        ->and(Storage::disk('local')->exists($galleryPath))->toBeTrue()
        ->and(Storage::disk('public')->exists($galleryPath))->toBeFalse();

    $this->artisan('erp:migrate-public-media-to-local')
        ->assertSuccessful();

    expect(Storage::disk('local')->exists($imagePath))->toBeTrue();
});
