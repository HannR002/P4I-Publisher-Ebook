<?php

namespace App\Services;

use App\Models\LibraryItem;
use App\Models\User;

class LibraryAccessService
{
    public function canRead(LibraryItem $item, ?User $user): bool
    {
        return match ($item->access_policy) {
            'public_read_download', 'public_read_only', 'external' => true,
            'registered_read_download', 'registered_read_only' => $user !== null,
            'manual_purchase' => $this->hasGrant($item, $user),
            default => false,
        };
    }

    public function canDownload(LibraryItem $item, ?User $user): bool
    {
        $policyAllows = match ($item->access_policy) {
            'public_read_download' => true,
            'registered_read_download' => $user !== null,
            'manual_purchase' => $this->hasGrant($item, $user),
            default => false,
        };

        return $policyAllows && $item->files()->where('download_allowed', true)->exists();
    }

    public function hasGrant(LibraryItem $item, ?User $user): bool
    {
        if (! $user) return false;

        return $item->accessGrants()
            ->where('user_id', $user->id)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->exists();
    }

    public function actions(LibraryItem $item, ?User $user): array
    {
        return [
            'can_read' => $this->canRead($item, $user),
            'can_download' => $this->canDownload($item, $user),
            'login_required' => str_starts_with($item->access_policy, 'registered_') && ! $user,
            'can_purchase' => $item->access_policy === 'manual_purchase' && ! $this->hasGrant($item, $user),
            'is_external' => $item->access_policy === 'external',
            'is_physical' => $item->access_policy === 'physical_only',
        ];
    }
}
