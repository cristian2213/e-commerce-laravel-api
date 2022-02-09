<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use App\services\HttpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Lukasoppermann\Httpstatus\Httpstatus;
use Lukasoppermann\Httpstatus\Httpstatuscodes;


class UsersController extends Controller implements Httpstatuscodes
{
    public Httpstatus $httpStatus;
    public HttpService $httpService;

    public function __construct(HttpService $httpService)
    {
        $this->httpStatus = new Httpstatus();
        $this->httpService = $httpService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $users = User::with('roles')->paginate(10);
            return response()->json([
                'users' => $users
            ], self::HTTP_OK);
        } catch (\Throwable $th) {
            return $this->httpService->manageResponseError($th);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = DB::table('users')->where('email', $request->email)->first();
            if ($user)
                return $this->returnJSONReponse(self::HTTP_NOT_FOUND, "The email $request->email exists already.");

            ['password' => $password, 'confirmPassword' => $confirmPassword, 'roles' => $roles] = $request->all();

            if ($password !== $confirmPassword)
                return response()->json([
                    'statusCode' => self::HTTP_BAD_REQUEST,
                    'message' => "Passwords dont't match."
                ], self::HTTP_BAD_REQUEST);

            $request->password = Hash::make($request->password);
            $newUser = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
            ]);


            $newRoles = [];
            for ($i = 0; $i < count($roles); $i++) {
                $newRole = Role::create([
                    'name' => $roles[$i],
                    'user_id' => $newUser->id
                ]);
                array_push($newRoles, $newRole);
            }

            $newUser['roles'] = $newRoles;
            return response()->json($newUser);
        } catch (\Throwable $th) {
            return $this->httpService->manageResponseError($th);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id)
    {
        try {
            $user = User::find($id);
            if (!$user)
                return $this->returnJSONReponse(self::HTTP_NOT_FOUND, "The user #$id doesn't exist.");

            return response()->json($user, self::HTTP_OK);
        } catch (\Throwable $th) {
            return $this->httpService->manageResponseError($th);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            return true;
            $user = User::find($id);
            if (!$user)
                return $this->returnJSONReponse();

            $dataToUpdate = array_filter($request->all(), function ($value, $key) {
                if ($key !== 'roles') return $value;
            }, ARRAY_FILTER_USE_BOTH);

            $updatedUser = $user->update($dataToUpdate);

            if ($request->roles) {
                $roles = Role::where('user_id', $id)->whereNull('deleted_at')->get();
                if (count($roles) < 1) return null;

                foreach ($roles as $role) {
                    $role->deleted_at = Carbon::now();
                    $role->save();
                }
            }



            return response()->json($updatedUser);
        } catch (\Throwable $th) {
            return $this->httpService->manageResponseError($th);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $user = User::find($id);
            if (!$user)
                return $this->returnJSONReponse(self::HTTP_NOT_FOUND, "The user #$id doesn't exist.");

            User::destroy($id);
            $roles = Role::where('user_id', $id)->whereNull('deleted_at')->get();
            if (count($roles) > 0) {
                foreach ($roles as $role) {
                    $role->deleted_at = Carbon::now();
                    $role->save();
                }
            }

            return response()->json([
                'statusCode' => self::HTTP_OK,
                'message' => 'User deleted',
            ], self::HTTP_OK);
        } catch (\Throwable $th) {
            return $this->httpService->manageResponseError($th);
        }
    }

    /**
     * Return a Json Response if the user doesn't exist
     * @param int $statusCode
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function returnJSONReponse(
        int $statusCode = self::HTTP_NOT_FOUND,
        string $message = null
    ): JsonResponse {
        return response()->json([
            'statusCode' => $statusCode,
            'message' => $message ?? $this->httpStatus->getReasonPhrase(self::HTTP_NOT_FOUND)
        ]);
    }
}
