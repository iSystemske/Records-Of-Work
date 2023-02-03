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

use Gibbon\Domain\System\SettingGateway;
use Gibbon\Module\RecordsOfWork\Domain\IssueGateway;
use Gibbon\Module\RecordsOfWork\Domain\IssueNoteGateway;
use Gibbon\Module\RecordsOfWork\Domain\TechGroupGateway;
use Gibbon\Module\RecordsOfWork\Domain\TechnicianGateway;

require_once '../../gibbon.php';

$URL = $session->get('absoluteURL') . '/index.php?q=/modules/' . $session->get('module');

if (!isActionAccessible($guid, $connection2, '/modules/Records Of Work/workRecord_view.php')) {
    $URL .= '/workRecord_view.php&return=error0';
    header("Location: {$URL}");
    exit();
} else {
    $workrecordID = $_POST['workrecordID'] ?? '';

    $issueGateway = $container->get(IssueGateway::class);
    $issue = $issueGateway->getByID($workrecordID);

    if (empty($issue)) {
        $URL .= '/workRecord_view.php&return=error1';
        header("Location: {$URL}");
        exit();
    }

    $gibbonPersonID = $session->get('gibbonPersonID');

    $technicianGateway = $container->get(TechnicianGateway::class);
    $techGroupGateway = $container->get(TechGroupGateway::class);
    $settingGateway = $container->get(SettingGateway::class);

    $technician = $technicianGateway->getTechnicianByPersonID($gibbonPersonID);

    $noTech = empty($technicianGateway->getByID($issue['qualityassuaranceID']));

    if ($technician->isNotEmpty() //Is tech
        && !($gibbonPersonID == $issue['gibbonPersonID']) //Not owner
        && ($noTech || $issueGateway->isRelated($workrecordID, $gibbonPersonID) || $techGroupGateway->getPermissionValue($gibbonPersonID, 'fullAccess')) //Has access (no tech assigned, or is related, or has full acces) TODO: No Tech Check should probably check that the tech has permission to view Submitted records.
        && $settingGateway->getSettingByScope('Records Of Work', 'qaNotes') //Setting is enabled
    ) {
      //Proceed!
        $URL .= "/workRecord_discussView.php&workrecordID=$workrecordID";

        $note = $_POST['techNote'] ?? '';

        if (empty($note)) {
            $URL .= '&return=error1';
            header("Location: {$URL}");
            exit();
        }

        $issueNoteGateway = $container->get(IssueNoteGateway::class);

        $issueNoteID = $issueNoteGateway->insert([
            'workrecordID' => $workrecordID,
            'note' => $note,
            'timestamp' => date('Y-m-d H:i:s'),
            'gibbonPersonID' => $gibbonPersonID
        ]);
        
        if ($issueNoteID === false) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

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
