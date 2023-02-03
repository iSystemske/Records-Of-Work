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

use Gibbon\Domain\System\LogGateway;
use Gibbon\Module\RecordsOfWork\Domain\TechnicianGateway;
use Gibbon\Module\RecordsOfWork\Domain\IssueGateway;

require_once '../../gibbon.php';

$URL = $session->get('absoluteURL') . '/index.php?q=/modules/' . $session->get('module');

if (!isActionAccessible($guid, $connection2, '/modules/Records Of Work/recordsOfWork_manageTechnicians.php')) {
    //Fail 0
    $URL .= '/workRecord_view.php&return=error0';
    header("Location: {$URL}");
    exit();
} else {
    //Proceed!
    $URL .= '/recordsOfWork_manageTechnicians.php';

    $qualityassuaranceID = $_GET['qualityassuaranceID'] ?? '';
    $newqualityassuaranceID = $_POST['newqualityassuaranceID'] ?? '';
    $technicianGateway = $container->get(TechnicianGateway::class);

    if (empty($qualityassuaranceID) || !$technicianGateway->exists($qualityassuaranceID) || (!empty($newqualityassuaranceID) && !$technicianGateway->exists($newqualityassuaranceID))) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit();
    } else {
        //Write to database
        $gibbonPersonID = $technicianGateway->getByID($qualityassuaranceID)['gibbonPersonID'];
        if (!$technicianGateway->deleteTechnician($qualityassuaranceID, $newqualityassuaranceID)) {
            //Fail 2
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        $logGateway = $container->get(LogGateway::class);
        $logGateway->addLog($session->get('gibbonSchoolYearID'), 'Records Of Work', $session->get('gibbonPersonID'), 'QA Removed', ['gibbonPersonID' => $gibbonPersonID]);

        //Success 0
        $URL .= '&return=success0';
        header("Location: {$URL}");
        exit();
    }
}
?>
