<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('Create, Edit and Delete QA Group');
$I->loginAsAdmin();
$I->amOnModulePage('Records Of Work', 'recordsOfWork_manageTechnicianGroup.php');

// Add ------------------------------------------------
$I->clickNavigation('Add');
$I->seeBreadcrumb('Create QA Group');

$I->fillField('groupName', 'Test Group');
$I->click('Submit');

$I->seeSuccessMessage();

$groupID = $I->grabValueFromURL('groupID');

// Edit ------------------------------------------------
$I->amOnModulePage('Records Of Work', 'recordsOfWork_editTechnicianGroup.php', array('groupID' => $groupID));
$I->seeBreadcrumb('Edit QA Group');

$I->seeInField('groupName', 'Test Group');
$I->seeInField('viewRecords', '1');
$I->seeInField('assignRecords', '');
$I->seeInField('acceptRecords', '1');
$I->seeInField('recordsChecked', '1');
$I->seeInField('createRecordsForOther', '1');
$I->seeInField('reassignRecords', '');
$I->seeInField('undoRecordsChecked', '1');
$I->seeInField('fullAccess', '');
$I->selectFromDropdown('viewRecordsStatus', 1);
//$I->selectFromDropdown('departmentID', 1); TODO: MAKE THIS WORK
$I->click('Submit');
$I->seeSuccessMessage();

// Delete ------------------------------------------------
$I->amOnModulePage('Records Of Work', 'recordsOfWork_technicianGroupDelete.php', array('groupID' => $groupID));
$I->selectFromDropdown('group', 2);
$I->click('Submit');
$I->seeSuccessMessage();
