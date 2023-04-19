<?php

use Gibbon\Forms\Prefab\DeleteForm;

if (!isActionAccessible($guid, $connection2, '/modules/Records Of Work/records_delete.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Proceed!
    $gibbonSchoolYearID = $_GET['gibbonSchoolYearID'] ?? '';
    $gibbonCourseClassID = $_GET['gibbonCourseClassID'] ?? '';
    $workrecordID = $_GET['workrecordID']?? '';

    //Check if workrecordID specified
    if ($workrecordID == '' ) {
        $page->addError(__('no way ! You have not specified one or more required parameters.'));
    } else {
            $data = array('workrecordID' => $workrecordID);
            $sql = 'DELETE FROM recordsOfWork WHERE workrecordID=:workrecordID';
            $result = $connection2->prepare($sql);
            $result->execute($data);

        if ($result->rowCount() != 1) {
            $page->addError(__('The specified record cannot be found.'));
        } else {
            $form = DeleteForm::createForm($session->get('absoluteURL').'/index.php?q=/modules/'.$session->get('module')."/workRecord_view.php?", true);
            echo $form->getOutput();
        }
    }
}

?>