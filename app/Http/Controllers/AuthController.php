<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 * name="Authentication",
 * description="API Endpoints for User Authentication"
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     * path="/api/auth/register",
     * summary="Register a new user",
     * tags={"Authentication"},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"name","email","password","password_confirmation"},
     * @OA\Property(property="name", type="string", example="John Doe"),
     * @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     * @OA\Property(property="password", type="string", format="password", example="password123"),
     * @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="User registered successfully",
     * @OA\JsonContent(
     * @OA\Property(property="token", type="string", example="your-auth-token"),
     * @OA\Property(property="user", type="object",
     * @OA\Property(property="id", type="string", format="uuid", example="some-uuid"),
     * @OA\Property(property="name", type="string", example="John Doe"),
     * @OA\Property(property="email", type="string", format="email", example="john.doe@example.com")
     * )
     * )
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation error",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Validation failed."),
     * @OA\Property(property="errors", type="object", example={"email": {"The email has already been taken."}})
     * )
     * ),
     * @OA\Response(
     * response=500,
     * description="An error occurred during registration.",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="An error occurred during registration."),
     * @OA\Property(property="error", type="string", example="Error details here")
     * )
     * )
     * )
     */
    public function register(RegisterRequest $request)
    {
        $validatedData = $request->validated();
        try {
            $user = User::create([
                "name" => $validatedData["name"],
                "email" => $validatedData["email"],
                "password" => $validatedData["password"],
            ]);

            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred during registration.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     * path="/api/auth/login",
     * summary="Authenticate user and return access token",
     * tags={"Authentication"},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"email","password"},
     * @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     * @OA\Property(property="password", type="string", format="password", example="password123")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="User authenticated successfully",
     * @OA\JsonContent(
     * @OA\Property(property="token", type="string", example="your-auth-token"),
     * @OA\Property(property="user", type="object",
     * @OA\Property(property="id", type="string", format="uuid", example="some-uuid"),
     * @OA\Property(property="name", type="string", example="John Doe"),
     * @OA\Property(property="email", type="string", format="email", example="john.doe@example.com")
     * )
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthorized",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Unauthorized")
     * )
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation error",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="The given data was invalid."),
     * @OA\Property(property="errors", type="object", example={"email": {"The email field is required."}})
     * )
     * ),
     * @OA\Response(
     * response=500,
     * description="An error occurred during login.",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="An error occurred during login."),
     * @OA\Property(property="error", type="string", example="Error details here")
     * )
     * )
     * )
     */
    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                "email" => "required|email",
                "password" => "required"
            ]);

            if (!Auth::attempt($credentials)) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $user = $request->user(); // Auth::user() também funciona aqui
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ]);
        } catch (ValidationException $e) { // Captura ValidationException para 422
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $ex) { // Captura outras exceções
            return response()->json(['message' => $ex->getMessage()], 500);
        }
    }
}
