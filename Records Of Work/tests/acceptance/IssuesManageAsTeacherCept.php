<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('Create and manage a record of work as a Teacher/Owner using simple and complex categories, checking permissions');
$I->loginAsTeacher();
$I->amOnModulePage('Records Of Work', 'workRecord_view.php');

// Add ------------------------------------------------
$I->createIssueForMyself();
$workrecordID = $I->grabValueFromURL('workrecordID');
$I->checkTeacherPermissions();

// discussView Accept ------------------------------------------------
$I->click('Logout');
$I->loginAsAdmin();
$I->acceptRecords($workrecordID);
$I->discussIssue($workrecordID);

// discuss ------------------------------------------------
$I->click('Logout');
$I->loginAsTeacher();
$I->discussIssue($workrecordID);

//Resolve ------------------------------------------------
$I->recordsChecked($workrecordID);

//Reincarnate ------------------------------------------------
$I->undoRecordsChecked($workrecordID);

//Resolve ------------------------------------------------
$I->recordsChecked($workrecordID);

//Test from view
$I->reincarnateIssueFromView($workrecordID);
$I->checkTeacherPermissionsFromView($workrecordID);
$I->resolveIssueFromView($workrecordID);

//check with simple categories
$I->changetoSimpleCategory();
$I->loginAsTeacher();
$I->amOnModulePage('Records Of Work', 'workRecord_view.php');

// Add ------------------------------------------------
$I->createIssueForMyselfSimple();
$workrecordID = $I->grabValueFromURL('workrecordID');

// discussView Accept ------------------------------------------------
$I->click('Logout');
$I->loginAsAdmin();
$I->acceptRecords($workrecordID);


// discuss ------------------------------------------------
$I->click('Logout');
$I->loginAsTeacher();
$I->discussIssue($workrecordID);


//Resolve ------------------------------------------------
$I->recordsChecked($workrecordID);

//Reincarnate ------------------------------------------------
$I->undoRecordsChecked($workrecordID);

//Resolve ------------------------------------------------
$I->recordsChecked($workrecordID);

//go back to complex categories
$I->changetoComplexCategory();
