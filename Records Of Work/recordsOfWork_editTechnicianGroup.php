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
use Gibbon\Forms\Form;
use Gibbon\Services\Format;
use Gibbon\Module\RecordsOfWork\Domain\DepartmentGateway;
use Gibbon\Module\RecordsOfWork\Domain\GroupDepartmentGateway;
use Gibbon\Module\RecordsOfWork\Domain\TechGroupGateway;

$page->breadcrumbs
    ->add(__('Manage QA Groups'), 'recordsOfWork_manageTechnicianGroup.php')
    ->add(__('Edit QA Group')); 

if (!isActionAccessible($guid, $connection2, '/modules/Records Of Work/recordsOfWork_manageTechnicianGroup.php')) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Proceed!
    $groupID = $_GET['groupID'] ?? '';
    
    $techGroupGateway = $container->get(TechGroupGateway::class);
    $values = $techGroupGateway->getByID($groupID);

    if (empty($values)) {
        $page->addError(__('No Group Selected.'));
    } else {
        $departmentGateway = $container->get(DepartmentGateway::class);
        $departmentData = $departmentGateway->selectDepartments()->toDataSet();

        $groupDepartmentGateway = $container->get(GroupDepartmentGateway::class);
        $groupDepartments = $groupDepartmentGateway->selectGroupDepartments($groupID)->toDataSet()->getColumn('departmentID');

        $statuses = [
            'All'       =>  __('All'),
            'UP'        =>  __('Submitted & InReview'),
            'PR'        =>  __('InReview & completed'), 
            'InReview'   =>  __('InReview')
        ];

        $form = Form::create('editTechnicianGroup', $session->get('absoluteURL') . '/modules/' . $session->get('module') . '/recordsOfWork_editTechnicianGroupProcess.php?groupID=' . $groupID , 'post');
        $form->addHiddenValue('address', $session->get('address'));
        $form->setTitle($values['groupName']);

        $form->addRow()->addHeading(__('Settings'));

        $row = $form->addRow();
            $row->addLabel('groupName', __('Group Name'));
            $row->addTextField('groupName')
                ->uniqueField('./modules/' . $session->get('module') . '/recordsOfWork_createTechnicianGroupAjax.php', ['currentGroupName' => $values['groupName']])
                ->required()
                ->setValue($values['groupName']);

        $settingGateway = $container->get(SettingGateway::class);
        if (count($departmentData) > 0 && !$settingGateway->getSettingByScope('Records Of Work', 'simpleCategories')) {
            $row = $form->addRow();
                $row->addLabel('departmentID', __('Department'))
                    ->description(__('Assigning a Department to a Tech Group will only allow techs in the group to work on records of work in the department.'));
                $row->addSelect('departmentID')
                    ->fromDataset($departmentData, 'departmentID', 'schoolYearGroup')
                    ->selectMultiple()
                    ->placeholder()
                    ->selected($groupDepartments);
        }

        $form->addRow()->addHeading(__('Permissons'));

        $row = $form->addRow();
            $row->addLabel('viewRecords', __('Allow View All records of work'))
                ->description(__('Allow the quality assuarance to see all the records of work instead of just their records of work and the records of work they working on.') . '<br/>' . Format::bold(__('Note: This overrides the "View Record Of Work Status" setting (i.e. shows all records of work regardless of status).')));
            $row->addCheckbox('viewRecords')
                ->setValue($values['viewRecords']);

        $row = $form->addRow();
            $row->addLabel('viewRecordsStatus', __('View records of work Status'))
                ->description(__('Choose what issue statuses the technicians can view.') . '<br/>' . Format::bold(__('Note: The "All" setting does not act like the "Allow View All records of work" setting (i.e. The option will only show the technician\'s own records of work and the isssues they are assigned).')));
            $row->addSelect('viewRecordsStatus')
                ->fromArray($statuses)
                ->required()
                ->selected($values['viewRecordsStatus']);

        $row = $form->addRow();
            $row->addLabel('assignRecords', __('Allow Assign records of work'))
                ->description(__('Allow the quality assuarance to assign records of work to other technicians.'));
            $row->addCheckbox('assignRecords')
                ->setValue($values['assignRecords']);

        $row = $form->addRow();
            $row->addLabel('acceptRecords', __('Allow Accept records of work'))
                ->description(__('Allow the quality assuarance to accept records of work to work on. '));
            $row->addCheckbox('acceptRecords')
                ->setValue($values['acceptRecords']);

        $row = $form->addRow();
            $row->addLabel('recordsChecked', __('Allow Resolve records of work'))
                ->description(__('Allow the quality assuarance to resolve a record of work they are working on.'));
            $row->addCheckbox('recordsChecked')
                ->setValue($values['recordsChecked']);

        $row = $form->addRow();
            $row->addLabel('createRecordsForOther', __('Allow Create records of work For Other'))
                ->description(__('Allow the quality assuarance to create records of work on behalf of others.'));
            $row->addCheckbox('createRecordsForOther')
                ->setValue($values['createRecordsForOther']);

        $row = $form->addRow();
            $row->addLabel('reassignRecords', __('Reassign Record Of Work'))
                ->description(__('This will allow the quality assuarance to reassign records of work another quality assuarance.'));
            $row->addCheckbox('reassignRecords')
                ->setValue($values['reassignRecords']);

        $row = $form->addRow();
            $row->addLabel('undoRecordsChecked', __('Reincarnate Record Of Work'))
                ->description(__('This will allow the quality assuarance to bring back a record of work that has been completed.'));
            $row->addCheckbox('undoRecordsChecked')
                ->setValue($values['undoRecordsChecked']);

        $row = $form->addRow();
            $row->addLabel('fullAccess', __('Full Access'))
                ->description(__('Enabling this will give the technician full access. This will override almost all the checks the system has in place. It will allow the quality assuarance to resolve any records of work, work on records of work they are not assigned to and all the other things listed above.'));
            $row->addCheckbox('fullAccess')
                ->setValue($values['fullAccess']);

        $form->loadAllValuesFrom($values);
        
        $row = $form->addRow();
            $row->addFooter();
            $row->addSubmit();

        echo $form->getOutput();
    }
}
?>
