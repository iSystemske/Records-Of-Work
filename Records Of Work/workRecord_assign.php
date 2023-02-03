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
use Gibbon\Services\Format;
use Gibbon\Module\RecordsOfWork\Domain\IssueGateway;
use Gibbon\Module\RecordsOfWork\Domain\TechGroupGateway;
use Gibbon\Module\RecordsOfWork\Domain\TechnicianGateway;

if (!isActionAccessible($guid, $connection2, '/modules/Records Of Work/workRecord_view.php')) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $workrecordID = $_GET['workrecordID'] ?? '';

    $issueGateway = $container->get(IssueGateway::class);
    $issue = $issueGateway->getByID($workrecordID);

    if (empty($workrecordID) || empty($issue)) {
        $page->addError(__('No issue selected.'));
    } else {
        $isReassign = $issue['qualityassuaranceID'] != null;

        $title = $isReassign ? 'Reassign Record Of Work' : 'Assign Record Of Work';
        $page->breadcrumbs->add(__($title));

        $permission = $isReassign ? 'reassignRecords' : 'assignRecords';

        $techGroupGateway = $container->get(TechGroupGateway::class);

        if ($techGroupGateway->getPermissionValue($session->get('gibbonPersonID'), $permission)) {
            $technicianGateway = $container->get(TechnicianGateway::class);

            $techs = array_reduce($technicianGateway->selectTechnicians()->fetchAll(), function ($group, $item) {
                $group[$item['qualityassuaranceID']] = Format::name($item['title'], $item['preferredName'], $item['surname'], 'Student', true) . ' (' . $item['groupName'] . ')';
                return $group;
            }, []);

            $ownerTech = $technicianGateway->getTechnicianByPersonID($issue['gibbonPersonID']);
            if($ownerTech->isNotEmpty()) {
                unset($techs[$ownerTech->fetch()['qualityassuaranceID']]);
            }  
            
            $form = Form::create('assignRecords',  $session->get('absoluteURL') . '/modules/' . $session->get('module') . '/workRecord_assignProcess.php?workrecordID=' . $workrecordID . '&permission=' . $permission, 'post');
            $form->addHiddenValue('address', $session->get('address'));

            $row = $form->addRow();
                $row->addLabel('technician', __('QA'));
                $select = $row->addSelect('technician')
                    ->fromArray($techs)
                    ->required();
                
                if ($isReassign) {
                    $select->selected($issue['qualityassuaranceID']); 
                } else {
                    $select->placeholder();
                }

            
            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        } else {
            $page->addError(__('You do not have access to this action.'));
        }
    }
}
?>
