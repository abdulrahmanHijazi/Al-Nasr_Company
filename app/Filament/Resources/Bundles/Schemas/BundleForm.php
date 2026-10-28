<?php

namespace App\Filament\Resources\Bundles\Schemas;

use App\Models\Product;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class BundleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Group::make()->columnSpan(['lg' => 2])->schema([
                Section::make(__('Offer details'))
                    ->description(__('A package of products sold together below their combined price.'))
                    ->icon(Heroicon::OutlinedGift)
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->required()->maxLength(255)->autofocus(),
                        TextInput::make('slug')->maxLength(255)->helperText(__('Leave empty to generate it from the name.')),
                        Textarea::make('description')->rows(3)->columnSpanFull(),
                        TextInput::make('bundle_price')->label('Bundle price')->numeric()->required()->minValue(0)
                            ->prefix(fn () => app(\App\Settings\SiteSettings::class)->currency),
                    ]),

                Section::make(__('Included products'))
                    ->description(__('Pick the products and how many of each the customer gets.'))
                    ->icon(Heroicon::OutlinedCube)
                    ->schema([
                        // Saved to the bundle_product pivot by the Create/Edit pages (SyncsBundleItems).
                        Repeater::make('items')->hiddenLabel()
                            ->minItems(1)
                            ->defaultItems(1)
                            ->addActionLabel(__('Add product'))
                            ->reorderable()
                            ->columns(3)
                            ->schema([
                                Select::make('product_id')->label('Product')
                                    ->options(fn () => Product::query()->orderBy('sku')->get()
                                        ->mapWithKeys(fn (Product $p) => [$p->id => "{$p->sku} — {$p->name}"])->all())
                                    ->searchable()->required()->distinct()->columnSpan(2),
                                TextInput::make('qty')->label('Quantity')->numeric()->integer()->minValue(1)->default(1)->required(),
                            ]),
                    ]),
            ]),

            Group::make()->columnSpan(['lg' => 1])->schema([
                Section::make(__('Publishing'))
                    ->icon(Heroicon::OutlinedEye)
                    ->schema([
                        Toggle::make('is_active')->label('Visible on the storefront')->default(true)
                            ->helperText(__('Shown on the storefront once this is on.')),
                        TextInput::make('sort_order')->numeric()->default(0)->helperText(__('Lower numbers appear first.')),
                    ]),
                Section::make(__('Schedule'))
                    ->icon(Heroicon::OutlinedCalendarDays)
                    ->schema([
                        DateTimePicker::make('starts_at')->helperText(__('Optional — visible from this moment.')),
                        DateTimePicker::make('ends_at')->helperText(__('Optional — hidden after this moment.'))->after('starts_at'),
                    ]),
                Section::make(__('Image'))
                    ->icon(Heroicon::OutlinedPhoto)
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('cover')->collection('cover')->image()->imageEditor(),
                    ]),
            ]),
        ]);
    }
}
