<?php

use Gibbon\Forms\Prefab\DeleteForm;

if (isActionAccessible($guid, $connection2, '/modules/Records Of Work/records_delete.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Proceed!
    $gibbonSchoolYearID = $_GET['gibbonSchoolYearID'] ?? '';
    $gibbonFormGroupID = $_GET['gibbonCourseClassID'] ?? '';

    //Check if gibbonFormGroupID specified
    if ($gibbonCourseClassID == '' and $gibbonCourseClassID == '') {
        $page->addError(__('You have not specified one or more required parameters.'));
    } else {
            $data = array('gibbonCourseClassID' => $gibbonCourseClassID, 'gibbonSchoolYearID' => $gibbonSchoolYearID);
            $sql = 'SELECT * FROM recordsOfWork WHERE gibbonCourseClassID=:gibbonCourseClassID AND gibbonSchoolYearID=:gibbonSchoolYearID';
            $result = $connection2->prepare($sql);
            $result->execute($data);

        if ($result->rowCount() != 1) {
            $page->addError(__('The specified record cannot be found.'));
        } else {
            $form = DeleteForm::createForm($session->get('absoluteURL').'/modules/'.$session->get('module')."/formGroup_manage_deleteProcess.php?gibbonCourseClassID=$gibbonCourseClassID", true);
            echo $form->getOutput();
        }
    }
}

?>