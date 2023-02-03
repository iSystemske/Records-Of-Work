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

use Gibbon\Forms\Prefab\DeleteForm;
use Gibbon\Module\RecordsOfWork\Domain\ReplyTemplateGateway;

if (!isActionAccessible($guid, $connection2, '/modules/Records Of Work/recordsOfWork_manageReplyTemplates.php')) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $replyTemplateGateway = $container->get(ReplyTemplateGateway::class);

    $recordsOfWporkReplyTemplateID = $_GET['recordsOfWporkReplyTemplateID'] ?? '';

    if (empty($recordsOfWporkReplyTemplateID) || !$replyTemplateGateway->exists($recordsOfWporkReplyTemplateID)) {
        $page->addError(__('Invalid Template.'));
    } else {
        $form = DeleteForm::createForm($session->get('absoluteURL') . '/modules/' . $session->get('module') . "/recordsOfWork_deleteReplyTemplateProcess.php");
        $form->addHiddenValue('address', $session->get('address'));
        $form->addHiddenValue('recordsOfWporkReplyTemplateID', $recordsOfWporkReplyTemplateID);

        echo $form->getOutput();
    }
}
?>
