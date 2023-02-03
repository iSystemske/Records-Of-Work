<?php

use Gibbon\Module\RecordsOfWork\Domain\ReplyTemplateGateway;

require_once '../../gibbon.php';

$URL = $session->get('absoluteURL') . '/index.php?q=/modules/' . $session->get('module');

if (!isActionAccessible($guid, $connection2, '/modules/Records Of Work/recordsOfWork_manageReplyTemplates.php')) {
    //Acess denied
    $URL .= '/workRecord_view.php&return=error0';
    header("Location: {$URL}");
    exit();
} else {    

    $replyTemplateGateway = $container->get(ReplyTemplateGateway::class);

    $recordsOfWporkReplyTemplateID = $_POST['recordsOfWporkReplyTemplateID'] ?? '';    

    if (empty($recordsOfWporkReplyTemplateID) || !$replyTemplateGateway->exists($recordsOfWporkReplyTemplateID)) {
        $URL .= '/recordsOfWork_manageReplyTemplates.php&return=error1';
        header("Location: {$URL}");
        exit();
    } else {
        $URL .= '/recordsOfWork_editReplyTemplate.php';
        $data = [
            'name' => $_POST['name'] ?? '',
            'body' => $_POST['body'] ?? '',
        ];
        if (empty($data['name']) || empty($data['body']) || !$replyTemplateGateway->unique($data, ['name'], $recordsOfWporkReplyTemplateID)) {
            $URL .= '&return=error1';
            header("Location: {$URL}");
            exit();
        } else {
            if (!$replyTemplateGateway->update($recordsOfWporkReplyTemplateID, $data)) {
                $URL .= '&return=error2';
            } else {
                $URL .= '&return=success0&recordsOfWporkReplyTemplateID=' . $recordsOfWporkReplyTemplateID;
            }

            header("Location: {$URL}");
            exit();
        }
    }
}   
?>
