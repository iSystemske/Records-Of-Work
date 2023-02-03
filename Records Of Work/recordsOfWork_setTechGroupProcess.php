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
use Gibbon\Module\RecordsOfWork\Domain\TechGroupGateway;
use Gibbon\Module\RecordsOfWork\Domain\TechnicianGateway;

require_once '../../gibbon.php';

$URL = $session->get('absoluteURL') . '/index.php?q=/modules/' . $session->get('module');

if (!isActionAccessible($guid, $connection2, '/modules/Records Of Work/recordsOfWork_manageTechnicians.php')) {
    $URL .= '/workRecord_view.php&return=error0';
    header("Location: {$URL}");
    exit();
} else {
    //Proceed!
    $qualityassuaranceID = $_GET['qualityassuaranceID'] ?? '';
    $group = $_POST['group'] ?? '';

    $techGroupGateway = $container->get(TechGroupGateway::class);
    $technicianGateway = $container->get(TechnicianGateway::class);

    if (empty($qualityassuaranceID) || !$technicianGateway->exists($qualityassuaranceID) || empty($group) || !$techGroupGateway->exists($group)) {
        $URL .= '/recordsOfWork_setTechGroup.php&return=error1';
        header("Location: {$URL}");
        exit();
    } else {
        //Write to database
        if (!$technicianGateway->update($qualityassuaranceID, ['groupID' => $group])) {
            $URL .= '/recordsOfWork_setTechGroup.php&return=error2';
            header("Location: {$URL}");
            exit();
        }

        $logGateway = $container->get(LogGateway::class);
        $logGateway->addLog($session->get('gibbonSchoolYearID'), 'Records Of Work', $session->get('gibbonPersonID'), 'QA Group Set', ['qualityassuaranceID' => $qualityassuaranceID, 'groupID' => $group]);

        //Success 0
        $URL .= '/recordsOfWork_manageTechnicians.php&return=success0';
        header("Location: {$URL}");
        exit();
    }
}
?>
