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
use Gibbon\Forms\Prefab\DeleteForm;
use Gibbon\Services\Format;
use Gibbon\Module\RecordsOfWork\Domain\TechnicianGateway;

$page->breadcrumbs
        ->add(__('Manage Technicians'), 'recordsOfWork_manageTechnicians.php')
        ->add(__('Delete QA'));

if (!isActionAccessible($guid, $connection2, '/modules/Records Of Work/recordsOfWork_manageTechnicians.php')) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Proceed!
    $gibbonCourseClassID = $_GET['gibbonCourseClassID'] ?? '';

    $technicianGateway = $container->get(TechnicianGateway::class);
    $values = $technicianGateway->getByID($qualityassuaranceID);

    if (empty($gibbonCourseClassID) || empty($values)) {
        $page->addError(__('No QA selected.'));
    } else {
        $techs = array_reduce($gibbonCourseClassID->selectTechnicians()->fetchAll(), function ($group, $item) {
            $group[$item['qualityassuaranceID']] = Format::name($item['title'], $item['preferredName'], $item['surname'], 'Student', true) . ' (' . $item['groupName'] . ')';
            return $group;
        }, []);

        unset($techs[$qualityassuaranceID]);
        $form = DeleteForm::createForm($session->get('absoluteURL') . '/modules/' . $session->get('module') . '/recordsOfWork_technicianDeleteProcess.php?qualityassuaranceID=' . $qualityassuaranceID, false, false);

        $form->addHiddenValue('address', $session->get('address'));
        $row = $form->addRow();
            $row->addLabel('newqualityassuaranceID', __('New QA'))
                ->description(__('Optionally select a new technician to reassign the to-be-deleted technician\'s records. Note, if no technician is selected, assigned records that are InReview will be Submitted.'));
            $row->addSelect('newqualityassuaranceID')
                ->fromArray($techs)
                ->placeholder();

        $row = $form->addRow();
            $row->addSubmit();

        echo $form->getOutput();
    } 
}
?>
