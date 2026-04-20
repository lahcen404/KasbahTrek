<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class CustomApiAuth
{
    public function handle(Request $request, Closure $next)
    {
        $header = $request->header('Authorization');

        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return response()->json(['message' => 'Unauthorized: No token provided'], 401);
        }

        // reeeplace 'Bearer ' with nothing
        $plainToken = str_replace('Bearer ', '', $header);

        // hashiiing the token
        $hashedToken = hash('sha256', $plainToken);

        // check if the hashed token exists in DB
        $tokenData = DB::table('personal_access_tokens')
            ->where('token', $hashedToken)
            ->first();

        if (!$tokenData) {
            return response()->json(['message' => 'Unauthorized: Invalid token'], 401);
        }

        // known token, we can find the user and set it in the auth context
        $user = User::find($tokenData->user_id);
        auth()->setUser($user);


        return $next($request);
    }
}
