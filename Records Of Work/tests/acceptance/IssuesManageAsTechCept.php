<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('Create and manage a record of work as a normal QA using simple and complex categories, checking permissions');
$I->loginAsTech();
$I->amOnModulePage('Records Of Work', 'workRecord_view.php');

// Add ------------------------------------------------
$I->createIssueOnBehalf();
$workrecordID = $I->grabValueFromURL('workrecordID');
$I->checkTechPermissions($workrecordID);

// discussView Accept ------------------------------------------------
$I->acceptRecords($workrecordID);

// discuss ------------------------------------------------
$I->discussIssue($workrecordID);


//Resolve ------------------------------------------------
$I->recordsChecked($workrecordID);

//Reincarnate ------------------------------------------------
$I->undoRecordsChecked($workrecordID);

//Resolve ------------------------------------------------
$I->recordsChecked($workrecordID);

//Test from view
$I->reincarnateIssueFromView($workrecordID);
$I->amOnModulePage('Records Of Work', 'workRecord_view.php');
$I->checkTechPermissionsFromView($workrecordID);
$I->resolveIssueFromView($workrecordID);


//check with simple categories
$I->changetoSimpleCategory();
$I->loginAsTech();
$I->amOnModulePage('Records Of Work', 'workRecord_view.php');

// Add ------------------------------------------------
$I->createIssueOnBehalfSimple();
$workrecordID = $I->grabValueFromURL('workrecordID');

// discussView Accept ------------------------------------------------
$I->acceptRecords($workrecordID);

// discuss ------------------------------------------------
$I->discussIssue($workrecordID);


// discussView Assign ------------------------------------------------
$I->dontSee('Reassign');

//Resolve ------------------------------------------------
$I->recordsChecked($workrecordID);

//Reincarnate ------------------------------------------------
$I->undoRecordsChecked($workrecordID);

//Resolve ------------------------------------------------
$I->recordsChecked($workrecordID);

$I->changetoComplexCategory();
