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
use Gibbon\Tables\DataTable;
use Gibbon\Module\RecordsOfWork\Domain\DepartmentGateway;
use Gibbon\Module\RecordsOfWork\Domain\SubcategoryGateway;

$page->breadcrumbs->add(__('Manage Departments'));

if (!isActionAccessible($guid, $connection2, '/modules/Records Of Work/recordsOfWork_manageDepartments.php')) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Proceed!
    $settingGateway = $container->get(SettingGateway::class);
    if ($settingGateway->getSettingByScope('Records Of Work', 'simpleCategories')) {
        $page->addWarning(__('Simple Categories are currently enabled. Please disabled them in Records Of Work Settings in order to Manage Departments.'));
    } else {
        $departmentGateway = $container->get(DepartmentGateway::class);
        $subcategoryGateway = $container->get(SubcategoryGateway::class);

        $departmentData = $departmentGateway->selectDepartments()->toDataSet();

        $formatCategoryList = function($row) use ($subcategoryGateway) {
            $categories = $subcategoryGateway->selectBy(['departmentID' => $row['departmentID']])->fetchAll();
            if (count($categories) < 1) {
                return __('This department does not have any subcategories.');
            }
            return implode(', ', array_column($categories, 'className'));
        };

        $table = DataTable::create('departments');
        $table->setTitle('Departments');

        $table->addHeaderAction('add', __('Add'))
                ->setURL('/modules/' . $session->get('module') . '/recordsOfWork_createDepartment.php');

        $table->addColumn('schoolYearGroup', __('Department Name'));

        $table->addColumn('departmentDesc', __('Department Description'));

        $table->addColumn('categories', __('Subcategories in department'))->format($formatCategoryList);;

        $table->addActionColumn()
                ->addParam('departmentID')
                ->format(function ($department, $actions) use ($session, $departmentData) {
                    $actions->addAction('edit', __('Edit'))
                            ->setURL('/modules/' . $session->get('module') . '/recordsOfWork_editDepartment.php');
                    $actions->addAction('delete', __('Delete'))
                            ->modalWindow()
                            ->setURL('/modules/' . $session->get('module') . '/recordsOfWork_deleteDepartment.php');
                });

        echo $table->render($departmentData);
    }
}
?>
