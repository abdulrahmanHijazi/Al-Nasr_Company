<?php

namespace App\Support;

use App\Enums\Background3dModel;
use App\Models\ThreeDModel;

/**
 * Everything a 3D picker (home sections, site background) can offer: "no model", the built-in models
 * and the equipment library — in one place so the form, list, detail page and validation agree.
 */
class Model3dChoices
{
    public const NONE = 'none';

    /**
     * @return array<string, array{label: string, image: string, group: string}> key => card for the visual picker
     */
    public static function options(bool $withNone = true): array
    {
        $general = __('General models');
        $options = [];

        if ($withNone) {
            $options[self::NONE] = ['label' => __('No 3D model'), 'image' => ModelPreview::noneUrl(), 'group' => $general];
        }

        foreach (Background3dModel::cases() as $model) {
            $options[$model->value] = ['label' => $model->label(), 'image' => ModelPreview::url($model), 'group' => $general];
        }

        foreach (ThreeDModel::query()->active()->ordered()->get() as $model) {
            $options[$model->key()] = [
                'label' => $model->label(),
                'image' => $model->previewUrl() ?? ModelPreview::noneUrl(),
                'group' => $model->type->label(),
            ];
        }

        return $options;
    }

    /** @return array<int, string> every value a picker may store (library models even when switched off) */
    public static function keys(bool $withNone = true): array
    {
        return [
            ...($withNone ? [self::NONE] : []),
            ...Background3dModel::values(),
            ...ThreeDModel::query()->pluck('slug')->map(fn (string $slug) => ThreeDModel::KEY_PREFIX.$slug)->all(),
        ];
    }

    /** Human label of a stored value, or null for "no model". */
    public static function label(?string $key): ?string
    {
        if (blank($key) || $key === self::NONE) {
            return null;
        }

        return Background3dModel::tryFrom($key)?->label()
            ?? ThreeDModel::query()->where('slug', ThreeDModel::slugFromKey($key))->first()?->label()
            ?? $key;
    }

    /** @return array<string, string> key => label, for filters */
    public static function labels(): array
    {
        return collect(self::options(false))->map(fn (array $o) => $o['label'])->all();
    }
}
