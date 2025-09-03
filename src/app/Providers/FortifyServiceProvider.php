<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use App\Actions\Fortify\CreateNewUser;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;
use App\Http\Requests\Auth\LoginRequest as AppLoginRequest;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Laravel\Fortify\Contracts\RegisterResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Fortify\Contracts\LogoutResponse;

class FortifyServiceProvider extends ServiceProvider
{
  public function register()
  {
    $this->app->bind(FortifyLoginRequest::class, AppLoginRequest::class);
  }

  public function boot()
  {
    $this->app->bind(CreatesNewUsers::class, CreateNewUser::class);

    Fortify::loginView(fn() => view('auth.login'));
    Fortify::registerView(fn() => view('auth.register'));

    // 会員登録処理（CreateNewUserで処理）
    Fortify::createUsersUsing(CreateNewUser::class);

    // ログイン時のバリデーション
    Fortify::authenticateUsing(function (Request $request) {
      $loginRequest = new LoginRequest();
      $loginRequest->setContainer(app())
        ->setRedirector(app('redirect'))
        ->merge($request->all());

      $loginRequest->validateResolved();

      $user = User::where('email', $request->email)->first();

      if ($user && Hash::check($request->password, $user->password)) {
        return $user;
      }

      RateLimiter::for('custom-login', function (Request $request) {
        return Limit::perMinute(100)->by($request->email . $request->ip());
      });

      throw ValidationException::withMessages([
        'email-login' => 'ログイン情報が登録されていません。',
      ]);
    });

    // ログアウト後のリダイレクト
    $this->app->singleton(LogoutResponse::class, function () {
      return new class implements LogoutResponse {
        public function toResponse($request)
        {
          return redirect('/login');
        }
      };
    });
    $this->app->singleton(RegisterResponse::class, function () {
      return new class implements RegisterResponse {
        public function toResponse($request)
        {
          Session::put('first_login', true);
          return redirect()->route('profile.edit');
        }
      };
    });
  }
}
