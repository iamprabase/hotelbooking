<?php

namespace App\Http\Controllers;

use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Traits\FileManageTrait;
use App\Mail\ForgotPasswordMail;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class AuthenticationController extends Controller
{
    use FileManageTrait;

    public function createAccount(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'first_name' => 'required|max:50',
            'middle_name' => 'nullable|max:50',
            'last_name' => 'required|max:50',
            'email' => 'required|email|unique:users',
            'phone_number' => 'nullable|unique:users',
            'password' => 'required|min:6',
            'confirm_password' => 'required|same:password',
        ]);
        
        if ($validate->fails()) {
            return response([
                "message" => $validate->errors(),
            ], 422);
        }
        
        $data = $request->except('confirm_password', 'is_admin', 'status');
        $data['password'] = Hash::make($request->password);
        
        $user = User::create($data);

        if ($user) {
            $token = $user->createToken('visible')->plainTextToken;
            return response()->json([
                'user_id' => $user->id,
                'token' => $token,
                "message" => "Registration Success.",
                "avatar" => ''
            ]);
        }

        return response()->json([
            "message" => "Registration Failed.",
        ], 400);
    }
    
    public function signin(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);

        if ($validate->fails()) {
            return response([
                "message" => $validate->errors(),
            ], 422);
        }

        $field = (filter_var(request()->email, FILTER_VALIDATE_EMAIL) || !request()->email) ? 'email' : 'phone_number';
        $email = $request->email;
         
        if($field == 'phone_number') {
            $user = User::where('phone_number', $email)->first();
            if($user) {
                $email = $user->email;
            }
        }
        $is_admin = isset($request->is_admin) ? $request->is_admin : 0;
        $attemptLogin = Auth::attempt(['email' => $email, 'password' => $request->password, 'is_admin' => $is_admin]);

        if ($attemptLogin) {
            $token = Auth::user()->createToken('visible')->plainTextToken;
            $hasProfilePicture = Auth::user()->avatar;
            $path = $hasProfilePicture ? $hasProfilePicture->file_path : NULL;
            return response()->json([
                'user_id' => Auth::user()->id,
                'token' => $token,
                "message" => "Login Success.",
                "avatar" => $path ? $this->createPath(Storage::url($path)) : ''
            ]);
        }

        return response()->json([
            "message" => "Email/Phone number or Password mismatch.",
        ], 401);
    }

    public function signout()
    {
        Auth::user()->currentAccessToken()->delete();

        return response()->json([
            "message" => "User Logged Out.",
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $validate = Validator::make($request->all(), [
            'first_name' => 'required|max:50',
            'middle_name' => 'nullable|max:50',
            'last_name' => 'required|max:50',
            'email' => 'required|email|unique:users,email,'.$user->id.',id,is_admin,0',
            'phone_number' => 'nullable|unique:users,phone_number,'.$user->id.',id,is_admin,0',
        ]);
        if ($validate->fails()) {
            return response([
                "message" => $validate->errors(),
            ], 422);
        }
        try{
            $data = $request->only('first_name', 'middle_name', 'last_name', 'email', 'phone_number');
            $user->update($data);
    
            return response()->json([
                "message" => "User Profile Updated.",
            ]);

        } catch(\Exception $e) {
            return response()->json([
                "message" => "Some Error Occured.",
            ], 400);
        }
    }

    public function updateProfilePicture(Request $request)
    {
        $user = Auth::user();
        $validate = Validator::make($request->all(), [
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5024'
        ]);
        if ($validate->fails()) {
            return response([
                "message" => $validate->errors(),
            ], 422);
        }
        try{
            $image = $request->file('profile_image');
            $hasProfilePicture = Auth::user()->avatar;
            $path = $this->uploadOne($image, Auth::user()->id, 'user', $hasProfilePicture, 'user_avatars');
            return response()->json([
                "message" => "Profile Picture Updated.",
                "avatar" => $this->createPath(Storage::url($path))
            ]);
        } catch(\Exception $e) {
            return response()->json([
                "message" => $e->getMessage(),
            ], 400);
        }
    }
    
    private function sendResetToken($credentials) 
    {
        $token_exists = DB::table('password_resets')->whereEmail($credentials['email'])->first();
        $token = rand(2500, 10000);
        if($token_exists) {
            DB::table('password_resets')->whereEmail($credentials['email'])->update([
                'email' => $credentials['email'],
                'token' => $token,
                'created_at' => Carbon::now(),
            ]);
        } else {
            DB::table('password_resets')->insert([
                'email' => $credentials['email'],
                'token' => $token,
                'created_at' => Carbon::now(),
            ]);
        }
        
        return $token;
    }

    public function forgotPassword(Request $request) 
    {
        $validate = Validator::make($request->all(), [
            'email' => 'required',
        ]);
        if ($validate->fails()) {
            return response([
                "message" => $validate->errors(),
            ], 422);
        }

        $credentials = $request->only('email');
        $email = $credentials['email'];

        $field = (filter_var($email, FILTER_VALIDATE_EMAIL) || !$email) ? 'email' : 'phone_number';
         
        if($field == 'phone_number') {
            $user = User::where('phone_number', $email)->first();
            if($user) {
                $email = $user->email;
            }
        } else {
            $user = User::where('email', $email)->first();
            if($user) {
                $email = $user->email;
            }
            $token = $this->sendResetToken($credentials);
    
            try{
                // Mail::to(Auth::user()->email)->send(new ForgotPasswordMail($token));
                
                return response()->json(["msg" => 'Reset password token.', 'link' => $token]);
            } catch(\Exception $e) {
                return response()->json([
                    "message" => "Some Error Occured.",
                ], 400);
            }
        }

        return response()->json(["msg" => 'Email/phonenumber mismatch.'], 401);

    }

    public function passwordReset(Request $request) 
    {
        $validate = Validator::make($request->all(), [
            'email' => 'required',
            'token' => 'required',
            'password' => 'required|min:6',
            'confirm_password' => 'required|same:password',
        ]);
        if ($validate->fails()) {
            return response([
                "message" => $validate->errors(),
            ], 422);
        }

        $credentials = $request->password;
        $token = DB::table('password_resets')->whereEmail($request->email)->first();
        if($token->token!=$request->token) {
            return response()->json([
                "message" => "Invalid Token.",
            ], 400);
        }

        $user = User::whereEmail($request->email)->orWhere('name', 'LIKE', $request->email)->update([
            'password' => Hash::make($credentials)
        ]);

        if ($user && !$user->is_admin) {
            DB::table('password_resets')->whereEmail($request->email)->delete();
            return response()->json([
                "message" => "Password Updated.",
            ]);
        }

        return response()->json([
            "message" => "Failed Resetting.",
        ], 400);
    }
}
