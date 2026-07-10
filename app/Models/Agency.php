<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Agency extends Model
{
    use HasFactory;

    public const DEPDEV_ID = 276;
    public const DEPDEV_NAME = 'Department of Economy, Planning, and Development';
    public const DEPDEV_NAME_WITH_ABBREVIATION = 'Department of Economy, Planning, and Development (DEPDev)';
    public const DEPDEV_ABBREVIATION = 'DEPDev';

    protected $guarded = ['id'];

    protected $appends = ['name', 'abbreviation', 'display_name'];

    public function users()
    {
        return $this->hasMany(User::class, 'agency_id');
    }

    public function forms()
    {
        return $this->hasMany(Form::class, 'agency_id');
    }

    public function formSubmissions()
    {
        return $this->hasMany(FormSubmission::class, 'agency_id');
    }

    public function getNameAttribute(): ?string
    {
        return $this->attributes['UACS_AGY_DSC']
            ?? $this->attributes['name']
            ?? null;
    }

    public function getAbbreviationAttribute(): ?string
    {
        return $this->attributes['Abbreviation']
            ?? $this->attributes['abbreviation']
            ?? null;
    }

    public function getDisplayNameAttribute(): string
    {
        $name = $this->name ?? ('Agency #' . $this->id);
        $abbreviation = $this->abbreviation;

        return $abbreviation ? $name . ' (' . $abbreviation . ')' : $name;
    }

    public function getSelectionNameAttribute(): string
    {
        $name = trim((string) ($this->name ?? ''));
        $abbreviation = trim((string) ($this->abbreviation ?? ''));

        if ($abbreviation !== '' && $name !== '') {
            return $abbreviation . ' - ' . $name;
        }

        if ($abbreviation !== '') {
            return $abbreviation;
        }

        return $name !== '' ? $name : 'Agency #' . $this->id;
    }

    public function scopeDepDev(Builder $query): Builder
    {
        return $query->where(function (Builder $query) {
            $query->where('Abbreviation', self::DEPDEV_ABBREVIATION)
                ->orWhere('UACS_AGY_DSC', self::DEPDEV_NAME)
                ->orWhere('UACS_AGY_DSC', self::DEPDEV_NAME_WITH_ABBREVIATION);
        });
    }

    public static function depDevIds(): array
    {
        return collect([self::DEPDEV_ID])
            ->merge(self::depDev()->pluck('id'))
            ->unique()
            ->map(fn ($id) => (string) $id)
            ->all();
    }

    public static function isDepDevId($agencyId): bool
    {
        if (empty($agencyId)) {
            return false;
        }

        if ((int) $agencyId === self::DEPDEV_ID) {
            return true;
        }

        return self::whereKey($agencyId)->depDev()->exists();
    }

    public function isDepDev(): bool
    {
        return (int) $this->id === self::DEPDEV_ID
            || strtoupper((string) $this->abbreviation) === strtoupper(self::DEPDEV_ABBREVIATION)
            || $this->name === self::DEPDEV_NAME
            || $this->name === self::DEPDEV_NAME_WITH_ABBREVIATION;
    }
}
