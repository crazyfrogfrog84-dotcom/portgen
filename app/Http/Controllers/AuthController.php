<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Portfolio;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    private static ?string $dummyPasswordHash = null;

    public function showLogin(Request $request): View
    {
        if ($request->query->has('intended')) {
            $this->rememberSafeIntended($request->query('intended'), $request);
        }

        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();
        $email = Str::lower(trim($credentials['email']));
        $key = 'portgen-login:'.hash('sha256', $email.'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return $this->loginError($email, $key);
        }

        $user = User::query()->where('email', $email)->first();
        $authenticated = Auth::attempt([
            'email' => $email,
            'password' => $credentials['password'],
        ], $request->boolean('remember'));

        if (! $user) {
            $this->dummyPasswordCheck($credentials['password']);
        }

        if (! $authenticated) {
            RateLimiter::hit($key, 60);

            return $this->loginError($email, $key);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        return $this->redirectToSafeIntended($request)
            ->with('status', 'Logged in. Welcome back.');
    }

    public function showRegister(Request $request): View
    {
        $claimUrl = $request->query('claim_url');

        return view('auth.register', [
            'claimUrl' => is_string($claimUrl) && $this->claimablePortfolio($claimUrl, $request) ? $claimUrl : null,
        ]);
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $claimUrl = $request->validated('claim_url');
        $claimPortfolio = $claimUrl ? $this->claimablePortfolio($claimUrl, $request) : null;
        if ($claimUrl && ! $claimPortfolio) {
            return back()
                ->withErrors(['claim_url' => 'That private link cannot claim this portfolio. Reopen the original link and try again.'])
                ->withInput($request->safe()->except(['password', 'password_confirmation']));
        }

        $user = User::query()->create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
        ]);

        if ($claimPortfolio) {
            Gate::forUser($user)->authorize('claim', $claimPortfolio);
            $claimPortfolio->user_id = $user->id;
            $claimPortfolio->save();
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with(
            'status',
            $claimPortfolio ? 'Account created and portfolio added to your account.' : 'Account created. Your portfolios are ready to manage here.'
        );
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portfolios.create')->with('status', 'Logged out.');
    }

    private function loginError(string $email, string $key): RedirectResponse
    {
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return back()->withErrors([
                'email' => 'Too many attempts. Try again in '.$seconds.' seconds.',
            ])->withInput(['email' => $email])->with('lockoutSeconds', $seconds)->setStatusCode(429);
        }

        return back()->withErrors(['email' => 'The email or password is incorrect.'])
            ->withInput(['email' => $email]);
    }

    private function dummyPasswordCheck(string $password): void
    {
        self::$dummyPasswordHash ??= Hash::make(Str::random(64));
        Hash::check($password, self::$dummyPasswordHash);
    }

    private function rememberSafeIntended(mixed $intended, Request $request): void
    {
        $safePath = $this->sameSitePath($intended, $request);
        if ($safePath !== null) {
            $request->session()->put('url.intended', $safePath);
        }
    }

    private function redirectToSafeIntended(Request $request): RedirectResponse
    {
        $safePath = $this->sameSitePath($request->session()->pull('url.intended'), $request);

        return redirect()->to($safePath ?? route('dashboard'));
    }

    private function sameSitePath(mixed $candidate, Request $request): ?string
    {
        if (! is_string($candidate) || $candidate === '' || preg_match('/[\x00-\x1F\\\\]/', $candidate)) {
            return null;
        }

        $parts = parse_url($candidate);
        if ($parts === false || isset($parts['user']) || isset($parts['pass'])) {
            return null;
        }

        if (isset($parts['host'])) {
            if (strtolower($parts['host']) !== strtolower($request->getHost())) {
                return null;
            }
            if (isset($parts['scheme']) && ! in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
                return null;
            }
            if (isset($parts['port']) && $parts['port'] !== $request->getPort()) {
                return null;
            }
        } elseif (isset($parts['scheme'])) {
            return null;
        }

        $path = $parts['path'] ?? '/';
        if (! str_starts_with($path, '/') || str_starts_with($path, '//')) {
            return null;
        }

        return $path.(isset($parts['query']) ? '?'.$parts['query'] : '');
    }

    private function claimablePortfolio(string $claimUrl, Request $request): ?Portfolio
    {
        $parts = parse_url($claimUrl);
        if ($parts === false || ! isset($parts['host']) || ! isset($parts['scheme'])
            || strtolower($parts['host']) !== strtolower($request->getHost())
            || (isset($parts['port']) && $parts['port'] !== $request->getPort())
            || ! in_array(strtolower($parts['scheme']), ['http', 'https'], true)
            || ! preg_match('#^/manage/([A-Za-z0-9]{10})$#', $parts['path'] ?? '', $matches)) {
            return null;
        }

        $signedRequest = Request::create($claimUrl, 'GET');
        if (! URL::hasValidSignature($signedRequest)) {
            return null;
        }

        return Portfolio::query()->where('slug', $matches[1])->whereNull('user_id')->first();
    }
}
