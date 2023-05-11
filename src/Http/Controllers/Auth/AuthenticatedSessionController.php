<?php

namespace Canvas\Http\Controllers\Auth;

use Canvas\Http\Requests\LoginRequest;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return Application|Factory|View|RedirectResponse
     */
    public function create()
    {
        if (Auth::guard('canvas')->check()) {
            return redirect()->route('canvas');
        }

        return view('canvas::auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  LoginRequest  $request
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->route('canvas');
    }

    public function index(Request $request)
    {
        $token = $request->get('token', null);
        if ($token === null) {
            return response('Bad request', 400);
        }

        try {
            $user = JWTAuth::setToken($token)->authenticate();
            if ($user->canvas === null) {
                throw new Exception('Canvas user not found for user ' . $user->id);
            }

            auth('canvas')->login($user->canvas);
            $request->session()->regenerate();
            return redirect()->to(route('canvas') . '/posts');
        } catch (\Exception $e) {
            \Log::error($e->getMessage(), [$e]);
            return response('Unauthorized', 401);
        }
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('canvas')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('canvas.login');
    }
}
