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

use Gibbon\Forms\Form;
use Gibbon\Module\RecordsOfWork\Domain\DepartmentGateway;
use Gibbon\Module\RecordsOfWork\Domain\SubcategoryGateway;

if (!isActionAccessible($guid, $connection2, '/modules/Records Of Work/recordsOfWork_manageDepartments.php')) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $departmentID = $_GET['departmentID'] ?? '';
    $subcategoryID = $_GET['subcategoryID'] ?? '';

    $departmentGateway = $container->get(DepartmentGateway::class);

    $subcategoryGateway = $container->get(SubcategoryGateway::class);
    $subcategory = $subcategoryGateway->getByID($subcategoryID);

    if (empty($departmentID) || !$departmentGateway->exists($departmentID) || empty($subcategory) || $subcategory['departmentID'] != $departmentID) {
        $page->addError(__('Invalid Data Provided'));
    } else {
        $form = Form::create('createSubcategory',  $session->get('absoluteURL') . '/modules/' . $session->get('module') . '/recordsOfWork_editSubcategoryProcess.php', 'post');
        $form->addHiddenValue('address', $session->get('address'));
        $form->addHiddenValue('departmentID', $departmentID);
        $form->addHiddenValue('subcategoryID', $subcategoryID);

        $row = $form->addRow();
            $row->addLabel('className', __('Subcategory Name'));
            $row->addTextField('className')
                ->required()
                ->uniqueField('./modules/' . $session->get('module') . '/recordsOfWork_createSubcategoryAjax.php', ['departmentID' => $departmentID, 'currentSubcategoryName' => $subcategory['className']])
                ->maxLength(55)
                ->setValue($subcategory['className']);

        $row = $form->addRow();
            $row->addFooter();
            $row->addSubmit();

        echo $form->getOutput();
    }
}
?>
