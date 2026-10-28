<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\HomePageSection;
use Illuminate\Auth\Access\HandlesAuthorization;

class HomePageSectionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:HomePageSection');
    }

    public function view(AuthUser $authUser, HomePageSection $homePageSection): bool
    {
        return $authUser->can('View:HomePageSection');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:HomePageSection');
    }

    public function update(AuthUser $authUser, HomePageSection $homePageSection): bool
    {
        return $authUser->can('Update:HomePageSection');
    }

    public function delete(AuthUser $authUser, HomePageSection $homePageSection): bool
    {
        return $authUser->can('Delete:HomePageSection');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:HomePageSection');
    }

    public function restore(AuthUser $authUser, HomePageSection $homePageSection): bool
    {
        return $authUser->can('Restore:HomePageSection');
    }

    public function forceDelete(AuthUser $authUser, HomePageSection $homePageSection): bool
    {
        return $authUser->can('ForceDelete:HomePageSection');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:HomePageSection');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:HomePageSection');
    }

    public function replicate(AuthUser $authUser, HomePageSection $homePageSection): bool
    {
        return $authUser->can('Replicate:HomePageSection');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:HomePageSection');
    }

}