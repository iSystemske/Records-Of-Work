<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('Change and check settings');
$I->loginAsAdmin();


$I->amOnModulePage('Records Of Work', 'recordsOfWork_settings.php');
$I->seeBreadcrumb('Manage Records Of Work Settings');


$newFormValues = array(
            'records0fWorkCategory' => 'Facilities,ICT',
            'recordsPriority' => '1,2,3',
            'recordsPriorityName' => 'Priority',
        );
$I->uncheckOption('simpleCategories');
$I->submitForm('#helpDeskSettings', $newFormValues, 'Submit');
$I->seeSuccessMessage();

$I->seeInFormFields('#helpDeskSettings', $newFormValues);

