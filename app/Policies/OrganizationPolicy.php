<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    /**
     * Determine if the user can view any organizations.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the organization.
     */
    public function view(User $user, Organization $organization): bool
    {
        return $user->id === $organization->user_id;
    }

    /**
     * Determine if the user can create organizations.
     */
    public function create(User $user): bool
    {
        return $user->canCreateOrganization();
    }

    /**
     * Determine if the user can update the organization.
     */
    public function update(User $user, Organization $organization): bool
    {
        return $user->id === $organization->user_id;
    }

    /**
     * Determine if the user can delete the organization.
     */
    public function delete(User $user, Organization $organization): bool
    {
        return $user->id === $organization->user_id;
    }
}


