<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

use Gibbon\Comms\NotificationSender;
use Gibbon\Domain\System\LogGateway;
use Gibbon\Domain\System\NotificationGateway;
use Gibbon\Module\RecordsOfWork\Domain\IssueDiscussGateway;
use Gibbon\Module\RecordsOfWork\Domain\IssueGateway;
use Gibbon\Module\RecordsOfWork\Domain\TechGroupGateway;
use Gibbon\Module\RecordsOfWork\Domain\TechnicianGateway;

require_once '../../gibbon.php';

$URL = $session->get('absoluteURL') . '/index.php?q=/modules/' . $session->get('module');

if (!isActionAccessible($guid, $connection2, '/modules/Records Of Work/workRecord_view.php')) {
    $URL .= '/workRecord_view.php&return=error0';
    header("Location: {$URL}");
    exit();
} else {
    $workrecordID = $_GET['workrecordID'] ?? '';

    $issueGateway = $container->get(IssueGateway::class);
    $issue = $issueGateway->getByID($workrecordID);

    if (empty($workrecordID) || empty($issue)) {
        $URL .= '/workRecord_view.php&return=error1';
        header("Location: {$URL}");
        exit();
    }

    $gibbonPersonID = $session->get('gibbonPersonID');

    $techGroupGateway = $container->get(TechGroupGateway::class);

    if ($issueGateway->isRelated($workrecordID, $gibbonPersonID) || $techGroupGateway->getPermissionValue($gibbonPersonID, 'fullAccess')) {
      //Proceed!
        $URL .= "/workRecord_discussView.php&workrecordID=$workrecordID";

        if ($issue['status'] != 'InReview') {
            $URL .= '&return=error0';
            header("Location: {$URL}");
            exit();
        }

        $comment = $_POST['comment'] ?? '';

        if (empty($comment)) {
            $URL .= '&return=error1';
            header("Location: {$URL}");
            exit();
        }

        $issueDiscussGateway = $container->get(IssueDiscussGateway::class);

        $recordsOfWorkDiscussID = $issueDiscussGateway->insert([
            'workrecordID' => $workrecordID,
            'comment' => $comment,
            'timestamp' => date('Y-m-d H:i:s'),
            'gibbonPersonID' => $gibbonPersonID
        ]);
        
        if ($recordsOfWorkDiscussID === false) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }
       
        $technicianGateway = $container->get(TechnicianGateway::class);
        $technician = $technicianGateway->getTechnicianByPersonID($gibbonPersonID);

        $isTech = $technician->isNotEmpty() && ($issue['gibbonPersonID'] != $gibbonPersonID);

        //Send Notification
        $notificationGateway = $container->get(NotificationGateway::class);
        $notificationSender = new NotificationSender($notificationGateway, $session); 

        $message = __('A new message has been added to Record Of Work #') . $workrecordID . ' (' . $issue['issueName'] . ').';

        $personIDs = $issueGateway->getPeopleInvolved($workrecordID);

        $notificationGateway = $container->get(NotificationGateway::class);
        $notificationSender = new NotificationSender($notificationGateway, $session);
 
        foreach ($personIDs as $personID) {
            if ($personID != $gibbonPersonID) {
                $notificationSender->addNotification($personID, $message, 'Records Of Work', '/index.php?q=/modules/Records Of Work/workRecord_discussView.php&workrecordID=' . $workrecordID);
            } 
        }

        $notificationSender->sendNotifications();

        //Log
        $array = ['recordsOfWorkDiscussID' => $recordsOfWorkDiscussID];

        if ($isTech) {
            $array['qualityassuaranceID'] = $technician->fetch()['qualityassuaranceID'];
        } 

        $logGateway = $container->get(LogGateway::class);
        $logGateway->addLog($session->get('gibbonSchoolYearID'), 'Records Of Work', $gibbonPersonID, 'Discussion Posted', $array);

        $URL .= '&return=success0';
        header("Location: {$URL}");
        exit();
    } else {
        //Fail 0 aka No permission
        $URL .= '/workRecord_view.php&return=error0';
        header("Location: {$URL}");
        exit();
    }
}
?>
