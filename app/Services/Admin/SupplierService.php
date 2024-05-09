<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Services\GeneralService;
use App\Services\MeidaService;
use App\Services\UserService;
use Illuminate\Support\Facades\Hash;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Class SupplierService
 * @package App\Services
 */
class SupplierService
{
    public function __construct(private GeneralService $generalService, private MeidaService $meidaService, private UserService $userService)
    {
    }

    public function index($request)
    {
        $searchCols = ['name', 'email', 'first_name', 'last_name', 'cnic', 'phone', 'address'];
        $relationalCols = [
            'roles' => ['name'],
        ];

        $data = User::with(['login_history', 'photo'])->role("Supplier");

        $data = $this->generalService->handleSearch($request['searchText'], $data, $searchCols, '', $relationalCols);

        if ($request['perpage'] == 'All') {
            return $this->generalService->handleAllData($request, $data);
        } else {
            return $this->generalService->handlePagination($request, $data);
        }
    }

    public function store($data)
    {
        $user_password = $this->userService->generateStrongPassword(10);
        $data['password'] = Hash::make($user_password);
        $data['name'] = $data['first_name'] . ' ' . $data['last_name'];
        $data['cnic'] = $this->userService->setCNIC();
        $user = User::create($data);
        $user->assignRole("Supplier");
        $this->meidaService->store($data['image_id'], $user["id"], get_class($user));
        $user = $user->load('photo');
        // $this->generalService->sendFCMNotification($user, "Supplier Created ", 'Welcome to Chex!. We are delighted to have you join our team.');
        $subject = "Welcome to MyCheX Supplier Portal";
        $content = "I am writing to inform you that a supplier account has been created for you on MyCheX. Your login credentials are as follows:<br /><br /> <b>Email:</b> " . $user->email . "<br /> <b>Temporary Password:</b> " . $user_password . "<br /><br /> Looking forward to your collaboration!</span>";
        // Notification::send($user, new GeneralNotification($user, 'User', 'create', "Welcome to Chex!. We are delighted to have you join our team.", $subject, $content));
        $this->generalService->sendEmail($user, $subject, $content);
        return $user;
    }


    public function update($data, $supplier)
    {
        $this->meidaService->update($data["image_id"], $supplier["id"], "App\\Models\\User");
        $data['name'] = $data['first_name'] . ' ' . $data['last_name'];
        $supplier->update($data);
        $supplier->load('photo');
        $subject = 'Subject';
        $content = 'Content';
        Notification::send($supplier, new GeneralNotification($supplier, 'User', 'update', "Your account information has been updated successfully.", $subject, $content));
        $this->generalService->sendEmail($supplier, $subject, $content);
        return $supplier;
    }
}
