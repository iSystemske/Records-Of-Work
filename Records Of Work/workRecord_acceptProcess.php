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
use Gibbon\Module\RecordsOfWork\Domain\IssueGateway;
use Gibbon\Module\RecordsOfWork\Domain\TechGroupGateway;
use Gibbon\Module\RecordsOfWork\Domain\TechnicianGateway;

//Bit of a cheat, but needed for gateway to work
$_POST['address'] = '/modules/Records Of Work/workRecord_acceptProcess.php';

require_once '../../gibbon.php';

$URL = $session->get('absoluteURL') . '/index.php?q=/modules/' . $session->get('module');

if (!isActionAccessible($guid, $connection2, '/modules/Records Of Work/workRecord_view.php')) {
    //Fail 0
    $URL .= '/workRecord_view.php&return=error0';
    header("Location: {$URL}");
    exit();
} else {
    //Proceed!
    $workrecordID = $_GET['workrecordID'] ?? '';
    
    $issueGateway = $container->get(IssueGateway::class);
    $issue = $issueGateway->getByID($workrecordID);

    if (empty($issue) || $issue['qualityassuaranceID'] != null) {
        //Fail 3
        $URL .= '/workRecord_view.php&return=error1';
        header("Location: {$URL}");
        exit();
    } else {
        $gibbonPersonID = $session->get('gibbonPersonID');

        $techGroupGateway = $container->get(TechGroupGateway::class);

        $technicianGateway = $container->get(TechnicianGateway::class);
        $technician = $technicianGateway->getTechnicianByPersonID($gibbonPersonID);

        if ($technician->isNotEmpty() && $techGroupGateway->getPermissionValue($gibbonPersonID, 'acceptRecords')) {
            $URL .= '/workRecord_discussView.php&workrecordID=' . $workrecordID;  
    
            //Write to database
            $qualityassuaranceID = $technician->fetch()['qualityassuaranceID'];
            if (!$issueGateway->update($workrecordID, ['qualityassuaranceID' => $qualityassuaranceID, 'status' => 'InReview'])) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Send Notification
            $notificationGateway = $container->get(NotificationGateway::class);
            $notificationSender = new NotificationSender($notificationGateway, $session);

            $notificationSender->addNotification($issue['gibbonPersonID'], __('Quality assuarance team has started working on your records.'), 'Records Of Work', $absoluteURL . '/index.php?q=/modules/Records Of Work/workRecord_discussView.php&workrecordID=' . $workrecordID);

            $notificationSender->sendNotifications();

            //Log
            $logGateway = $container->get(LogGateway::class);
            $logGateway->addLog($session->get('gibbonSchoolYearID'), 'Records Of Work', $gibbonPersonID, 'Record Of Work Accepted', ['workrecordID' => $workrecordID, 'qualityassuaranceID' => $qualityassuaranceID]);

            //Success 1 aka Accepted
            $URL .= '&return=success0';
            header("Location: {$URL}");
            exit();
        } else {
            $URL .= '/workRecord_view.php&return=error0';
            header("Location: {$URL}");
            exit();
        }
    }
}
?>
