<?php

namespace App\Services;

use App\Models\User;
use App\Jobs\SendEmailJob;
use App\Models\Admin\Role;
use App\Models\Admin\Media;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Notifications\GeneralNotification;
use App\Services\Interfaces\UserInterface;
use Illuminate\Support\Facades\Notification;


/**
 * Class UserService
 * @package App\Services
 */
class UserService implements UserInterface
{
    public function __construct(private User $model, private MeidaService $meidaService, private GeneralService $generalService)
    {
        // $this->generalService = $generalService;
        // parent::__construct($model);
    }

    public function index($request)
    {
        $roles = [];
        $where = [];
        if ($request['roles']) {
            $roles = $request['roles'];
        } else {
            // $roles = Role::whereNotIn('name', ['Super Admin', 'Manager'])->pluck('name')->toArray();
            $roles = Role::whereNotIn('name', ['Super Admin'])->pluck('name')->toArray();
        }
        $searchCols = ['name', 'email', 'first_name', 'last_name', 'cnic', 'phone', 'address'];
        $relationalCols = [
            'roles' => ['name'],
        ];
        if (Auth::user()->roles->pluck('name')[0] == 'Super Admin') {
            array_push($where, [
                'key' => 'company_id',
                'operator' => '=',
                'value' => $request['company_id']
            ]);
        } else if (Auth::user()->roles->pluck('name')[0] == 'Manager') {
            array_push($where, [
                'key' => 'company_id',
                'operator' => '=',
                'value' => Auth::user()->company_id
            ]);
        }

        $data = User::with(['login_history', 'roles', 'photo']);

        if (count($roles)) {
            $data = $data->role($roles);
        }

        if ($request['status']) {
            $data = $data->where('status', $request['status']);
        }

        if (isset($request['manager']) && $request['manager']) {
            $data = $data->where('id', '!=', Auth::user()->id);
        }

        $data = $this->generalService->handleWhere($data, $where);
        $data = $this->generalService->handleSearch($request['searchText'], $data, $searchCols, '', $relationalCols);

        if ($request['perpage'] == 'All') {
            return $this->generalService->handleAllData($request, $data);
        } else {
            return $this->generalService->handlePagination($request, $data);
        }
    }


    // public function index($req, $pageType = null, $with = [], $parent = false, $where = [], $orWhere = [], $user_role = [], $whereIn = [])
    // {
    //     return $req->perpage == 'all' ? $this->getAll($req, $pageType, $with, $parent, $where, $orWhere, $user_role, $whereIn) : $this->paginatePerPage($req, $pageType, $with, $parent, $where, $orWhere, $user_role, $whereIn);
    // }

    public function getAll($req, $pageType, $with, $parent, $where, $orWhere, $user_role, $whereIn)
    {
        if ($pageType != null) {
            return $this->withSearch($req, $parent)
                ->with($with)
                ->where('page_type', $pageType)
                ->get();
        }

        return $this->withSearch($req, $parent)
            ->with($with)
            ->get();
    }


    public function paginatePerPage($req, $pageType, $with, $parent, $where, $orWhere, $user_role, $whereIn)
    {
        $data = $this->withSearch($req, $parent)->with($with);
        if ($pageType != null) {

            return $this->withSearch($req, $parent)
                ->with($with)
                ->where('page_type', $pageType)
                ->paginate($req->perpage ? $req->perpage : 10);
        }
        if ($user_role) {
            $data = $data->role($user_role);
        }

        if ($req['category_id'] != '') {
            $data = $data->where('category_id', $req['category_id']);
        }

        if ($req['sub_category_id'] != '') {
            $data = $data->where('sub_category_id', $req['sub_category_id']);
        }

        if ($req['role'] != '') {
            $data = $data->role($req['role']);
        }

        if (count($whereIn) > 0) {
            foreach ($whereIn as $param) {
                $data = $data->whereIn($param['key'], $param['value']);
                return  $data->paginate($req->perpage ? $req->perpage : 10);
            }
        }
        if (count($where)) {
            foreach ($where as $param) {
                $data = $data->where($param['key'], $param['operator'], $param['value']);
            }
            $data = $data->paginate($req->perpage ? $req->perpage : 10);
            return $data;
        }



        return $this->withSearch($req, $parent)
            ->with($with)
            ->paginate($req->perpage ? $req->perpage : 10);
    }

    private function withSearch($req, $parent)
    {
        $mode = $this->model;
        $orderBy = $req->orderBy ? $req->orderBy : 'id';
        $seq = $req->seq == 'true' ? 'desc' : 'asc';
        $searchQuery = $req->query('searchText');
        $query =  $mode::where(function ($q) use ($searchQuery, $mode) {
            foreach ($mode->getFillable() as $col) {
                $q->orWhere($col, 'like', "%{$searchQuery}%");
            }
        })->orderBy($orderBy, $seq);

        if ($parent) {
            $query = $query->where('parent_id', null);
        }

        return $query;
    }

    public function setCNIC()
    {
        $date = now()->format('mdY');
        if (Auth::user()->hasRole('Super Admin')) {
            $title      = Auth::user()->name;
        } else {
            $title      = Auth::user()->company->title ?? Auth::user()->name;
        }
        $words = explode(' ', $title);
        $acronym = implode('', array_map(function ($word) {
            return strtoupper($word[0]);
        }, $words));
        $randomNumber = rand(1, 1000000);

        $CNIC = $acronym . $date . $randomNumber;
        return $CNIC;
    }


    public function store($request)
    {
        $user_password = $this->generateStrongPassword(10);
        $validated = $request->validated();
        $validated['password'] = Hash::make($user_password);
        $validated['company_id'] = Auth::user()->company_id;
        $validated['name'] = $validated['first_name'] . ' ' . $validated['last_name'];
        $validated['cnic'] = $this->setCNIC();
        $validated['otp'] = mt_rand(1111, 9999);
        $validated['otp_expiry'] = date("Y-m-d H:i:s", strtotime('+1 hour'));
        $model = $this->model->create($validated);
        $model->assignRole($validated['role']);
        $this->meidaService->store($request->image_id, $model->id, "App\\Models\\User");
        $subject = '';
        $content = '';
        $createdUserContent = '';
        if ($validated['role'] == 'Manager') {
            $subject = "MyCheX Account Creation - Your Managerial Access!";
            $content = "<span> I am writing to inform you that you have been officially assigned the role of manager within our company. Your leadership and expertise will undoubtedly contribute to our company success. <br /><br /> Your managerial access on MyCheX will empower you to oversee team activities, assign tasks, and streamline communication efficiently. To get started, please use the following credentials: <br /><br /> <b>Email:</b> " . $model->email . "<br /> <b>Password:</b> " . $user_password . "<br /> <br />If you encounter any issues or have questions, feel free to reach out <a href='mailto:info@mychex.co.uk'>info@mychex.co.uk</a> <br /> <br /> We appreciate your leadership, and we believe MyCheX will further enhance our team's productivity.</span>";
            $createdUserContent = "Congratulations! You are now the official team manager. Please check your email for details.";
        } else {
            $subject = "Welcome to MyChex";
            $content = "<span> We are excited to inform you that a user account has been created for you on MyCheX, our designated platform for team collaboration, task management, and communication.<br /><br /> To get started, please use the provided credentials to log in. <br /><br /> <b>Email</b>: $model->email <br /> <br /> <b>Password</b> : $user_password <br /><br />MyCheX will be our central hub for organising tasks, sharing updates, and fostering smooth communication within our team.<br / ><br / >We are thrilled to have you on board and look forward to maximising our collaboration.</span>";
            $createdUserContent = "Welcome to MyCheX! Your account is ready. Check your email for login details.";
        }
        Notification::send($model, new GeneralNotification($model, 'User', 'create', $createdUserContent, $subject, $content));
        Notification::send(Auth::user(), new GeneralNotification($model, 'User', 'create', "A user account has been created on MyCheX. Please check your email for details and assist in guiding the new user through password setup.", $subject, $content));
        $this->generalService->sendEmailToUser($model, $subject, $content, 'https://portal.mychex.co.uk/reset-password?email=' . $model->email . '&otp=' . $model->otp);
        $this->generalService->sendEmail(auth()->user(), 'User Account Creation Confirmation', "I hope this email finds you well.<br /><br />We wanted to inform you that a new user account has been successfully created on MyChex. The details of the account are as follows:<br /> <b>Email:</b>" . $model->email . "<br /><b>Password:</b>" . $user_password . "<br /><br />If you have any further instructions or require assistance regarding this user account, please don't hesitate to reach out at <a href='info@mychex.co.uk'>info@mychex.co.uk </a>", 'https://portal.mychex.co.uk/reset-password?email=' . $model->email . '&otp=' . $model->otp);
        $this->generalService->sendOtherManagersNotification($model, auth()->user() . ' has created a new user on MyChex. Check your email for details');
        return $model;
    }

    public function update($request, $model)
    {
        $validated = $request->validated();
        $this->meidaService->update($request->image_id, $model->id, "App\\Models\\User");
        $validated['name'] = $validated['first_name'] . ' ' . $validated['last_name'];
        $allExceptCnic = collect($validated)->except(['cnic'])->all();
        $model->update($allExceptCnic);
        $model->syncRoles($validated['role']);
        $model->load('photo');
        $content =
            "<span> We wanted to inform you that your user account on MyCheX has been recently updated.<br /><br />The following details can now be modified by you:<br /> <b>Name:</b> " . $model->name . " <br /> <b>Role: </b>" . $validated['role'] . " <br /> If you have any questions about the updates or encounter any issues, please do not hesitate to reach out <a href=" . "info@mychex.co.uk" . ">info@mychex.co.uk</a> <br /><br /> We appreciate your cooperation.</span> ";
        $subject = 'User Account Update';
        Notification::send($model, new GeneralNotification($model, 'User', 'update', "Your MyCheX account just got an upgrade for a smoother and more efficient experience. Check it out now!", $subject, $content));
        Notification::send(Auth::user(), new GeneralNotification($model, 'User', 'update', $model->name . " has been updated. Manager, please check your email for details", $subject, $content));
        $this->generalService->sendOtherManagersNotification($model, $model->name . " has been updated user account");
        $this->generalService->sendEmail($model, "User Account Update", "I hope this email finds you well.<br /><br />We wanted to inform you that " . $model->name . " has been recently updated on MyChex. The following details can now be modified by you: <br /> <b>Name</b>" . $model->name . '<br /><b>Role:</b>' . $validated['role'] . "<br /><br />If you have any further instructions or require assistance regarding this user account, please don't hesitate to reach out at <a href=" . "info@mychex.co.uk" . ">info@mychex.co.uk</a><br /><br />Thank you for your attention to this matter.");
        $this->generalService->sendEmail(auth()->user(), $subject, $content);
        $this->generalService->sendOtherManagersEmail($subject, "I hope this email finds you well.<br />We wanted to inform you that <Manager Name> has been recently updated the " . $model->name . ".The following details can now be modified by you:<br > <b>Name:</b>" . $model->name . "<br /><b>Role:</b>" . $validated['role'] . "If you have any further instructions or require assistance regarding this user account, please don't hesitate to reach out at <a href=" . "info@mychex.co.uk" . ">info@mychex.co.uk</a><br /><br />Thank you for your attention to this matter.");

        return $model;
    }

    public function updatePassword($request, $model)
    {
        $password = $this->generateStrongPassword(10);
        $validated['password'] = Hash::make($password);
        $model->update($validated);
        $content = "<span>I am writing to inform you that the password for your MyCheX account has been updated for security purposes. <br /> <br /> To access your account, please use the following login credentials:<br /><br /> <b>Email:</b> " . $model->email . "<br /> <b> Passowrd: </b> $password  <br /><br /> If you have any questions or concerns about this password update, feel free to reach out <a href='mailto:info@mychex.co.uk'>info@mychex.co.uk</a> <br /><br /> Thank you for your understanding. </span>";
        $subject = "Password Update Information";
        Notification::send($model, new GeneralNotification($model, 'User', 'update', "Password Update: Your account password has been changed. For security, check your email for instructions.", $subject, $content));
        Notification::send(Auth::user(), new GeneralNotification($model, 'User', 'update', "You updated the " . $model->name . " user password", $subject, $content));
        $this->generalService->sendFCMNotification($model, $subject, 'Your MyCheX account password has been updated');
        $this->generalService->sendEmail($model, $subject, $content);
        return $model;
    }

    public function generateStrongPassword($length = 8)
    {
        $characters = '0123456789abcdABCDVWXYZ!@#$%^&*()-_';
        $password = '';
        $charactersLength = strlen($characters);
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $password;
    }

    // staff for Drop Dropdown
    public function staffList($request)
    {
        $roles = [];
        $where = [];
        if ($request['roles']) {
            $roles = $request['roles'];
        }

        $searchCols = ['name', 'email', 'first_name', 'last_name'];

        if (Auth::user()->roles->pluck('name')[0] == 'Super Admin') {
            array_push($where, [
                'key' => 'company_id',
                'operator' => '=',
                'value' => $request['company_id']
            ]);
        } else if (Auth::user()->roles->pluck('name')[0] == 'Manager') {
            array_push($where, [
                'key' => 'company_id',
                'operator' => '=',
                'value' => Auth::user()->company_id
            ]);
        }

        $data = User::select('id', 'name', 'first_name', 'last_name', 'name', 'status')->where('company_id', auth()->user()->company_id);

        if (count($roles)) {
            $data = $data->role($roles);
        }
        if ($request['status']) {
            $data = $data->where('status', $request['status']);
        }
        $data = $this->generalService->handleWhere($data, $where);
        $data = $this->generalService->handleSearch($request['searchText'], $data, $searchCols, '', []);

        if ($request['perpage'] == 'All') {
            return $this->generalService->handleAllData($request, $data);
        } else {
            return $this->generalService->handlePagination($request, $data);
        }
    }
}
