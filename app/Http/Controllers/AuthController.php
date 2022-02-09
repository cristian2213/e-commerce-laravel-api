<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\services\HttpService;
use App\services\TokenService;
use Illuminate\Http\JsonResponse;
use Lukasoppermann\Httpstatus\Httpstatus;
use Lukasoppermann\Httpstatus\Httpstatuscodes;

class AuthController extends Controller implements Httpstatuscodes
{
    public Httpstatus $httpStatus;
    public HttpService $httpService;
    public TokenService $tokenService;

    public function __construct(HttpService $httpService, TokenService $tokenService)
    {
        $this->httpStatus = new Httpstatus();
        $this->httpService = $httpService;
        $this->tokenService = $tokenService;
        $this->middleware('api', ['except' => ['login']]);
    }

    public function login(Request $request): JsonResponse
    {
        try {
            $user = User::where('email', $request->email)->with('roles')->first();

            if (!$user)
                return response()->json([
                    'statusCode' => self::HTTP_NOT_FOUND,
                    'message' => "User #$user->id doesn't exist."
                ], self::HTTP_NOT_FOUND);

            $credentials = $request->all();
            $roles = array_map(function ($role) {
                return $role['name'];
            }, $user->roles->toArray());

            $token = $this->tokenService->generateToken([
                'email' => $credentials['email'],
                'roles' => $roles,
                'credentials' => $credentials
            ]);

            if (!$token)
                return response()->json([
                    'statusCode' => self::HTTP_UNAUTHORIZED,
                    'message' => "The email or password is invalid.",
                ], self::HTTP_UNAUTHORIZED);

            return $this->tokenService->respondWithToken($token);
        } catch (\Throwable $th) {
            return $this->httpService->manageResponseError($th);
        }
    }


    public function logOut(): JsonResponse
    {
        auth()->logout(true);
        return response()->json([
            'statusCode' => self::HTTP_OK,
            'message' => 'User logged out successfuly',
        ], self::HTTP_OK);
    }

    public function refreshToken()
    {
        try {
            $newToken = auth()->refresh();
            return $this->tokenService->respondWithToken($newToken);
        } catch (\Throwable $th) {
            return $this->httpService->manageResponseError($th);
        }
    }


    public function getAuthenticatedUser(Request $request)
    {
        $token = $request->bearerToken();
        if (!$token) return response()->json([
            'statusCode' => self::HTTP_UNAUTHORIZED,
            'message' => 'The token is required.'
        ], self::HTTP_UNAUTHORIZED);

        return response()->json([
            'statusCode' => self::HTTP_OK,
            'user' => auth()->user(),
        ], self::HTTP_OK);
    }
}
