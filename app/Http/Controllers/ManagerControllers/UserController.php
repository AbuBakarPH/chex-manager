<?php

namespace App\Http\Controllers\ManagerControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\FetchHistorySchedule;
use App\Http\Requests\ScheduleAnswerRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Models\Admin\Category;
use App\Models\Admin\Company;
use App\Services\Interfaces\UserInterface;
use App\Models\User;
use App\Models\Admin\Role;
use App\Models\Schedule;
use App\Models\ScheduleAnswer;
use App\Services\MeidaService;
use Illuminate\Support\Facades\Validator;
use App\Rules\HistoryScheduleDateRange;
use Carbon\Carbon;
use Laravel\Sanctum\PersonalAccessToken;

class UserController extends Controller
{
    public function __construct(private UserInterface $service, private MeidaService $meidaService)
    {
    }


    public function index(Request $request)
    {
        return $this->response('User listing', $this->service->index($request), 200);
    }

    public function store(StoreUserRequest $request)
    {
        $user = $this->service->store($request);
        $role = $user->getRoleNames()[0];
        return $this->response(
            $role . ' created successfully',
            $user,
            200
        );
    }


    public function show($id)
    {
        $user = User::where('id', $id)->with('photo', 'roles')->first();
        $user['role'] = $user->getRoleNames()[0];
        return $this->response('User detail', $user, 200);
    }

    public function update(UpdateUserRequest $request, $id)
    {
        $user = User::findOrFail($id);
        $user = $this->service->update($request, $user);
        $role = $user->getRoleNames()[0];
        return $this->response(
            $role . ' updated successfully',
            $user
        );
    }

    public function destroy($id)
    {
        $user = User::findOrfail($id);
        return $this->response(
            'User deleted successfully',
            $this->service->delete($user)
        );
    }


    /**
     * Becuase of that data is not saved in
     * quasar store for that reason here i am getting only logged use data
     */
    public function getUserDetail(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $user['photo'] = $user->photo ?? (object)[];
        $user['token'] = str_replace("Bearer ", "", $request->header('Authorization'));
        $user['profile'] = collect($user)->except(['roles', 'permissions']);
        $user['role']   = $user->getRoleNames()[0];
        $user['permission']    = $user->getAllPermissions()->pluck('name')->toArray();
        if ($user->company && $user->company->theme_setting) {
            $user['company_photo'] = $user->company->theme_setting->photo;
        }

        return $this->response('User detail', $user);
    }

    public function getStaffList(Request $request)
    {
        $users = $this->service->staffList($request);
        return $this->response('Staff List', $users);
    }

    /** 
     * Update User Password By Admin
     */

    public function updateUserPassword(Request $request, $id)
    {
        $user = User::findOrfail($id);
        return $this->response(
            'Update User Password successfully',
            $this->service->updatePassword($request, $user),
            200
        );
    }

    public function getUserRole($role)
    {
        $users = User::role($role)->where('company_id', Auth::user()->company_id)->get();
        return $this->response('Staff List', $users);
    }

    public function update_token(Request $request)
    {
        User::where('device_token', $request['device_token'])->update(['device_token' =>  NULL]);
        $user = auth()->user();
        $user->update(['device_token' => $request['device_token']]);
        return $this->response('token updated', $user);
    }

    public function allowNotification(Request $request)
    {
        $user = auth()->user();
        $company = Company::where('id', $user->company_id)->first();
        $update = $request['type'] == 'allow_notification' ? $company->allow_notification : $company->allow_email;
        $company->update([$request['type'] => !$update]);

        return $this->response('record updated', $company);
    }

    public function handleAvatar(Request $request)
    {
        $data = $request->validate([
            'avatar_id' => 'nullable|in:7365,2198,5847,3921,6574,1236,8402,5673,9814,4269,1738,6492',
        ], [
            'avatar_id.in' => 'The selected avatar is invalid.',
        ]);

        $user = auth()->user();
        $user->update($data);
        return response()->noContent();
    }
}
