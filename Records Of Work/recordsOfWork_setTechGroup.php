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
use Gibbon\Module\RecordsOfWork\Domain\TechnicianGateway;

$page->breadcrumbs
    ->add(__('Manage Technicians'), 'recordsOfWork_manageTechnicians.php')
    ->add(__('Edit QA'));

if (!isActionAccessible($guid, $connection2, '/modules/Records Of Work/recordsOfWork_manageTechnicians.php')) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Proceed!
    $qualityassuaranceID = $_GET['qualityassuaranceID'] ?? '';

    $technicianGateway = $container->get(TechnicianGateway::class);
    $values = $technicianGateway->getByID($qualityassuaranceID);

    if (empty($qualityassuaranceID) || empty($values)) {
        $page->addError(__('No QA selected.'));
    } else {
        $sql = 'SELECT groupID as value, groupName as name FROM qualityAssuaranceGroups ORDER BY qualityAssuaranceGroups.groupID ASC';

        $form = Form::create('setTechGroup',  $session->get('absoluteURL') . '/modules/' . $session->get('module') . '/recordsOfWork_setTechGroupProcess.php?qualityassuaranceID=' . $qualityassuaranceID, 'post');
        $form->addHiddenValue('address', $session->get('address'));

        $row = $form->addRow();
            $row->addLabel('group', __('QA Group'));
            $row->addSelect('group')
                ->fromQuery($pdo, $sql, [])
                ->selected($values['groupID'])
                ->required(); 

        $form->loadAllValuesFrom($values);
        
        $row = $form->addRow();
            $row->addFooter();
            $row->addSubmit();

        echo $form->getOutput();
    }
}
?>
