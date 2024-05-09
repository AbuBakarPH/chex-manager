<?php

namespace App\Services;

use App\Enums\VisitType;
use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\CqcVisit;
use App\Jobs\SendEmailJob;
use App\Services\GeneralService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Class RiskService
 * @package App\Services
 */
class CqcVisitService
{
    public function __construct(private GeneralService $generalService)
    {
    }

    public function index($request)
    {
        $searchCols = ['title'];
        $relationalCols = [];
        $data = CqcVisit::where('company_id', Auth::user()->company_id)->latest();
        $data = $this->generalService->handleSearch($request['searchText'], $data, $searchCols, '', $relationalCols);

        if ($request['perpage'] == 'all') {
            return $this->generalService->handleAllData($request, $data);
        } else {
            $data = $this->generalService->handlePagination($request, $data);
            $future = CqcVisit::where('company_id', Auth::user()->company_id)
                ->whereDate('visit_date', '>=', date('Y-m-d'))
                ->first();
            return ['listing' => $data, 'coming' => $future];
        }
    }

    public function store($request)
    {
        $companyId = auth()->user()->company_id;
        $request['company_id'] = $companyId;
        $request['created_by'] = auth()->id();
        $cqcVisit = CqcVisit::create($request->all());
        $companyUsers = User::whereCompanyId($companyId)->setStatus(1)->get();
        $content = 'I want to bring your attention to an important upcoming CQC visit: <br /><br /><b>Title:</b> ' . $cqcVisit->title . '<br /><br /><b>Date:</b> ' .   Carbon::parse($cqcVisit->visit_date)->format('d-m-Y')  . '<br /><br /><b>Time:</b> ' . Carbon::parse($cqcVisit->visit_date)->format('h:i A') . '<br /><br />Kindly review your assigned tasks on our platform and ensure their completion before the scheduled visit. If you have any questions or face challenges, do not hesitate to reach out <a href="mailto:info@mychex.co.uk">info@mychex.co.uk</a> <br /><br />Your cooperation is crucial in ensuring the success of this visit. <br /><br />Thank you for your prompt attention to this matter. ';
        foreach ($companyUsers as $user) {
            // $this->generalService->sendEmail($user, $cqcVisit->title, $content);
            $this->generalService->sendEmail($user, 'Urgent: CQC Visit - Preparations Required', $content);
            if ($user->id != auth()->user()->id) {
                if ($user->getRoleNames()[0] == 'Manager') {
                    Notification::send($user, new GeneralNotification($cqcVisit, 'CqcVisit', 'POST', Auth::user()->name . ' has created a new CQC visit scheduled for ' . Carbon::parse($cqcVisit->visit_date)->format('d-m-Y') . ' at ' . Carbon::parse($cqcVisit->visit_date)->format('h:i A') . '. Please check your email for more details.', 'Urgent: CQC Visit - Preparations Required', $content));
                } else {
                    Notification::send($user, new GeneralNotification($cqcVisit, 'CqcVisit', 'POST', 'Important Notice: CQC Visit scheduled for ' . Carbon::parse($cqcVisit->visit_date)->format('d-m-Y') . ' at ' . Carbon::parse($cqcVisit->visit_date)->format('h:i A') . '. Check your email for details and ensure readiness.', 'Urgent: CQC Visit - Preparations Required', $content));
                }
            }
        }
        Notification::send(Auth::user(), new GeneralNotification($cqcVisit, 'CqcVisit', 'POST', 'Urgent: CQC Visit scheduled for ' . Carbon::parse($cqcVisit->visit_date)->format('d-m-Y') . ' at ' . Carbon::parse($cqcVisit->visit_date)->format('h:i A') . '. Check your email for details. Ensure team readiness and contact our support team for any questions.', 'Urgent: CQC Visit - Preparations Required', $content));
        $this->generalService->sendFCMNotification($companyUsers, "Urgent: CQC Visit - Preparations Required", 'Your attention required for upcoming CQC visit.');
    }

    public function update($request, $cqcVisit)
    {
        DB::beginTransaction();

        try {
            $companyId = auth()->user()->company_id;
            $res = $cqcVisit->update($request->validated());
            $companyUsers = User::whereNot('id', auth()->id())->setStatus(1)->whereCompanyId($companyId)->get();

            $content = "I wanted to inform you about an update regarding the upcoming CQC (Care Quality Commission) visit. <br /><br />The visit details have been revised as follows: <br /><br /><b>Title:</b> " . $cqcVisit->title . " <br /><br /><b>Date:</b> " . explode(' ', $cqcVisit->visit_date)[0] . " <br /><br /><b>Time:</b> " . explode(' ', $cqcVisit->visit_date)[1] . " <br /><br />Please take note of these changes and ensure that you adjust your schedules accordingly. Your cooperation in accommodating these changes is greatly appreciated. <br /><br />Thank you for your attention to this matter.";
            foreach ($companyUsers as $user) {
                // $this->sendEmail($user, $cqcVisit->title, $content);
                // $this->sendEmail($user, "Update on CQC Visit", $content);
                $this->generalService->sendEmail($user, 'Update on CQC Visit', $content);

                if ($user->id != auth()->user()->id) {
                    if ($user->getRoleNames()[0] == 'Manager') {
                        Notification::send($user, new GeneralNotification($cqcVisit, 'CqcVisit', 'POST', Auth::user()->name . ' has updated the details for the upcoming CQC visit. Please review the changes in your email for further information.', 'Urgent: CQC Visit - Preparations Required', $content));
                    } else {
                        Notification::send($user, new GeneralNotification($cqcVisit, 'CqcVisit', 'POST', 'The details for the upcoming CQC visit have been successfully updated. Please review the changes in your email for further information.', 'Urgent: CQC Visit - Preparations Required', $content));
                    }
                }
            }
            Notification::send(Auth::user(), new GeneralNotification($cqcVisit, 'CqcVisit', 'POST', 'You have successfully updated the details for the upcoming CQC  visit.', 'Urgent: CQC Visit - Preparations Required', $content));
            $this->generalService->sendFCMNotification($companyUsers, "Update on CQC Visit", 'Gentle reminder regarding the upcoming CQC visit.');
            DB::commit();
            return $res;
        } catch (Exception $e) {
            info($e->getMessage());
            DB::rollback();
            return $e->getMessage();
        }
    }

    // private function sendEmail($user, $title, $message)
    // {
    //     $emailData = [
    //         "name"          =>  $user->first_name,
    //         "email"         =>  $user->email,
    //         "subject"       =>  $title,
    //         // "subject"       =>  "CQC Visit Scheduled",
    //         "body"          =>  $message,
    //         'button_url'    =>  'http://localhost:9000/',
    //         'button_text'   =>  "Login",
    //         'logo'          => (Auth::user()->company->photo != null) ? Auth::user()->company->photo->path : config('app.aws_url') . "public/logo.png",
    //     ];

    //     return SendEmailJob::dispatch($emailData);
    // }
}
