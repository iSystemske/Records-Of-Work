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

use Gibbon\Module\RecordsOfWork\Domain\DepartmentGateway;
use Gibbon\Module\RecordsOfWork\Domain\DepartmentPermissionsGateway;
use Gibbon\Domain\User\RoleGateway;

require_once '../../gibbon.php';

$URL = $session->get('absoluteURL') . '/index.php?q=/modules/' . $session->get('module');

if (!isActionAccessible($guid, $connection2, '/modules/Records Of Work/recordsOfWork_manageDepartments.php')) {
    $URL .= '/workRecord_view.php&return=error0';
    header("Location: {$URL}");
    exit();
} else {
    $departmentID = $_POST['departmentID'] ?? '';
    
    $departmentGateway = $container->get(DepartmentGateway::class);
    $departmentPermissionsGateway = $container->get(DepartmentPermissionsGateway::class);
    
    //Check that department exists
    if(empty($departmentID) || !$departmentGateway->exists($departmentID)) {
        $URL .= 'recordsOfWork_manageDepartments.php&return=error1';
        header("Location: {$URL}");
        exit();
    }

    $URL .= "/recordsOfWork_editDepartment.php&departmentID=$departmentID";

    $schoolYearGroup = $_POST['schoolYearGroup'] ?? '';
    $departmentDesc = $_POST['departmentDesc'] ?? '';
    $roles = $_POST['roles'] ?? [];

    //Check that data is valid
    if (empty($schoolYearGroup) || strlen($schoolYearGroup) > 55 || empty($departmentDesc) || strlen($departmentDesc) > 128 || empty($roles)) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit();
    }

    $data = ['schoolYearGroup' => $schoolYearGroup, 'departmentDesc' => $departmentDesc];

    //Check that department name is unique
    if (!$departmentGateway->unique($data, ['schoolYearGroup'], $departmentID)) {
        $URL .= '&return=error7';
        header("Location: {$URL}");
        exit();
    }

    //Update department
    if (!$departmentGateway->update($departmentID, $data)) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit();
    }

    //Remove current role permissions
    if (!$departmentPermissionsGateway->deleteWhere(['departmentID' => $departmentID])) {
        $URL .= '&return=warning1';
        header("Location: {$URL}");
        exit();
    }

    $return = 'success0';

    //Add new role permissions
    foreach ($roles as $role) {
        $data = ['departmentID' => $departmentID, 'gibbonRoleID' => $role];
        if ($departmentPermissionsGateway->insert($data) === false) {
            $return = 'warning1';
        }
    }

    $URL .= "&return=$return";
    header("Location: {$URL}");
    exit();
}
?>
