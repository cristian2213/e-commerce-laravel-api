<?php


namespace App\services;

use Illuminate\Http\JsonResponse;
use Lukasoppermann\Httpstatus\Httpstatus;
use Lukasoppermann\Httpstatus\Httpstatuscodes;

class TokenService implements Httpstatuscodes
{
    public Httpstatus $httpStatus;

    public function __construct()
    {
        $this->httpStatus = new Httpstatus();
    }

    public function generateToken($data)
    {
        ['email' => $email, 'roles' => $roles, 'credentials' => $credentials] = $data;

        $token = auth()->claims([
            'email' => $email,
            'roles' => $roles
        ])->attempt($credentials);

        return $token;
    }

    public function respondWithToken(string $token): JsonResponse
    {
        return response()->json([
            'statusCode' => self::HTTP_OK,
            'token' => $token,
            'tokenType' => 'bearer',
            'expiresInMiliseconds' => auth()->factory()->getTTL() * 60000,
        ], self::HTTP_OK);
    }
}
