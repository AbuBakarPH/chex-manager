<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Auth\LoginRequest;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        if (Auth::guard('web')->attempt($validated)) {
            $user = Auth::guard('web')->user()->load('photo');
            if ($user->getRoleNames()[0] != 'Staff') {
                return $this->error("Access denied. Use MyChex manager portal.", 422);
            }

            if ($user['status'] == 0) {
                return $this->error("Inactive account. Contact manager.", 422);
            }

            if ($user->company && $user->company->theme_setting) {
                $data['company_photo'] = $user->company->theme_setting->photo;
            }
            $data['role']   = $user->getRoleNames()[0];
            $data['token']  = $user->createToken('Chex-app')->plainTextToken;
            $data['permission']    = $user->getAllPermissions()->pluck('name')->toArray();
            $ip_address = json_decode(file_get_contents('https://httpbin.org/ip'), true);
            $login_history['ip_address'] =  $ip_address['origin'];
            $login_history['user_agent'] = request()->userAgent();
            $user->login_history()->create($login_history);
            $data['profile'] = collect($user)->except(['roles', 'permissions']);
            return $this->response('login successfully', $data);
        } else {
            return $this->error("Invalid email or password.", 422);
        }
    }
}
