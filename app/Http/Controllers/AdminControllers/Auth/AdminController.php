<?php

namespace App\Http\Controllers\AdminControllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\AdminControllers\Auth\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\Admin\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;
use App\Http\Requests\Auth\LoginRequest;
use App\Jobs\FetchUserIPAddress;
use App\Jobs\SendEmailJob;
use App\Models\User;
use App\Models\Admin\CompanyIpAddress;
use Kreait\Laravel\Firebase\Facades\Firebase;

class AdminController extends BaseController
{
    /**
     * Login API For All Type of Users
     * Method POST
     */
    public function userLogin(LoginRequest $request)
    {
        $validated = $request->validated();
        if (Auth::guard('web')->attempt($validated)) {
            $user = Auth::guard('web')->user()->load('photo');
            $data['profile']['company'] = $user->company;
            if ($user->company && $user->company->theme_setting) {
                $data['company_photo'] = $user->company->theme_setting->photo;
            }

            $data['profile'] = collect($user)->except(['roles', 'permissions']);
            $data['role']   = $user->getRoleNames()[0];

            // if (@fsockopen("www.example.com", 80)) 
            FetchUserIPAddress::dispatch($user, request()->userAgent());
            // Start IP Restrict
            // if ($user->getRoleNames()[0] != "Super Admin") {
            //     $ip_address = CompanyIpAddress::where('company_id', $user->company_id)->first();
            //     if (is_null($ip_address)) {
            //         return $this->sendError("IP not found, please contact Admin", 422);
            //     } else if (!is_null($ip_address)) {
            //         $active_ip = CompanyIpAddress::where('company_id', $user->company_id)->where('is_active',1)->first();
            //         if (is_null($active_ip)) {
            //             return $this->sendError("Activate your IP, please contact Admin", 422);
            //         } else {
            //             if ($login_history['ip_address'] != $active_ip->ip_address) {
            //                 return $this->sendError("You are not allowed from this workspace, please contact Admin", 422);
            //             }
            //         }
            //     }
            // }
            // End IP Restrict
            $data['permission']    = $user->getAllPermissions()->pluck('name')->toArray();
            $data['token']  = $user->createToken('Chex-app')->plainTextToken;
            return $this->successResponse($data, 'login successfully');
        } else {
            return $this->error("Invalid email or password.", 422);
        }
    }

    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 400);
        }
        $email = $request->input('email');
        $user = User::where('email', $email)->first();

        if (isset($user->otp_expiry) && ($user->otp_expiry > date('Y-m-d H:i:s')) && $user->otp_count >= 3) {
            return $this->sendError('Token is already sent. Please wait for 1 hour.', ['error' => 'Token is already sent. Please wait for 1 hour.'], 404);
        }

        if (is_null($user)) {
            return $this->sendError('User not found', ['error' => 'User not found'], 404);
        }
        $user->otp          = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $user->otp_expiry   = date("Y-m-d H:i:s", strtotime('+1 hours'));
        $user->otp_count +=  1;
        $user->save();

        //Email Request data create
        $email_data = [
            "name"          =>  $user->name,
            "email"         =>  $email,
            "subject"       =>  "Your Password Reset OTP",
            "body"          =>  "<span>Your one-time password (OTP) for resetting your password is: " . $user->otp . " <br /><br />Please use this code to complete the password reset process.</span>",
            "button_url"    =>  "",
            "button_text"   =>  "",
            'logo'          => ($user->company != null) ? $user->company->photo->path : 'public/logo.png',

        ];
        dispatch(new SendEmailJob($email_data));
        return response()->noContent();
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|min:4',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 400);
        }

        $email = $request->input('email');
        $user = User::where('email', $email)->first();

        if (is_null($user)) {
            return $this->sendError('User not found', ['error' => 'User not found'], 404);
        }

        if (now() > $user["otp_expiry"]) {
            return $this->sendError('OTP is expired', ['error' => 'OTP is expired'], 404);
        }

        if ($user["otp"] != $request->input('otp')) {
            return $this->sendError('Incorrect OTP', ['error' => 'Incorrect OTP'], 404);
        }

        return response()->noContent();
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|min:4',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 400);
        }

        $email = $request->input('email');
        $user = User::where('email', $email)->first();

        if (is_null($user)) {
            return $this->sendError('User not found', ['error' => 'User not found'], 404);
        }

        if (now() > $user["otp_expiry"]) {
            return $this->sendError('OTP is expired', ['error' => 'OTP is expired'], 404);
        }

        if ($user["otp"] != $request->input('otp')) {
            return $this->sendError('Incorrect OTP', ['error' => 'Incorrect OTP'], 404);
        }

        $user->password = $request->input('password');
        $user->otp_expiry = NULL;
        $user->otp = NULL;
        $user->otp_count = 0;
        $user->save();

        //Email Request data create
        $email_data = [
            "name"          =>  $user->name,
            "email"         =>  $email,
            "subject"       =>  "Password Updated",
            "body"          =>  "<span>This is to inform you that your Chex account password has been updated successfully. Below are your new login credentials: <br /><br /> <b> Username: </b>" . $user->name . " <br > <b>Password:</b> " . $request->input('password') . "<br /><br />Please ensure to keep this information confidential. If you did not request this change or have any concerns, please contact our support team immediately.
Thank you for using Chex. We appreciate your commitment to keeping your account secure. </span>",
            "button_url"    =>  "",
            "button_text"   =>  "",
            'logo'          => ($user->company != null) ? $user->company->photo->path : 'logo.png',

        ];
        //send user email
        dispatch(new SendEmailJob($email_data));

        return response()->noContent();
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }
        Auth::guard('admin')->logout();
        return $this->successResponse('', 'Admin successfully logged out');
    }
}
