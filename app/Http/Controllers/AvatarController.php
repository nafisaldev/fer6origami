<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserAvatar;
use Illuminate\Http\Response;

class AvatarController extends Controller
{
    public function show(User $user): Response
    {
        $avatar = UserAvatar::where('user_id', $user->id)->firstOrFail();

        $binary = base64_decode((string) $avatar->data, true);

        if ($binary === false || $binary === '') {
            abort(404);
        }

        return response($binary, 200, [
            'Content-Type' => $avatar->mime,
            'Content-Length' => (string) strlen($binary),
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
