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
use Gibbon\Domain\System\SettingGateway;
use Gibbon\Module\RecordsOfWork\Domain\DepartmentGateway;
use Gibbon\Module\RecordsOfWork\Domain\GroupDepartmentGateway;
use Gibbon\Module\RecordsOfWork\Domain\TechGroupGateway;

require_once '../../gibbon.php';

$URL = $session->get('absoluteURL') . '/index.php?q=/modules/' . $session->get('module');

if (!isActionAccessible($guid, $connection2, '/modules/Records Of Work/recordsOfWork_manageTechnicianGroup.php')) {
    //Fail 0
    $URL .= '/workRecord_view.php&return=error0';
    header("Location: {$URL}");
    exit();
} else {
    $groupID = $_GET['groupID'] ?? '';

    $techGroupGateway = $container->get(TechGroupGateway::class);

    if (empty($groupID) || !$techGroupGateway->exists($groupID)) {
        $URL .= '/recordsOfWork_manageTechnicianGroup.php&return=error1';
        header("Location: {$URL}");
        exit();
    } else {
        $URL .= "/recordsOfWork_editTechnicianGroup.php&groupID=$groupID";

        $departmentGateway = $container->get(DepartmentGateway::class);

        $groupName = $_POST['groupName'] ?? '';
        $departments = $_POST['departmentID'] ?? [];

        $viewRecordsStatus =  $_POST['viewRecordsStatus'] ?? '';

        if (empty($groupName) || empty($viewRecordsStatus) || ($departmentID != null && !$departmentGateway->exists($departmentID))) {
            $URL .= '&return=error1';
            header("Location: {$URL}");
            exit();
        } else {
            $settings = ['viewRecords', 'assignRecords', 'acceptRecords', 'recordsChecked', 'createRecordsForOther', 'reassignRecords', 'undoRecordsChecked', 'fullAccess'];

            $data = [
                'groupName' => $groupName,
                'viewRecordsStatus' => $viewRecordsStatus,
            ];

            foreach ($settings as $setting) {
                $data[$setting] = isset($_POST[$setting]);
            }

            if (!$techGroupGateway->unique($data, ['groupName'], $groupID)) {
                $URL .= '&return=error7';
                header("Location: {$URL}");
                exit();
            }

            $settingGateway = $container->get(SettingGateway::class);
            if (!$settingGateway->getSettingByScope('Records Of Work', 'simpleCategories')) {
                $groupDepartmentGateway = $container->get(GroupDepartmentGateway::class);
                $groupDepartmentGateway->deleteWhere(['groupID' => $groupID]);

                foreach ($departments as $departmentID) {
                    $groupDepartmentGateway->insert(['groupID' => $groupID, 'departmentID' => $departmentID]);
                }
            }

            if (!$techGroupGateway->update($groupID, $data)) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Success 0
            $logGateway = $container->get(LogGateway::class);
            $logGateway->addLog($session->get('gibbonSchoolYearID'), 'Records Of Work', $session->get('gibbonPersonID'), 'QA Group Edited', ['groupID' => $groupID]);

            $URL .= '&return=success0';
            header("Location: {$URL}");
            exit();
        }
    }
}
?>
