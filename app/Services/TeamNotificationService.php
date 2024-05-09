<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Facades\Auth;



class TeamNotificationService
{

    public function __construct(private GeneralService $generalService)
    {
        $this->generalService = $generalService;
    }

    public function forCreatingTeam($team)
    {

        $stafSubject = 'New Team Formation on MyCheX';
        $managerSubj = 'New Team Creation Confirmation';
        $otherManagerSubj = 'New Team Creation Confirmation';

        $content = "<span>We hope this email finds you well. <br /><br /> We are pleased to inform you that a new team has been created on MyChex, and you have been successfully added as a member.<br /><br /> <b> Team Name : </b> " . $team->title . "<br /> <b> Team Members: </b> ";
        $userNotificationContent = "You have been added to the " . $team->title . "! Start collaborating with ";
        if (isset($team->users)) {
            if (count($team->users) == 1) {
                $content .= $team->users[0]->name . " <br />";
                $userNotificationContent .= $team->users[0]->name . " team member now.";
                $teamAssignee = $team->users[0]->name . " <br /><br />";
            }

            if (count($team->users) == 2) {
                $content .= $team->users[0]->name . ", " . $team->users[1]->name . " <br />";
                $userNotificationContent .= $team->users[0]->name . ", " . $team->users[1]->name . " team members now.";
                $teamAssignee = $team->users[0]->name . ", " . $team->users[1]->name . " <br /><br />";
            }

            if (count($team->users) > 2) {
                $content .= $team->users[0]->name . ", " . $team->users[1]->name . " and more <br />";
                $userNotificationContent .= $team->users[0]->name . ", " . $team->users[1]->name . " and more team members now.";
                $teamAssignee = $team->users[0]->name . ", " . $team->users[1]->name . " and more <br /><br />";
            }
        }
        $content .= "<br /> If you have any questions or need assistance, please contact us at <a href='mailto:info@mychex.co.uk'>info@mychex.co.uk.</a><br /><br /> Welcome aboard and looking forward to a productive collaboration!<br /><span>";

        Notification::send($team->users, new GeneralNotification($team, 'Team', 'POST', $userNotificationContent, $stafSubject, $content));
        $this->sendFCMNotificaiton($team, 'New Team Formation on MyChex', "You've been added to a new team on MyChex!");
        Notification::send(Auth::user(), new GeneralNotification($team, 'Team', 'POST', $team->title . " is live! Head to your email for all the details.", $stafSubject, $content));
        $managerContent = 'We hope this email finds you well.<br /><br />We wanted to inform you that a new team has been successfully created on MyChex. Below are the details of the team: <br /><br /><b>Team Name: </b> ' . $team->title . '<br /> <b>Team Members: </b>' . $teamAssignee . 'If you have any further instructions or require assistance regarding this new team, please feel free to reach out at <a href="mailto:info@mychex.co.uk">info@mychex.co.uk</a><br /><br /> Thank you for your attention to this matter. ';
        $otherManagerContent = 'We hope this email finds you well.<br /> We wanted to inform you that <b>' . Auth::user()->name . '</b> has created a new team on MyChex. Below are the details of the team: <br /><br /> <b>Team Name:</b> ' . $team->title . '<br /> <b>Team Members:</b> ' . $teamAssignee . ' If you have any further instructions or require assistance regarding this new team, please feel free to reach out at <a href="mailto : info@mychex.co.uk">info@mychex.co.uk</a>. <br /><br /> Thank you for your attention to this matter.';
        $this->sendStaffEmail($team->users, $stafSubject, $content);
        // left is to send email to removed staff
        $this->sendManagerEmail($managerSubj, $managerContent);
        $this->sendOtherManagersEmail($otherManagerSubj, $otherManagerContent);
        $this->sendOtherManagersNotificaiton($team, '<b> ' . Auth::user()->name . '</b> has been created a new team ' . $team->title . '. Check email for details.');
    }


    public function forUpdatingTeam($team, $validated)
    {

        $old_team_member = $team->users;
        $new_team_member = $validated['user_id'];

        if (($team->title != $validated['title']) && (count($old_team_member) ==  count($new_team_member)) && ($team->is_active == $validated['is_active'])) {
            $this->teamTitleUpdate($team, $validated);
        } else if ((count($old_team_member) < count($new_team_member)) && ($team->title == $validated['title']) && ($team->is_active == $validated['is_active'])) {
            $this->newTeamMemberAttach($team, $validated);
        } else if ((count($old_team_member) > count($new_team_member)) && ($team->title == $validated['title']) && ($team->is_active == $validated['is_active'])) {
            $this->removeTeamMember($team, $validated);
        } else if (($validated['is_active'] == 1) && ($team->title == $validated['title']) && (count($old_team_member) ==  count($new_team_member))) {
            $this->teamActiveStatus($team, $validated);
        } else if (($validated['is_active'] == 0) && ($team->title == $validated['title']) && (count($old_team_member) ==  count($new_team_member))) {
            $this->teamInactiveStatus($team, $validated);
        } else {
            $this->teamUpdate($team, $validated);
        }
    }

    private function teamActiveStatus($team, $validated)
    {
        $subj = 'Team Status Activation: Action Required';
        $email = '<a href="mailto:info@mychex.co.uk">info@mychex.co.uk</a>';
        $staffContent = "We hope this message finds you well.<br /><br />  We wanted to inform you that <b>" . auth()->user()->name . "</b> has <b> activated </b> the status of <b>" . $team->title . "</b>. This indicates that their team is now officially <b> active </b> and ready to proceed with their tasks and responsibilities. <br /> If there are any actions required from your end or if you have any questions regarding this status <b> activation </b>, please feel free to reach out to us at " . $email . '<br /><br /> Thank you for your attention to this matter.';
        $managerContent = "We hope you're doing well.<br /><br /> We wanted to inform you that the status of <b>" . $team->title . "</b> has been successfully <b> activated </b>. Please ensure that all team members are aware of this status change and are informed about any necessary adjustments to their activities. <br /> If there are any specific reasons for this <b> activation </b> or if you require further assistance in managing this situation, please reach out at " . $email . "<br /><br />Thank you for your attention to this matter.";
        $otherManagerContent = "We hope this email finds you well.<br /><br />We wanted to inform you that <b>" . auth()->user()->name . "</b> has <b> activated </b> the status of <b>" . $team->title . "</b>. This means that their team is currently <b> active </b>, and ongoing tasks or collaborations may be impacted.If there are any actions required from your end or if you have any questions regarding this <b> activation </b>, please feel free to reach out at " . $email . "<br /><br />Thank you for your attention to this matter.";
        $this->sendStaffEmail($team->users, $subj, $staffContent);
        $this->sendManagerEmail($subj, $managerContent);
        $this->sendOtherManagersEmail($subj, $otherManagerContent);

        Notification::send($team->users, new GeneralNotification($team, 'Team', 'Update', "Need your attention! " . $team->title . " status has been activated let's continue to work together effectively. ", '', ''));
        Notification::send(auth()->user(), new GeneralNotification($team, 'Team', 'Update', $team->title . " status has been activated. Manager, please check your email for details.", '', ''));
        $this->sendOtherManagersNotificaiton($team,  auth()->user()->name . " has activated status of team. Check your email for further details. ");
        $this->sendFCMNotificaiton($team, $subj, $team->title . " status has been activated.");
    }

    private function teamInactiveStatus($team, $validated)
    {
        $subj = 'Team Status Inactivation: Important Notice';
        $email = '<a href="mailto:info@mychex.co.uk">info@mychex.co.uk</a>';
        $staffContent = "We wanted to inform you that the status of " . $team->title . " has been deactivated. Please take note of this status change and refrain from further action until further notice.<br /> If you have any urgent concerns or questions regarding this inactivation, please do not hesitate to reach out at " . $email . "<br /><br />Thank you for your attention to this matter.";
        $managerContent = "We hope you're doing well.<br /><br /> We wanted to inform you that the status of " . $team->title . " has been successfully inactivated. Please ensure that all team members are aware of this status change and are informed about any necessary adjustments to their activities. <br /><br />If there are any specific reasons for this inactivation or if you require further assistance in managing this situation, please reach out at " . $email . 'Thank you for your attention to this matter.';
        $otherManagerContent = "We hope this email finds you well.<br /><br />We wanted to inform you that [Manager's Name] has deactivated the status of [team name]. This means that their team is currently inactive, and ongoing tasks or collaborations may be impacted. If there are any actions required from your end or if you have any questions regarding this inactivation, please feel free to reach out at " . $email . 'Thank you for your attention to this matter.';
        $this->sendStaffEmail($team->users, $subj, $staffContent);
        $this->sendManagerEmail($subj, $managerContent);
        $this->sendOtherManagersEmail($subj, $otherManagerContent);

        Notification::send($team->users, new GeneralNotification($team, 'Team', 'Update', "Need your attention! " . $team->title . " status has been deactivated; check you email for details. ", '', ''));
        Notification::send(auth()->user(), new GeneralNotification($team, 'Team', 'Update', $team->title . " status has been deactivated. Manager, please check your email for details.", '', ''));
        $this->sendOtherManagersNotificaiton($team,  auth()->user() . " has deactivated status of team. Check your email for further details. ");
        $this->sendFCMNotificaiton($team, $subj, $team->title . " status has been deactivated.");
    }

    private function newTeamMemberAttach($team, $validated)
    {
        $team_user = $team->users->pluck('id')->toArray();
        $newly_attach = array_diff($validated['user_id'], $team_user);
        $removed_staff = array_diff($team_user, $validated['user_id']);
        $attached_user = User::whereIn('id', $newly_attach)->get();
        $romved_user = User::whereIn('id', $removed_staff)->get();
        $more_users = $this->getMoreStaff($attached_user);
        $romved_user = [];
        $removed_members = '';
        if (count($romved_user) > 0) {
            $removed_users = $this->getMoreStaff($romved_user);
        }
        if (count($romved_user) > 0) {
            $subj = 'Important Update: Adding/Removed Team Member';
            $removed_members = '<br /><b>Removed Members :</b>' . $removed_users;
        } else {
            $subj = 'Important Update: Adding Team Member';
        }
        $email = '<a href="mailto:info@mychex.co.uk">info@mychex.co.uk</a>';
        $staffContent = 'We hope this message finds you well.<br /><br /> We wanted to inform you about recent changes to the <b>' . $team->title . '</b>. There has been an update in team composition: <br /><br /><b>Added Members:</b> ' . $more_users . ' <br />If you encounter any difficulties or have any questions about the updates, please do not hesitate to reach out <a href="mailto:info@mychex.co.uk">info@mychex.co.uk</a><br /> <br /> Thank you for your attention to this matter.';
        $managerContent = "We hope you're doing well.<br /><br /> We wanted to inform you that a new member has been added to your team on MyChex. Below are the details: <br /><br /> <b>Team:</b>" . $team->title . "<br /><b>New Members:</b> " . $more_users . "<br /> " .  (count($romved_user) > 0) ? $removed_members : '' . " If you have any further instructions or require assistance regarding this change, please don't hesitate to reach out at " . $email . " <br /><br /> Thank you for your attention to this matter.";
        $otherManagerContent = 'We hope this email finds you well.<br /><br />We wanted to inform you that ' . auth()->user()->name . ' has added a new member to their team. Below are the details:<br /><br /><b>Team:</b> ' . $team->title . '<br /><b>New Members:</b> ' . $more_users . "<br /> " .  (count($romved_user) > 0) ? $removed_members : '' . "<br />If you have any further instructions or require assistance regarding this change, please don't hesitate to reach out at " . $email . "<br /><br />Thank you for your attention to this matter.";
        $this->sendStaffEmail($attached_user, $subj, $staffContent);
        $this->sendManagerEmail($subj, $managerContent);
        $this->sendOtherManagersEmail($subj, $otherManagerContent);

        Notification::send($team->users, new GeneralNotification($team, 'Team', 'Update', 'Team Update: ' . $team->title . '. has changes! ' . $more_users . ' added to the team. Stay in the loop for more details in your email.', '', ''));
        Notification::send(auth()->user(), new GeneralNotification($team, 'Team', 'Update', $team->title . " has changes! " . $more_users . " added to the team. Manager, please check your email. ", '', ''));
        $this->sendOtherManagersNotificaiton($team,  auth()->user()->name . ' has added new member to the ' . $team->title . '. Manager, please check your email. ');
        $this->sendFCMNotificaiton($team, $subj, "An important update regarding " . $team->title . " awaits you!");
    }

    public function removeTeamMember($team, $validated)
    {
        $team_user = $team->users->pluck('id')->toArray();
        $removed_user = array_diff($team_user, $validated['user_id']);
        $attached_user = User::whereIn('id', $removed_user)->get();
        $more_users = $this->getMoreStaff($attached_user);
        $subj = 'Removing Team Member';
        $email = '<a href="mailto:info@mychex.co.uk">info@mychex.co.uk</a>';
        $staffContent = "We hope this message finds you well. <br /><br />We wanted to inform you about recent changes to the <b>" . $team->title . "</b>. There has been an update in team composition:<br /><b>Removed Member</b>: " . $more_users . "<br />If you encounter any difficulties or have any questions about the updates, please do not hesitate to reach out " . $email . ". <br /><br />Thank you for your attention to this matter.";
        $managerContent = "We hope you're doing well.<br /><br /> We wanted to inform you that a new member has been added to your team on MyChex. Below are the details:<br /><br /> <b>Team:</b>" . $team->title . "<br /><b>Removed Members:</b> " . $more_users . "<br /><br /> If you have any further instructions or require assistance regarding this change, please don't hesitate to reach out at " . $email . " <br /><br /> Thank you for your attention to this matter.";
        $otherManagerContent = 'We hope this email finds you well.<br /><br />We wanted to inform you that ' . auth()->user()->name . ' has removed a member to their team. Below are the details:<br /><br /><b>Team:</b> ' . $team->title . '<br /><b>Removed Members:</b> ' . $more_users . "<br /><br />If you have any further instructions or require assistance regarding this change, please don't hesitate to reach out at " . $email . "<br /><br />Thank you for your attention to this matter.";
        $this->sendStaffEmail($attached_user, $subj, $staffContent);
        $this->sendManagerEmail($subj, $managerContent);
        $this->sendOtherManagersEmail($subj, $otherManagerContent);

        Notification::send($team->users, new GeneralNotification($team, 'Team', 'Update', $team->title . " has changes! " . $more_users . " removed from the team. Manager, please check your email. ", '', ''));
        Notification::send(auth()->user(), new GeneralNotification($team, 'Team', 'Update', $team->title . " has changes! " . $more_users . " removed from the team. Manager, please check your email. ", '', ''));
        $this->sendOtherManagersNotificaiton($team,  auth()->user() . " has removed member from the " . $team->title . ". Manager, please check your email. ");
        $this->sendFCMNotificaiton($team, $subj, "Your team title on MyChex has been changed to " . $team->title . ". Tap to view details. ");
    }

    private function teamTitleUpdate($team, $validated)
    {
        $subj = 'Team Title Change Notification';
        $staffContent = 'We hope this email finds you well.<br /><br />We wanted to inform you that the team title on MyChex has been updated. 
                        The new team title is: <b>' . $validated['title'] . '</b>. <br /><br /> If you have any questions or need further assistance, please dont hesitate to reach out to us at <a href="mailto"info@mychex.co.uk"> info@mychex.co.uk </a>.';
        $managerContent = 'We hope this email finds you well.<br /><br />We wanted to inform you that the title of a team on MyChex has been successfully changed. Below are the updated details: <br /><br /><b>Old Team Title: </b> ' . $team->title . '<br /> <b>New Team Title: </b> ' . $validated['title'] . ' <br /><br /> If you have any further instructions or require assistance regarding this change, please feel free to reach out at <a href="mailto:info@mychex.co.uk">info@mychex.co.uk</a> <br /><br />Thank you for your attention to this matter.';
        $otherManagerContent = 'We hope this email finds you well.<br /><br /> We wanted to inform you that <b>' . Auth::user()->name . '</b> has changed the title of team. Below are the updated details:<br /><br /><b>Old Team Title: </b> ' . $team->title . '<br /> <b>New Team Title: </b> ' . $validated['title'] . ' <br /><br />If you have any further instructions or require assistance regarding this change, please feel free to reach out at <a href="mailto:info@mychex.co.uk">info@mychex.co.uk</a><br /><br />Thank you for your attention to this matter.';
        $this->sendStaffEmail($team->users, $subj, $staffContent);
        $this->sendManagerEmail($subj, $managerContent);
        $this->sendOtherManagersEmail($subj, $otherManagerContent);

        Notification::send($team->users, new GeneralNotification($team, 'Team', 'Update', 'Attention! Your team title has been updated to ' . $validated['title'] . '. Please review immediately.', '', ''));
        Notification::send(auth()->user(), new GeneralNotification($team, 'Team', 'Update', $validated['title'] . ' team has been changed on MyChex. Manager, please check your email for details.', '', ''));
        $this->sendOtherManagersNotificaiton($team,  Auth()->user() . ' has changed the title of team ' . $validated['title'] . '. Please check your email for details.');
        $this->sendFCMNotificaiton($team, $subj, "Your team title on MyChex has been changed to " . $validated['title'] . ". Tap to view details. ");
    }

    private function teamUpdate($team, $validated)
    {
        $content = '';
        $subj = 'Team Updates';
        $status = $validated['is_active'] == 1 ? "Activation " : 'Deactivation ';
        $activated = $validated['is_active'] == 1 ? "activated " : 'deactivated ';


        if ($team->title != $validated['title']) {
            $subj = 'Team Title Change Notification';
            $content = '<br />We wanted to inform you that the team title on MyChex has been updated. The new team title is: <br /><b>' . $team->title . '</b> <br />';
        }

        $changed_title = $team->title != $validated['title'] ? 'Title Change &' : '';
        if ($team->is_active != $validated['is_active']) {
            $subj =  $changed_title . 'Team Status' . $status;
            $content .= '<br />We wanted to inform you that the status of ' . $team->title . ' has been ' . $activated . '. Please take note of this status change and refrain from further action until further notice.';
        }

        $changed_status = $team->is_active != $validated['is_active'] ? $changed_title . 'Team Status &' : '';
        if (count($validated['user_id']) != count($team->users)) {
            $subj =  $changed_status . 'Team Members Updated';
        }
        $team_user_list = $team->users->pluck('id')->toArray();
        $newly_attach = array_diff($validated['user_id'], $team_user_list);
        $newly_attached_user = User::whereIn('id', $newly_attach)->get();

        $removed_user = array_diff($team_user_list, $validated['user_id']);
        $removing_user = User::whereIn('id', $removed_user)->get();


        if (count($newly_attached_user) > 0) {
            $new_attached_user = $this->getMoreStaff($newly_attached_user);
            $content .= '<br /><br /><b>New Team Members:</b>' . $new_attached_user . "<br /><br />";
        }

        if (count($removing_user) > 0) {
            $new_removed_user = $this->getMoreStaff($removing_user);
            $content .= '<br /><br /><b>Removed Team Members:</b>' . $new_removed_user . "<br /><br />";
        }

        $email = '<a href="mailto:info@mychex.co.uk">info@mychex.co.uk</a>';
        $content .= "If there are any actions required from your end or if you have any questions regarding this status activation, please feel free to reach out to us at " . $email . "<br /><br />Thank you for your attention to this matter.";
        if (count($newly_attached_user) > 0) {
            $this->sendStaffEmail($newly_attached_user, $subj, $content);
        } else if (count($removing_user) > 0) {
            $this->sendStaffEmail($removing_user, $subj, $content);
        } else {
            $this->sendStaffEmail($team->users, $subj, $content);
        }

        $this->sendManagerEmail($subj, $content);
        $this->sendOtherManagersEmail($subj, $content);
    }

    private function sendManagerEmail($subject, $content)
    {
        $this->generalService->sendEmail(Auth::user(), $subject, $content);
    }

    private function sendOtherManagersEmail($subject, $content)
    {
        $managers = User::where('id', '!=', Auth::id())->role('Manager')->where('company_id', Auth::user()->company_id)->get();
        foreach ($managers as $manager) {
            $this->generalService->sendEmail($manager, $subject, $content);
        }
    }

    private function sendOtherManagersNotificaiton($module, $content, $subj = null, $emailContent = null)
    {
        $managers = User::where('id', '!=', Auth::id())->role('Manager')->where('company_id', Auth::user()->company_id)->get();
        Notification::send($managers, new GeneralNotification($module, 'Team', 'POST', $content, $subj, $emailContent));
    }

    private function sendStaffEmail($users, $subject, $content)
    {
        foreach ($users as $user) {
            $this->generalService->sendEmail($user, $subject, $content);
        }
    }

    private function getMoreStaff($users)
    {
        if (count($users) == 1) {
            $content = $users[0]->name;
        }
        if (count($users) == 2) {
            $content = $users[0]->name . ", " . $users[1]->name;
        }
        if (count($users) > 2) {
            $content = $users[0]->name . ", " . $users[1]->name . " and more";
        }

        return $content;
    }

    private function sendFCMNotificaiton($team, $title, $content)
    {
        $this->generalService->sendFCMNotification($team->users, $title, $content);
    }
}
