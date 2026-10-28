<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PortfolioItem;
use Illuminate\Auth\Access\HandlesAuthorization;

class PortfolioItemPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PortfolioItem');
    }

    public function view(AuthUser $authUser, PortfolioItem $portfolioItem): bool
    {
        return $authUser->can('View:PortfolioItem');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PortfolioItem');
    }

    public function update(AuthUser $authUser, PortfolioItem $portfolioItem): bool
    {
        return $authUser->can('Update:PortfolioItem');
    }

    public function delete(AuthUser $authUser, PortfolioItem $portfolioItem): bool
    {
        return $authUser->can('Delete:PortfolioItem');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PortfolioItem');
    }

    public function restore(AuthUser $authUser, PortfolioItem $portfolioItem): bool
    {
        return $authUser->can('Restore:PortfolioItem');
    }

    public function forceDelete(AuthUser $authUser, PortfolioItem $portfolioItem): bool
    {
        return $authUser->can('ForceDelete:PortfolioItem');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PortfolioItem');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PortfolioItem');
    }

    public function replicate(AuthUser $authUser, PortfolioItem $portfolioItem): bool
    {
        return $authUser->can('Replicate:PortfolioItem');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PortfolioItem');
    }

}