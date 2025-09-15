<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use App\Models\UserRoles;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Editor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register','forgotPassword','reset','verifyEmail']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
    
        if (!$token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    
        // Check if the email is verified before generating a JWT token
        $user = auth()->user();
    
        if (!$user->verified) {
            return response()->json(['error' => 'Email not verified. Please check your email for verification.'], 401);
        }
    
        // Save the token into the personal_access_tokens table
        $user->tokens()->delete(); // Remove existing tokens
        $user->createToken('personal-token')->plainTextToken;
    
        return $this->respondWithToken($token);
    }//public function login(Request $request)
    

    public function register(Request $request)
    {
        // Validation rules for registration fields
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Check if the email is already used
        if (User::where('email', $request->input('email'))->exists()) {
            return response()->json(['message' => 'Email is already in use.'], 422);
        }
    
        // Create a new user with a concatenated username
        $user = User::create([
            'username' => $request->input('firstname'), // Modify this line based on your username generation logic
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
            'role_id' => 4,
            'verification_token' => Str::random(60), // Generate a unique token for email verification
            'verified' => false, // Mark the user as unverified
        ]);

        $verificationToken = $user->verification_token;

        Mail::send('email.verification', ['token' => $verificationToken, 'user' => $user], function ($message) use ($user) {
            $message->to($user->email);
            $message->subject('Email Verification');
        });
    
    
        // Create a new client (adjust fields based on your requirements)
        $client = Client::create([
            'user_id' => $user->id,
            'firstname' => $request->input('firstname'),
            'middlename' => $request->input('middlename'),
            'lastname' => $request->input('lastname'),
            // Uncomment and include other fields if needed
        ]);
    
        // Generate a JWT token for the registered user
        $token = auth()->login($user);
    
        return $this->respondWithToken($token);
    }//public function register(Request $request)

    public function verifyEmail($token)
    {
        
        $user = User::where('verification_token', $token)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid verification token.'], 422);
        }

        $user->verified = true;
        $user->verification_token = null;
        $user->save();

        return response()->json(['message' => 'Email verified successfully.'], 200);
    } //public function verifyEmail($token)

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        // return response()->json(auth()->user());

        $user = Auth::user();
        $data = User::where('id', $user->id)->get();
        $role = UserRoles::find($user->role_id);
        $client = Client::where('user_id', $user->id)->get();
        $editorData = Editor::where('user_id', $user->id)->get();

        $dataUser = [
            'id'        => auth()->user()->id,
            'email'     => auth()->user()->email,
            'name' => auth()->user()->username,
            'user'    => $user,
            'role' => $role->name,
            'data'      => $data,
            'clientData' => $client,
            'editorData' => $editorData,
        ];
        return response()->json(['success' => true, 'dataUser' => $dataUser], 200);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {

        // id || Role Name
        //=========================
        // 1  || Gode Mode
        // 2  || Admin
        // 3  || Editor
        // 4  || Client


        if (Auth::user()->role_id == 1) {
            $ability = [
                ['action' => 'manage', 'subject' => 'SystemSuperAdmin'],
                ['action' => 'manage', 'subject' => 'SystemAndAdmin'],
            ];
        }
        if (Auth::user()->role_id == 2) {
            $ability = [
                ['action' => 'manage', 'subject' => 'SystemAdmin'],
                ['action' => 'manage', 'subject' => 'SystemAndAdmin'],
            ];
        }
        if (Auth::user()->role_id == 3) {
            $ability = [
                ['action' => 'manage', 'subject' => 'Editor'],
                ['action' => 'manage', 'subject' => 'SystemAndEditor'],
            ];
        }
        if (Auth::user()->role_id == 4) {
            $ability = [
                ['action' => 'manage', 'subject' => 'Client'],
                ['action' => 'manage', 'subject' => 'SystemAndClient'],
            ];
        }

        $user = Auth::user();
        $role = UserRoles::find($user->role_id);
        $client = Client::where('user_id', $user->id)->get();
        $editorData = Editor::where('user_id', $user->id)->get();

        $clients = Client::where('user_id', $user->id)->first();
        $editor = Editor::where('user_id', $user->id)->first();
    
        // Check if the user is disabled as a client or editor
        if (($clients && $clients->status === 'Disabled') || ($editor && $editor->status === 'Disabled')) {
            return response()->json(['error' => 'Account disabled'], 403);
        }


        if ($role->id == 1) {
            $users = 'RESEARCH CENTER';
        }
        // if ($role->id == 2) {
        //     $userAgent = Agent::where('user_id', $user->id)->first();
        //     $userData = User::find($userAgent->user_id);
        //     $users = $userData->users;
        // }
        // if ($role->id >= 3) {
        //     $operator = Operator::where('user_id', $user->id)->first();
        //     $operatorData = User::find($operator->user_id);
        //     $users = $operatorData->users;
        // }

        $dataUser = [
            'id'        => auth()->user()->id,
            'email'     => auth()->user()->email,
            'name' => auth()->user()->username,
            'user'    => $user,
            'role'      => $role->name,
            'clientData' => $client,
            'editorData' => $editorData,
            'ability'   => $ability
        ];

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => 10 * 365 * 24 * 60 * 60,
            'user'          => $dataUser,
            'success' => true,
        ]);
    } //protected function respondWithToken($token)

    public function guard()
    {
        return Auth::guard();
    } // public function guard()


    public function Editpass($id)
    {
    //   $user = Auth::user();
      $changepass = User::findOrFail($id);
      if (request('newpass')) {
        $changepass->password = bcrypt(request('newpass'));
        $changepass->save();
      }
      return response()->json(['success' => true, 'changepass' => $changepass]);
    }//public function Editpass($id)

    public function EditorResetpass($id)
    {
      $changepass = User::findOrFail($id);
      if (request('newpass')) {
        $changepass->password = bcrypt(request('newpass'));
        $changepass->save();

        $reset_status = 0;
        $reset_editor = Editor::where('user_id', $id);
        $reset_editor->update([
            'reset_password_status' => $reset_status,
        ]);
      }
      return response()->json(['success' => true, 'changepass' => $changepass]);
    }//public function EditorResetpass($id)
  
    public function getPasswordData()
    {
      $user = Auth::user();
      $password = $user->password;
  
      return response()->json([
          'success' => true,
          'password' => $password,
      ]);
    }//public function getPasswordData()

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
    
        $user = User::where('email', $request->email)->first();
    
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
    
        // Generate a random token for password reset
        $token = Str::random(60);
    
        DB::table('password_resets')->updateOrInsert(
            ['email' => $user->email],
            ['token' => $token, 'created_at' => now()]
        );

        // Create a new token for the user and associate the password reset token
        $sanctumToken = $user->createToken('password-reset', ['reset_token' => $token])->plainTextToken;
    
        Mail::send('email.forgetPassword', ['token' => $token, 'user' => $user], function ($message) use ($user) {
            $message->to($user->email);
            $message->subject('Reset Password');
        });
    
        return response()->json(['message' => 'Password reset link sent successfully']);
    }//public function forgotPassword(Request $request)

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'password' => 'required|confirmed|min:6',
        ]);
    
        // Check if the user with the given token exists
        $passwordReset = DB::table('password_resets')
            ->where('token', $request->token)
            ->first();
    
        if (!$passwordReset) {
            return response()->json(['message' => 'Invalid or expired token'], 422);
        }
    
        $user = User::where('email', $passwordReset->email)->first();
    
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
    
        // Update the user's password
        $user->update([
            'password' => Hash::make($request->password),
        ]);
    
        // Optionally, you may want to invalidate the used token
        DB::table('password_resets')->where('token', $request->token)->delete();
    
        return response()->json(['message' => 'Password reset successfully'], 200);
    }
    //public function reset(Request $request)
}
