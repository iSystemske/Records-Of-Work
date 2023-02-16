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
$_POST['address'] = '/modules/Records Of Work/workRecord_resolveProcess.php';

require_once '../../gibbon.php';

$URL = $session->get('absoluteURL') . '/index.php?q=/modules/' . $session->get('module') . '/workRecord_view.php';

if (!isActionAccessible($guid, $connection2, '/modules/Records Of Work/workRecord_view.php')) {
    //Fail 0
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit();
} else {
    //Proceed!
    $gibbonPersonID = $session->get('gibbonPersonID');

    $workrecordID = $_GET['workrecordID'] ?? '';
    
    $issueGateway = $container->get(IssueGateway::class);
    $issue = $issueGateway->getByID($workrecordID);

    if (empty($workrecordID) || empty($issue)){
        //Fail 3
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit();
    } else {
        $techGroupGateway = $container->get(TechGroupGateway::class);
        $related = $issueGateway->isRelated($workrecordID, $gibbonPersonID) || $techGroupGateway->getPermissionValue($gibbonPersonID, 'fullAccess');
        if (($issue['gibbonPersonID'] == $gibbonPersonID) || ($related && $techGroupGateway->getPermissionValue($gibbonPersonID, 'recordsChecked'))) {
            //Write to database
            if (!$issueGateway->update($workrecordID, ['status' => 'completed'])) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Send Notification
            $notificationGateway = $container->get(NotificationGateway::class);
            $notificationSender = new NotificationSender($notificationGateway, $session);

            $message = __('Record Of Work #') . $workrecordID . ' (' . $issue['weekNumber'] . ') ' . __('has been completed.');

            $personIDs = $issueGateway->getPeopleInvolved($workrecordID);
            
            foreach ($personIDs as $personID) {
                if ($personID != $gibbonPersonID) {
                    $notificationSender->addNotification($personID, $message, 'Records Of Work', '/index.php?q=/modules/Records Of Work/workRecord_discussView.php&workrecordID=' . $workrecordID);
                } 
            }

            $notificationSender->sendNotifications();

            //Log
            $array['workrecordID'] = $workrecordID;

            $technicianGateway = $container->get(TechnicianGateway::class);
            $technician = $technicianGateway->getTechnicianByPersonID($gibbonPersonID);
            if ($technician->isNotEmpty()) {
                $array['qualityassuaranceID'] = $technician->fetch()['qualityassuaranceID'];
            }

            $logGateway = $container->get(LogGateway::class);
            $logGateway->addLog($session->get('gibbonSchoolYearID'), 'Records Of Work', $gibbonPersonID, 'Record Of Work completed', $array);

            //Success 0
            $URL .= '&return=success0';
            header("Location: {$URL}");
            exit();
        } else {
            $URL .= '&return=error0';
            header("Location: {$URL}");
            exit();
        }
    }
}
?>
