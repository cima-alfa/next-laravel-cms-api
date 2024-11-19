<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\User\SessionCollection;
use App\Http\Resources\User\UserCollection;
use App\Http\Resources\User\UserResource;
use App\Models\Session;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Spatie\QueryBuilder\QueryBuilder;

class UserController extends Controller
{
    protected $fields = [
        'id',
        'username',
        'email',
        'name_display',
        'name_first',
        'name_second',
        'name_last',
        'phone',
        'phone_prefix',
        'locale',
        'email_verified_at',
        'created_at',
        'updated_at',
    ];

    /**
     * Display a users listing.
     */
    public function index(Request $request): UserCollection
    {
        $pages = QueryBuilder::for(User::class)
            ->allowedFields($this->fields)
            ->allowedFilters($this->fields)
            ->allowedSorts($this->fields)
            ->defaultSort(['-created_at']);

        $pages = $request->has('all') ? $pages->get() : $pages->jsonPaginate()->appends(request()->query());

        return new UserCollection($pages);
    }

    /**
     * Check if current user is authenticated.
     */
    public function isAuthenticated(): null
    {
        return null;
    }

    /**
     * Get current authenticated user.
     */
    public function getAuthenticated(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    /**
     * Get the specified user.
     */
    public function get(User $user): UserResource
    {
        return new UserResource($user);
    }

    /**
     * Get active sessions.
     */
    public function getSessions(): SessionCollection
    {
        $sessions = Session::has('user')
            ->where('last_activity', '>=', Carbon::now()->subMinutes(config('session.lifetime'))->getTimestamp())
            ->orderByDesc('last_activity')
            ->with('user')
            ->get();

        return new SessionCollection($sessions);
    }

    /**
     * Get active sessions of the specified user.
     */
    public function getUserSessions(string $userId): SessionCollection
    {
        $sessions = User::findOrNotFound($userId)?->sessions()
            ->where('last_activity', '>=', Carbon::now()->subMinutes(config('session.lifetime'))->getTimestamp())
            ->orderByDesc('last_activity')
            ->with('user')
            ->get();

        return new SessionCollection($sessions);
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user, Request $request): array
    {
        if ($request->user()?->id === $user->id) {
            return ['message' => 'User could not be deleted'];
        }

        return $user->delete() ? ['message' => 'User deleted successfully'] : ['message' => 'User could not be deleted'];
    }
}