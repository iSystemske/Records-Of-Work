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
use Gibbon\Services\Format;
use Gibbon\Module\RecordsOfWork\Domain\IssueGateway;
use Gibbon\Module\RecordsOfWork\Domain\TechnicianGateway;
use Gibbon\Module\RecordsOfWork\Domain\TechGroupGateway;

require_once '../../gibbon.php';

$absoluteURL = $session->get('absoluteURL');

$URL = $absoluteURL . '/index.php?q=/modules/' . $session->get('module');

if (!isActionAccessible($guid, $connection2, '/modules/Records Of Work/workRecord_view.php')) {
    //Fail 0
    $URL .= '/workRecord_view.php&return=error0';
    header("Location: {$URL}");
    exit();
} else {
    $permission = $_GET['permission'] ?? '';

    if (empty($permission) || !in_array($permission, ['assignRecords', 'reassignRecords'])) {
        $URL .= '/workRecord_view.php&return=error1';
        header("Location: {$URL}");
        exit();
    }
    $gibbonPersonID = $session->get('gibbonPersonID');

    $techGroupGateway = $container->get(TechGroupGateway::class);
    if (!$techGroupGateway->getPermissionValue($gibbonPersonID, $permission)) {
        $URL .= '/workRecord_view.php&return=error0';
        header("Location: {$URL}");
        exit();
    }

    $workrecordID = $_GET['workrecordID'] ?? '';
    
    $issueGateway = $container->get(IssueGateway::class);
    $issue = $issueGateway->getByID($workrecordID);

    if (empty($workrecordID) || empty($issue)) {
        $URL .= '/workRecord_view.php&return=error1';
        header("Location: {$URL}");
        exit();
    }

    $qualityassuaranceID = $_POST['technician'] ?? '';
    if (empty($qualityassuaranceID)) {
        $URL .= "/workRecord_assign.php&workrecordID=$workrecordID&return=error1";
        header("Location: {$URL}");
        exit();
    }

    $technicianGateway = $container->get(TechnicianGateway::class);
    $technician = $technicianGateway->getTechnician($qualityassuaranceID);

    if ($technician->isEmpty()) {
        $URL .= "/workRecord_assign.php&workrecordID=$workrecordID&return=error1";
        header("Location: {$URL}");
        exit();
    }

    $technician = $technician->fetch();

    if ($technician['gibbonPersonID'] == $issue['gibbonPersonID']) {
        $URL .= "/workRecord_assign.php&workrecordID=$workrecordID&return=error1";
        header("Location: {$URL}");
        exit();
    }
        
    if (!$issueGateway->update($workrecordID, ['qualityassuaranceID' => $qualityassuaranceID, 'status' => 'InReview'])) {
        $URL .= "/workRecord_assign.php&workrecordID=$workrecordID&qualityassuaranceID=$qualityassuaranceID&return=error2";
        header("Location: {$URL}");
        exit();
    }

    $assign = 'assigned';
    if ($permission == 'reassignRecords') {
        $assign = 'reassigned';
    }

    //Send Notification
    $notificationGateway = $container->get(NotificationGateway::class);
    $notificationSender = new NotificationSender($notificationGateway, $session);

    $message = Format::name($technician['title'], $technician['preferredName'], $technician['surname'], 'Student') . __(" has been $assign Record Of Work #") . $workrecordID . '(' . $issue['weekNumber'] . ').';

    $personIDs = $issueGateway->getPeopleInvolved($workrecordID);

    foreach($personIDs as $personID) {
        if ($personID != $gibbonPersonID) {
            $notificationSender->addNotification($personID, $message, 'Records Of Work', '/index.php?q=/modules/Records Of Work/workRecord_discussView.php&workrecordID=' . $workrecordID);
        } 
    }    

    $notificationSender->sendNotifications();

    //Log
    $logGateway = $container->get(LogGateway::class);
    $logGateway->addLog($session->get('gibbonSchoolYearID'), 'Records Of Work', $gibbonPersonID, 'QA Assigned', ['workrecordID' => $workrecordID, 'technicainID' => $qualityassuaranceID]);

    $URL .= "/workRecord_discussView.php&workrecordID=$workrecordID&return=success0";
    header("Location: {$URL}");
    exit();
}
?>
