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
    $URL .= '/recordsOfWork_manageReplyTemplates.php';

    $replyTemplateGateway = $container->get(ReplyTemplateGateway::class);

    $recordsOfWporkReplyTemplateID = $_POST['recordsOfWporkReplyTemplateID'] ?? '';

    if (empty($recordsOfWporkReplyTemplateID) || !$replyTemplateGateway->exists($recordsOfWporkReplyTemplateID)) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit();
    } else {
        if ($replyTemplateGateway->delete($recordsOfWporkReplyTemplateID)) {
            $URL .= '&return=success0';
        } else {
            $URL .= '&return=error2';
        }

        header("Location: {$URL}");
        exit();
    }
}   
?>
