<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('contents')) {
            return;
        }

        foreach (DB::table('contents')->select(['id', 'referrer', 'video'])->cursor() as $row) {
            DB::table('contents')->where('id', $row->id)->update([
                'referrer' => json_encode($this->toStringList($row->referrer), JSON_THROW_ON_ERROR),
                'video' => json_encode($this->toStringList($row->video), JSON_THROW_ON_ERROR),
            ]);
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE contents MODIFY referrer JSON NULL');
            DB::statement('ALTER TABLE contents MODIFY video JSON NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('contents')) {
            return;
        }

        foreach (DB::table('contents')->select(['id', 'referrer', 'video'])->cursor() as $row) {
            DB::table('contents')->where('id', $row->id)->update([
                'referrer' => $this->firstString($row->referrer),
                'video' => $this->firstString($row->video),
            ]);
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE contents MODIFY referrer VARCHAR(255) NULL');
            DB::statement('ALTER TABLE contents MODIFY video VARCHAR(255) NULL');
        }
    }

    /** @return list<string> */
    private function toStringList(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                return [];
            }

            if (str_starts_with($trimmed, '[')) {
                try {
                    /** @var mixed $decoded */
                    $decoded = json_decode($trimmed, true, 512, JSON_THROW_ON_ERROR);
                    if (is_array($decoded)) {
                        return $this->normalizeList($decoded);
                    }
                } catch (JsonException) {
                    // fall through — treat as plain string
                }
            }

            return [$trimmed];
        }

        if (is_array($value)) {
            return $this->normalizeList($value);
        }

        return [];
    }

    private function firstString(mixed $value): ?string
    {
        $list = $this->toStringList($value);

        return $list[0] ?? null;
    }

    /**
     * @param  array<mixed>  $values
     * @return list<string>
     */
    private function normalizeList(array $values): array
    {
        $out = [];
        foreach ($values as $item) {
            if (! is_string($item)) {
                continue;
            }
            $trimmed = trim($item);
            if ($trimmed !== '') {
                $out[] = $trimmed;
            }
        }

        return $out;
    }
};
