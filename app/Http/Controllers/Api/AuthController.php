<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

use App\Validators\LoginValidator;

use App\Traits\ApiResponse;

use App\Models\User;

use DB;
use GuzzleHttp;

use Config;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use stdClass;

class AuthController extends Controller
{
    use ApiResponse;

    protected $instance = null;
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function __construct()
    {
        $this->middleware('auth:api')->only(['logout']);
    }
  

    public function login(Request $request, LoginValidator $loginValidator) {

        try {
            $input = $request->all();


            if (!$loginValidator->with($input)->passes()) {
                return $this->failResponse([
                    "message" => $loginValidator->getErrors()[0],
                    "messages" => $loginValidator->getErrors()
                ], 422);
            }


            $user = User::whereEmail($input['email'])->first();

            // If the class is using the ThrottlesLogins trait, we can automatically throttle
            // the login attempts for this application. We'll key this by the username and
            // the IP address of the client making these requests into this application.
            // if (method_exists($this, 'hasTooManyLoginAttempts') &&
            //     $this->hasTooManyLoginAttempts($request)) {
            //     $this->fireLockoutEvent($request);
            //     return $this->sendLockoutResponse($request);
            // }

            if (Hash::check($request->password, $user->password, [])) {

                $token = $this->getToken($request);
                $data = json_decode((string) $token->getBody(), true);
                $user['token_type'] = $data['token_type'];
                $user['expires_in'] = $data['expires_in'];
                $user['access_token'] = $data['access_token'];
                $user['refresh_token'] = $data['refresh_token'];

                $response = [
                    "message" => trans('message.login_success'),
                    "data" => $user
                ];

                // $this->clearLoginAttempts($request);

                return $this->successResponse($response);


            } else {
                $response = ['message'=> trans('auth.failed')];
                return $this->failResponse($response, 400);
            }

            // If the login attempt was unsuccessful we will increment the number of attempts
            // to login and redirect the user back to the login form. Of course, when this
            // user surpasses their maximum number of attempts they will get locked out.
            $this->incrementLoginAttempts($request);

        } catch (\Exception $e) {
            return $this->failResponse([
                "message" => $e->getMessage(),
            ], 500);
        }
    }

    public function logout(Request $request) {

        try {
            $token = $request->user()->token();
            $token->revoke();
            $response = [
                'message'=> trans('message.logout_success')
            ];
            return $this->successResponse($response);
        } catch (\Exception $e) {
            return $this->failResponse([
                "message" => $e->getMessage(),
            ], 500);
        }
    }
    

    protected function getToken($request) {
      
        try {
            $http = new GuzzleHttp\Client;
            $data = $http->post(env('APP_URL', 'http://localhost/mralfred_back_end/').'oauth/token', [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => env('CLIENT_ID'),
                    'client_secret' => env('CLIENT_SECRET'),
                    'username' => $request->email,
                    'password' => $request->password,
                    // 'scope' => '*',
                ],
            ]);

            return $data;
        } catch(\Exception $e){
            return $e->getMessage();
        }
    }


}
