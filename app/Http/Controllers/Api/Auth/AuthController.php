<?php

namespace App\Http\Controllers\Api\Auth;

use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Psr\Http\Message\StreamInterface;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Exception\BadResponseException;

class AuthController extends Controller
{
    /**
     * @param Request $request
     * @return mixed
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:55',
            'username'  => 'required|string|unique:users',
            'email'     => 'email|required|unique:users',
            'password'  => 'required|min:8|confirmed',
        ]);

        $data['password'] = bcrypt($request->password);

        return User::create($data);
    }

    /**
     * @param Request $request
     * @return JsonResponse|StreamInterface
     */
    public function login(Request $request)
    {
        $data = $request->only('username', 'password');

        $validator = Validator::make($data, [
            'username'  => 'required|string',
            'password'  => 'required',
        ]);

        if (! $validator->fails())
        {
            $http = new Client();

            try {
                $response = $http->post(config('services.passport.login_endpoint'), [
                    'form_params' => [
                        'grant_type'    => 'password',
                        'client_id'     => config('services.passport.client_id'),
                        'client_secret' => config('services.passport.client_secret'),
                        'username'      => $request->username,
                        'password'      => $request->password,
                        'scope'         => '*',
                    ]
                ]);

                return $response->getBody();
            } catch (BadResponseException $e) {
                if ($e->getCode() === 400)
                {
                    return response()->json(['error' => 'Wrong Credentials! Please check your inputs.'], 400);
                }

                return response()->json(['error' => 'Oops! Something weird happened on the server.'], $e->getCode());
            }
        }
        else
        {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    }

    /**
     * @return JsonResponse
     */
    public function logout()
    {
        auth()->user()->tokens->each(function ($token) {
            $token->delete();
        });

        return response()->json('', 204);
    }
}
