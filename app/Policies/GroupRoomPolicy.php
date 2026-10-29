<?php

namespace App\Policies;

use App\Models\GroupRoom;
use App\Models\User;

class GroupRoomPolicy
{
    /**
     * Determine whether the user can view a group room.
     */
    public function view(User $user, GroupRoom $groupRoom): bool
    {
        return $this->isApprovedClassroomMember($user, $groupRoom);
    }

    /**
     * Determine whether the user can create a group in a classroom.
     * The classroom membership approval check is handled by
     * GroupRoomController before creating the group.
     */
    public function create(User $user): bool
    {
        return $user->role === 'student';
    }

    /**
     * Determine whether the user can add a member to a group.
     */
    public function addMember(User $user, GroupRoom $groupRoom): bool
    {
        if ($user->role !== 'student') {
            return false;
        }

        return $groupRoom->members()
            ->where('user_id', $user->id)
            ->where('is_leader', true)
            ->exists();
    }

    /**
     * Check whether the user is an approved member of the
     * group's classroom.
     */
    private function isApprovedClassroomMember(
        User $user,
        GroupRoom $groupRoom
    ): bool {
        return $groupRoom->classroom()
            ->whereHas('members', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where('status', 'approved');
            })
            ->exists();
    }
}
