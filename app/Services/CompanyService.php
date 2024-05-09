<?php

namespace App\Services;

use App\Jobs\SendEmailJob;
use App\Models\Admin\Company;
use App\Models\Admin\Media;
use App\Models\User;
use App\Services\Interfaces\CompanyServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use App\Notifications\GeneralNotification;
use App\Services\GeneralService;
use App\Services\UserService;
use Auth;

/**
 * Class CompanyService
 * @package App\Services
 */
class CompanyService implements CompanyServiceInterface
{
    public function __construct(
        private Company $model,
        private MeidaService $meidaService,
        private GeneralService $generalService,
        private UserService $userService
    ) {
        $this->generalService = $generalService;
    }

    public function index($request)
    {
        $data = Company::with(['users', 'photo', 'ip_address']);
        $data = $this->generalService->handleSearch($request['searchText'], $data, ['title', 'email', 'phone', 'shifts']);
        if ($request['perpage'] == 'all' || $request['perpage'] == 'All') {
            return $this->generalService->handleAllData($request, $data);
        } else {
            return $this->generalService->handlePagination($request, $data);
        }
    }

    public function store($request)
    {
        $validatedData = $request->validated();

        $model = new Company([
            'title'     => $validatedData['title'],
            'shifts'    => $validatedData['shifts'],
            'address'   => $validatedData['company_address'],
            'email'     => $validatedData['company_email'],
            'phone'     => $validatedData['company_phone'],
            'start_time'     => $validatedData['start_time'],
            'end_time'     => $validatedData['end_time'],
        ]);

        $model->save();
        $company_content = 'We hope this email finds you well.<br /><br />We wanted to inform you that a new company has been successfully created on MyChex. Below are the details:<br /><b>Company Title: </b> ' . $model->title . ' <br /><b>Shift: </b> ' . str_replace("_", " ", $model->shifts)  . '<br /><b>Company Address: </b> ' . $model->address . ' <br /><b>Phone No: </b> ' . $model->phone . ' <br /><br />Please review the information to ensure accuracy. If there are any additional details or updates needed, please let us know at <a href="info@mychex.co.uk">info@mychex.co.uk </a><br /><br />Thank you for your attention to this matter.';
        $this->meidaService->store($request->logo_id, $model->id, "App\\Models\\Admin\\Company");
        $this->generalService->companyEmail($model, 'Welcome to MyChex: ' . $model->title, $company_content);
        $user_password = $this->userService->generateStrongPassword(10);
        $user = new User([
            'first_name'    => $validatedData['first_name'],
            'last_name'     => $validatedData['last_name'],
            'cnic'          => $this->userService->setCNIC(),
            'email'         => $validatedData['email'],
            'phone'         => $validatedData['phone'],
            'address'       => $validatedData['address'],
            'password'      => Hash::make($user_password),
            'company_id'    => $model->id,
            'name'          => $validatedData['first_name'] . " " . $validatedData['last_name'],
        ]);
        $user->save();
        $user->assignRole('Manager');
        $this->meidaService->store($request->image_id, $user->id, "App\\Models\\User");
        $content = "<span> I am writing to inform you that you have been officially assigned the role of manager within our company. Your leadership and expertise will undoubtedly contribute to our team's success. <br /><br /> Your managerial access on MyCheX will empower you to oversee team activities, assign tasks, and streamline communication efficiently. To get started, please use the following credentials: <br /><br /> <b>Email:</b> " . $user->email . "<br /> <b>Password:</b> " . $user_password . "<br /> <br />If you encounter any issues or have questions, feel free to reach out <a href='mailto:info@mychex.co.uk'>info@mychex.co.uk</a> <br /> <br /> We appreciate your leadership, and we believe MyCheX will further enhance our team's productivity.</span>";
        $subject = 'MyCheX Account Creation - Your Managerial Access!';
        Notification::send($user, new GeneralNotification($model, 'Company', 'POST', "Congratulations! You are now the official team manager. Please check your email for details.", $subject, $content));
        $this->generalService->sendEmail($user, $subject, $content);
        $model["user"] = $user;
        return $model;
    }

    public function update($request, $model)
    {
        $validatedData    = $request->validated();

        $model->title     = $validatedData['title'];
        $model->shifts    = $validatedData['shifts'];
        $model->address   = $validatedData['company_address'];
        $model->phone     = $validatedData['company_phone'];
        $model->start_time     = $validatedData['start_time'];
        $model->end_time     = $validatedData['end_time'];

        if ($model->logo) {
            Storage::disk('public')->delete('company-logos/' . $model->logo);
        }
        $this->meidaService->update($request->logo_id, $model->id, "App\\Models\\Admin\\Company");
        $model->save();

        $user = User::find($request->user_id);
        $user->first_name   = $validatedData['first_name'];
        $user->last_name    = $validatedData['last_name'];
        $user->phone        = $validatedData['phone'];
        $user->address      = $validatedData['address'];
        $user->name         = $validatedData['first_name'];
        $user->save();
        $this->meidaService->update($request->image_id, $request->user_id, "App\\Models\\User");
        return $model->refresh();
    }
}
