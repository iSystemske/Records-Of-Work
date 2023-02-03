<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('Create and manage a record of work as a Head QA using simple and complex categories)');
$I->loginAsHeadTech();
$I->amOnModulePage('Records Of Work', 'workRecord_view.php');

// Add ------------------------------------------------
$I->createIssueOnBehalf();
$workrecordID = $I->grabValueFromURL('workrecordID');

// discussView Accept ------------------------------------------------
$I->acceptRecords($workrecordID);

// discuss ------------------------------------------------
$I->discussIssue($workrecordID);


// discussView Assign ------------------------------------------------
$I->assignRecords($workrecordID);

//Resolve ------------------------------------------------
$I->recordsChecked($workrecordID);

//Reincarnate ------------------------------------------------
$I->undoRecordsChecked($workrecordID);

//Resolve ------------------------------------------------
$I->recordsChecked($workrecordID);

//Test from view
$I->reincarnateIssueFromView($workrecordID);
$I->resolveIssueFromView($workrecordID);


//check with simple categories
$I->changetoSimpleCategory();
$I->loginAsHeadTech();
$I->amOnModulePage('Records Of Work', 'workRecord_view.php');

// Add ------------------------------------------------
$I->createIssueOnBehalfSimple();
$workrecordID = $I->grabValueFromURL('workrecordID');

// discussView Accept ------------------------------------------------
$I->acceptRecords($workrecordID);

// discuss ------------------------------------------------
$I->discussIssue($workrecordID);


// discussView Assign ------------------------------------------------
$I->assignRecords($workrecordID);

//Resolve ------------------------------------------------
$I->recordsChecked($workrecordID);

//Reincarnate ------------------------------------------------
$I->undoRecordsChecked($workrecordID);

//Resolve ------------------------------------------------
$I->recordsChecked($workrecordID);

$I->changetoComplexCategory();
