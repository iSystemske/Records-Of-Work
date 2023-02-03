<?php
//This doesnt check for the specific workrecordID but flushing all the notifications should mean that the test is semiaccurate each time it runs
$I = new AcceptanceTester($scenario);
$I->wantTo('Check permissions when viewing records');

//Tech viewing issue
$I->loginAsTech();
$I->amOnModulePage('Records Of Work', 'workRecord_view.php');
$I->click("Open");
$I->dontSee('', '.error');
$workrecordID = $I->grabValueFromURL('workrecordID');

//Non issue creating teacher check view
$I->click('Logout');
$I->loginAsTeacher2();
$I->viewIssueError($workrecordID);
