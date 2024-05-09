<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Facades\Auth;




class ConfigNotificationService
{
    public function __construct(private GeneralService $generalService)
    {
        $this->generalService = $generalService;
    }

    public function sendConfigUpdateEmail($data, $config)
    {
        $config_user = $config->staffPivot->pluck('user_id')->toArray();
        $new_attach_user = array_diff($data['staff_id'], $config_user);

        if (count($data['staff_id']) > count($config_user)) {
            dd("Atatach");
            $this->addingUser($data, $config, $new_attach_user);
        }

        if (count($data['staff_id']) < count($config_user)) {
            $remove_user = array_diff($config_user, $data['staff_id']);
            $this->removingUser($data, $config, $remove_user);
        }
    }

    private function addingUser($data, $config, $new_attach_user)
    {
        $users = User::whereIn('id', $new_attach_user)->get();
        $approvers = User::whereIn('id', $data['approval'])->get();
        $staffSubj = 'Configuration Update - ' . $config->task->name;
        $subj = 'New User Added to Checklist: ' . $config->task->name;
        $email = '<a href="mailto:info@mychex.co.uk">info@mychex.co.uk</a>';
        $more_staff = $this->generalService->getMoreStaff($users);
        $staffContent = 'We hope this email finds you well.<br /><br />We wanted to inform you that you have been assigned to the checklist titled <b>' . $config->task->name . '</b> with a due date of ' . $config->due_date . '.<br />Your participation is vital in ensuring the completion of this checklist within the specified timeframe.<br />If you have any questions or need assistance with the checklist, please feel free to reach out at ' . $email;
        $managerContent = "We hope you're doing well.<br /><br />We wanted to inform you that a new user, " . $more_staff . ", has been successfully added to the checklist <b>" . $config->task->name . "</b>. The due date for this checklist is " . $config->due_date . ".<br />Please ensure that " . $more_staff . " is briefed on the checklist requirements and that they have the necessary resources to complete their tasks.<br /><br />Thank you for your attention to this matter.";
        $otherManagerContent = 'We hope this email finds you well. <br /><br />We wanted to inform you that ' . auth()->user()->name . ' has added a new user to the checklist. Below are the details:<br />Checklist Title: <b> ' . $config->task->name . '</b> <br /> Added User: ' . $more_staff . '<br />Due Date: ' . $config->due_date . '<br />If you have any questions or need further assistance, please dont hesitate to reach out at ' . $email . '.<br /><br />Thank you for your attention to this matter.';
        $this->generalService->sendStaffEmail($users, $staffSubj, $staffContent);
        $this->generalService->sendStaffEmail($approvers, $subj, $otherManagerContent); // send Email To Approvers
        $this->generalService->sendOtherManagersEmail($subj, $otherManagerContent);
        $this->generalService->sendManagerEmail($subj, $managerContent);
        $this->generalService->sendFCMNotification($users, $staffSubj, "You're in! You've been added to " . $config->task->name);
    }

    private function removingUser($data, $config, $new_attach_user)
    {
        $users = User::whereIn('id', $new_attach_user)->get();
        $approvers = User::whereIn('id', $data['approval'])->get();
        $subj = 'Update: User Removed from Checklist';
        $email = '<a href="mailto:info@mychex.co.uk">info@mychex.co.uk</a>';
        $more_staff = $this->generalService->getMoreStaff($users);
        $staffContent = "We hope this email finds you well.<br /><br />We wanted to inform you that you have been removed from the checklist <b>" . $config->task->name . "</b>.<br /><br /> If you have any questions or need further clarification about this change, please don't hesitate to reach out at  " . $email . '<br />';
        $managerContent = "We hope this email finds you well.<br /><br />We wanted to inform you that " . $more_staff . ", has been removed from the checklist <b>" . $config->task->name . "</b>. If you have any questions or need further assistance regarding this change, please don't hesitate to reach out at " . $email;
        $otherManagerContent = "We hope this email finds you well. <br /><br />We wanted to inform you that " . auth()->user()->name . " has removed a user from the checklist. Below are the details: <br /><b>Checklist Title: </b> " . $config->task->name . " <br /> Removed User: " . $more_staff . " <br />If you have any further instructions or require assistance regarding this change, please don't hesitate to reach out at " . $email . '<br /><br />Thank you for your attention to this matter.';
        $this->generalService->sendStaffEmail($users, $subj, $staffContent);
        $this->generalService->sendStaffEmail($approvers, $subj, $otherManagerContent); // send Email To Approvers
        $this->generalService->sendOtherManagersEmail($subj, $otherManagerContent);
        $this->generalService->sendManagerEmail($subj, $managerContent);
        $this->generalService->sendFCMNotification($users, $subj, " Update: You've been removed from  " . $config->task->name);
    }


    // private function sendOtherManagersNotificaiton($module, $content, $subj = null, $emailContent = null)
    // {
    //     $managers = User::where('id', '!=', Auth::id())->role('Manager')->where('company_id', Auth::user()->company_id)->get();
    //     Notification::send($managers, new GeneralNotification($module, 'Team', 'POST', $content, $subj, $emailContent));
    // }
}
