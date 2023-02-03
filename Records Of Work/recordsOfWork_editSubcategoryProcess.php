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
use Gibbon\Module\RecordsOfWork\Domain\DepartmentGateway;
use Gibbon\Module\RecordsOfWork\Domain\IssueGateway;
use Gibbon\Module\RecordsOfWork\Domain\SubcategoryGateway;

require_once '../../gibbon.php';

$URL = $session->get('absoluteURL') . '/index.php?q=/modules/' . $session->get('module');

if (!isActionAccessible($guid, $connection2, '/modules/Records Of Work/recordsOfWork_manageDepartments.php')) {
    $URL .= '/workRecord_view.php&return=error0';
    header("Location: {$URL}");
    exit();
} else {
    $departmentID = $_POST['departmentID'] ?? '';

    $departmentGateway = $container->get(DepartmentGateway::class);
    
    //Check that department exists
    if (empty($departmentID) || !$departmentGateway->exists($departmentID)) {
        $URL .= '/recordsOfWork_manageDepartments.php&return=error1';
        header("Location: {$URL}");
        exit();
    }

    $URL .= "/recordsOfWork_editDepartment.php&departmentID=$departmentID";

    $subcategoryID = $_POST['subcategoryID'] ?? '';

    $subcategoryGateway = $container->get(SubcategoryGateway::class);
    $subcategory = $subcategoryGateway->getByID($subcategoryID);

    //Check that subcategory exists and is within department
    if (empty($subcategory) || $subcategory['departmentID'] != $departmentID) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit();
    }

    $className = $_POST['className'] ?? '';

    //Check that subcategory name is valid
    if (empty($className) || strlen($className) > 55) {
    	$URL .= '&return=error1';
        header("Location: {$URL}");
        exit();
    }

    $data = ['className' => $className, 'departmentID' => $departmentID];

    //Check that subcategory name is unique (within department)
    if (!$subcategoryGateway->unique($data, ['className', 'departmentID'], $subcategoryID)) {
    	$URL .= '&return=error7';
        header("Location: {$URL}");
        exit();
    }

    //Update subcategory
    if (!$subcategoryGateway->update($subcategoryID, $data)) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit();
    }

    //Log
    $logGateway = $container->get(LogGateway::class);
    $logGateway->addLog($session->get('gibbonSchoolYearID'), 'Records Of Work', $session->get('gibbonPersonID'), 'Subcategory Edited', ['subcategoryID' => $subcategoryID]);

    $URL .= '&return=success0';
    header("Location: {$URL}");
    exit();
}
?>
