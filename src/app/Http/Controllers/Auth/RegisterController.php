<?php

namespace App\Http\Controllers\Auth;

use Laravel\Fortify\Http\Controllers\RegisteredUserController as FortifyRegisterController;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Fortify\Contracts\RegisterResponse;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class RegisterController extends FortifyRegisterController
{
  /**
   * ユーザー登録処理
   */
  public function store(Request $request, CreatesNewUsers $creator): RegisterResponse
  {

    $response = parent::store($request, $creator);


    /** @var User $user */
    $user = auth()->user();

    if ($user) {
      $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        [
          'id' => $user->getKey(),
          'hash' => sha1($user->getEmailForVerification()),
        ]
      );


      session(['verificationUrl' => $verificationUrl]);

      return new class implements RegisterResponse {
        public function toResponse($request)
        {
          return redirect()->route('verification.notice');
        }
      };
    }

    // $user が取得できなかった場合は Fortify 標準のレスポンスを返す
    return $response;
  }
}
