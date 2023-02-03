<?php
//This doesnt check for the specific workrecordID but flushing all the notifications should mean that the test is semiaccurate each time it runs
$I = new AcceptanceTester($scenario);
$I->wantTo('Check for notification presence');

$I->loginAsTech();
$I->amOnPage('/index.php?q=notifications.php');
$I->see("A new issue has been added (Test Record Of Work).", "//td[contains(text(),'Records Of Work')]//..");
$I->click('Delete All Notifications');

$I->click('Logout');
$I->loginAsHeadTech();
$I->amOnPage('/index.php?q=notifications.php');
$I->see("A new issue has been added (Test Record Of Work).", "//td[contains(text(),'Records Of Work')]//..");
$I->click('Delete All Notifications');


$I->click('Logout');
$I->loginAsTeacher();
$I->amOnPage('/index.php?q=notifications.php');
$I->see("A new message has been added to Record Of Work");
$I->see("Quality assuarance team has started working on your records.", "//td[contains(text(),'Records Of Work')]//..");
$I->click('Delete All Notifications');
