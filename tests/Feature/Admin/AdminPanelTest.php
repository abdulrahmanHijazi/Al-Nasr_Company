<?php

use App\Filament\Widgets\LatestMessages;
use App\Filament\Widgets\ProductsByCategoryChart;
use App\Filament\Widgets\StoreStatsOverview;
use App\Models\ContactMessage;
use App\Models\Product;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

it('redirects guests to the admin login', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
    $this->get('/admin/login')->assertOk();
});

it('keeps accounts without a role out of the panel', function () {
    $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
});

it('lets an administrator open the dashboard', function () {
    $this->actingAs(adminUser())
        ->get('/admin')
        ->assertOk()
        ->assertSee('Dashboard');
});

it('opens every resource list for an administrator', function (string $path) {
    $this->actingAs(adminUser())->get("/admin/{$path}")->assertOk();
})->with([
    'brands',
    'categories',
    'products',
    'portfolio-items',
    'bundles',
    'contact-messages',
    'home-page-sections',
    'shield/roles',
]);

it('opens every create form for an administrator', function (string $path) {
    $this->actingAs(adminUser())->get("/admin/{$path}/create")->assertOk();
})->with([
    'brands',
    'categories',
    'products',
    'portfolio-items',
    'bundles',
    'home-page-sections',
]);

it('shows live numbers in the stats widget', function () {
    Product::factory()->count(3)->create();
    Product::factory()->create(['stock_qty' => 0]);
    ContactMessage::factory()->count(2)->create();

    Livewire::actingAs(adminUser())
        ->test(StoreStatsOverview::class)
        ->assertSee('Products')
        ->assertSee('Out of stock')
        ->assertSee('Unread messages');
});

it('lists the latest enquiries in the dashboard widget', function () {
    $messages = ContactMessage::factory()->count(3)->create();

    Livewire::actingAs(adminUser())
        ->test(LatestMessages::class)
        ->assertCanSeeTableRecords($messages);
});

it('draws the products-by-category chart from real data', function () {
    $solar = App\Models\Category::factory()->create(['name' => ['en' => 'Solar Panels', 'ar' => 'ألواح']]);
    Product::factory()->count(2)->create()->each(fn ($p) => $p->categories()->attach($solar));

    Livewire::actingAs(adminUser())
        ->test(ProductsByCategoryChart::class)
        ->assertOk();
});
