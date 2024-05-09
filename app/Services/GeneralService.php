<?php

namespace App\Services;

use App\Jobs\SendEmailJob;
use Carbon\Carbon;
use App\Models\Task;
use App\Models\User;
use App\Models\Config;
use App\Models\Admin\Team;
use App\Models\Admin\Media;
use App\Models\PackagePlan;
use App\Models\QuestionRisk;
use App\Models\CompanyPackagePlan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use App\Services\SubscriptionService;
use App\Models\Admin\CompanyIpAddress;
use Edujugon\PushNotification\PushNotification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Notification;
use App\Notifications\GeneralNotification;

class GeneralService
{
    // private Request $request;
    public function __construct()
    {
    }

    // Start New Functions
    public function handleWhereIn($data, $whereIn)
    {
        if (count($whereIn)) {
            foreach ($whereIn as $key => $values) {
                $data = $data->whereIn($key, $values);
                if (in_array(null, $values)) {
                    $data = $data->orWhereNull($key);
                }
            }
            return $data;
        }
        return $data;
    }

    public function handleRelationalWhereIn($data, $whereIn)
    {
        foreach ($whereIn as $relation => $values) {
            foreach ($values as $key => $val) {
                $data = $data->orWhereHas($relation, function ($query) use ($key, $val) {
                    $query->whereIn($key, $val);
                });
            }
        }

        return $data;
    }

    public function handleWhere($data, $where)
    {
        if (count($where)) {
            foreach ($where as $param) {
                if (is_array($param)) {
                    $data = $data->where($param['key'], $param['operator'], $param['value']);
                }
            }
            return $data;
        }
        return $data;
    }

    public function handleRelationalWhere($data, $where)
    {
        foreach ($where as $relation => $conditions) {
            $data = $data->whereHas($relation, function ($query) use ($conditions) {
                $query->where(function ($q) use ($conditions) {
                    foreach ($conditions as $index => $condition) {
                        $q->where($condition['key'], $condition['operator'], $condition['value']);
                    }
                });
            });
        }

        return $data;
    }

    public function handleAllData($req, $data)
    {
        $orderBy = $req->orderBy ? $req->orderBy : 'id';
        $seq = $req->seq == 'true' ? 'desc' : 'asc';

        return $data->orderBy($orderBy, $seq)->get();
    }

    public function calculateTotalHours($checkinDatetime, $checkoutDatetime)
    {
        // Parse the datetime strings into Carbon instances
        $checkinDate = Carbon::parse($checkinDatetime);
        $checkoutDate = Carbon::parse($checkoutDatetime);

        // Calculate the time difference in hours
        $totalHours = $checkoutDate->diffInHours($checkinDate);

        return $totalHours;
    }

    public function handlePagination($req, $data)
    {
        $orderBy = $req->orderBy ? $req->orderBy : 'id';
        $seq = $req->seq == 'true' ? 'desc' : 'asc';
        $perpage = $req->perpage ? $req->perpage : 10;
        return $data->orderBy($orderBy, $seq)->paginate($perpage);
    }

    public function handleArrPagination($req, $data)
    {
        $orderBy = $req->orderBy ?? 'id';
        $seq = $req->seq == 'true' ? 'desc' : 'asc';
        $perpage = $req->perpage ?? 10;

        // Convert the array to a Laravel Collection
        $collection = new Collection($data);

        // Sort the collection based on the specified column and order
        $sorted = $collection->sortBy($orderBy, SORT_REGULAR, $seq == 'desc');

        // Paginate the sorted collection
        $paginator = new LengthAwarePaginator(
            $sorted->forPage($req->page ?? 1, $perpage),
            $collection->count(),
            $perpage,
            $req->page ?? 1
        );

        return $paginator;
    }

    public function handleSearch($searchText = '', $data, $cols = [], $statusCol = '', $children = [])
    {
        if (!$searchText) return $data;
        $searchQuery = strtolower($searchText);
        $search = "active" == $searchQuery ? '1' : ("inactive" == $searchQuery ? '0' : '');
        if ($searchQuery == 'inprogress') $search = 'in_progress';

        $query = $data->where(function ($q) use ($searchQuery, $cols, $statusCol, $search, $children) {
            // Search query in parent model with specified columns $cols
            if (count($cols)) {
                $q->where(function ($query) use ($searchQuery, $cols) {
                    foreach ($cols as $col) {
                        $query->orWhere($col, 'LIKE', "%{$searchQuery}%");
                    }
                });
            }

            // Search query in children model $children->key with specified columns $children->values
            if (count($children)) {
                foreach ($children as $child => $childCols) {
                    $q->orWhereHas($child, function ($query) use ($searchQuery, $childCols) {
                        foreach ($childCols as $col) {
                            $query->where($col, 'LIKE', "%{$searchQuery}%");
                        }
                    });
                }
            }

            // Search query in parent model for active which is 1 and inactive which is 0
            if ($statusCol && $search !== '') {
                $q->orWhere($statusCol, 'LIKE', "%{$search}%");
            }
        });

        return $query;
    }

    public function sendFCMNotification($users, $title, $body, $sound = 'default')
    {
        $tokens = $users->pluck('device_token')->toArray();
        $push = new PushNotification('fcm');
        $push->setMessage([
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => $sound,
            ],
            'data' => [
                'extraPayLoad1' => 'value1',
                'extraPayLoad2' => 'value2'
            ]
        ]);
        $push->setApiKey(config('app.firebase_server_key'));
        $push->setDevicesToken($tokens)->send();
        return true;
    }

    public function sendEmail($model, $subject, $content, $url = null)
    {
        $email_data = [
            "name"          =>  $model->name ?  $model->name : $model->title,
            "email"         =>  $model->email,
            "subject"       =>  $subject,
            "body"          =>  $content,
            'button_url'    =>  $url  == null ?  'https://portal.mychex.co.uk/' : $url,
            'button_text'   =>  "Login",
            'logo'          => (Auth::user()->company != null) ? Auth::user()->company?->photo?->path : config('app.aws_url') . "public/logo.png",
        ];

        if (!is_null(auth()->user()->company_id) && auth()->user()->company->allow_email == 1 && $model->status == 1) {
            dispatch(new SendEmailJob($email_data));
        } else if (Auth::user()->roles->pluck('name')[0] == 'Super Admin') {
            dispatch(new SendEmailJob($email_data));
        }
    }

    public function sendEmailToUser($model, $subject, $content, $url = null)
    {
        $email_data = [
            "name"          =>  $model->name ?  $model->name : $model->title,
            "email"         =>  $model->email,
            "subject"       =>  $subject,
            "body"          =>  $content,
            'button_url'    =>  $url  == null ?  'https://portal.mychex.co.uk/' : $url,
            'button_text'   =>  "Login",
            'logo'          => (Auth::user()->company != null) ? Auth::user()->company?->photo?->path : config('app.aws_url') . "public/logo.png",
        ];

        dispatch(new SendEmailJob($email_data));
    }

    public function sendOtherManagersNotification($module, $content, $subj = null, $emailContent = null)
    {
        $managers = User::where('id', '!=', Auth::id())->role('Manager')->where('company_id', Auth::user()->company_id)->get();
        Notification::send($managers, new GeneralNotification($module, 'Team', 'POST', auth()->user()->name ."create a new user account.", $subj, $emailContent));
    }

    public function sendOtherManagersEmail($subject, $content)
    {
        $managers = User::where(
            'id',
            '!=',
            Auth::id()
        )->role('Manager')->where('company_id', Auth::user()->company_id)->get();
        foreach ($managers as $manager) {
            $this->sendEmail($manager, $subject, $content);
        }
    }


    // Notification Emails

    public function sendManagerEmail($subject, $content)
    {
        $this->sendEmail(Auth::user(), $subject, $content);
    }

    public function sendStaffEmail($users, $subject, $content)
    {
        foreach ($users as $user) {
            $this->sendEmail($user, $subject, $content);
        }
    }

    public function getMoreStaff($users)
    {
        if (count($users) == 1) {
            $content = $users[0]->name;
        }
        if (count($users) == 2) {
            $content = $users[0]->name . ", " . $users[1]->name;
        }
        if (
            count($users) > 2
        ) {
            $content = $users[0]->name . ", " . $users[1]->name . " and more";
        }

        return $content;
    }

    public function companyEmail($model, $subject, $content, $url = null)
    {
        $email_data = [
            "name"          =>  $model->title,
            "email"         =>  $model->email,
            "subject"       =>  $subject,
            "body"          =>  $content,
            'button_url'    =>  $url  == null ?  'https://portal.mychex.co.uk/' : $url,
            'button_text'   =>  "Login",
            'logo'          => ($model->photo) ? $model->photo->path : config('app.aws_url') . 'public/logo.png',
        ];
        dispatch(new SendEmailJob($email_data));
    }
}
