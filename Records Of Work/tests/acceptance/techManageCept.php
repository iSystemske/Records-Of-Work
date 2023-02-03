<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('Create, edit, and delete a technician');
$I->loginAsAdmin();
$I->amOnModulePage('Records Of Work', 'recordsOfWork_manageTechnicians.php');

// Add ------------------------------------------------
$I->clickNavigation('Add');
$I->seeBreadcrumb('Create QA');


$I->selectFromDropdown('person', 2);
$I->selectFromDropdown('group', 2);
$I->click('Submit');
$I->seeSuccessMessage();

$qualityassuaranceID = $I->grabValueFromURL('qualityassuaranceID');

// Edit ------------------------------------------------
$I->amOnModulePage('Records Of Work', 'recordsOfWork_setTechGroup.php', array('qualityassuaranceID' => $qualityassuaranceID));
$I->seeBreadcrumb('Edit QA');

$I->selectFromDropdown('group', 2);
$I->click('Submit');
$I->seeSuccessMessage();

//Delete ------------------------------------------------
$I->amOnModulePage('Records Of Work', 'recordsOfWork_technicianDelete.php', array('qualityassuaranceID' => $qualityassuaranceID));

$I->click('Submit');
$I->seeSuccessMessage();
