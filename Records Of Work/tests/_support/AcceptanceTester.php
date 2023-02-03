<?php


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

    protected $breadcrumbEnd = '.trailEnd';

   /**
    * Define custom actions here
    */

    public function login($name, $password)
    {
        $I = $this;

        $I->amOnPage('/');
        $I->submitForm('form[id=loginForm]', [
            'username' => $name,
            'password' => $password
        ]);
    }

    public function loginAsAdmin()
    {
        $this->login('testingadmin', '7SSbB9FZN24Q');
    }

    public function loginAsTeacher()
    {
        $this->login('testingteacher', 'm86GVNLH7DbV');
    }
    
    public function loginAsTeacher2()
    {
        $this->login('testingteacher2', 'm86GVNLH7DbV');
    }

    public function loginAsStudent()
    {
        $this->login('testingstudent', 'WKLm9ELHLJL5');
    }

    public function loginAsParent()
    {
        $this->login('testingparent', 'UVSf5t7epNa7');
    }

    public function loginAsSupport()
    {
        $this->login('testingsupport', '84BNQAQfNyKa');
    }

    //HELPDESK LOGINS
    public function loginAsHeadTech()
    {
        $this->login('testingheadtech', '7SSbB9FZN24Q');
    }
    
    public function loginAsTech()
    {
        $this->login('testingtech', '7SSbB9FZN24Q');
    }

    public function clickNavigation($text)
    {
        return $this->click($text, '.linkTop a');
    }

    public function seeBreadcrumb($text)
    {
        return $this->see($text, $this->breadcrumbEnd);
    }

    public function seeSuccessMessage($text = 'Your request was completed successfully.')
    {
        return $this->see($text, '.success');
    }

    public function seeErrorMessage($text = '')
    {
        return $this->see($text, '.error');
    }

    public function seeWarningMessage($text = '')
    {
        return $this->see($text, '.warning');
    }

    public function grabValueFromURL($param)
    {
        return $this->grabFromCurrentUrl('/'.$param.'=([^=&\s]+)/');
    }

    public function grabEditIDFromURL()
    {
        return $this->grabFromCurrentUrl('/editID=(\d+)/');
    }

    public function selectFromDropdown($selector, $n)
    {
        $n = intval($n);

        if ($n < 0) {
            $option = $this->grabTextFrom('#content select[name='.$selector.'] option:nth-last-of-type('.abs($n).')');
        } else {
            $option = $this->grabTextFrom('#content select[name='.$selector.'] option:nth-of-type('.$n.')');
        }

        $this->selectOption('#content #'.$selector, $option);
    }

    public function amOnModulePage($module, $page, $params = null)
    {
        if (mb_stripos($page, '.php') === false) {
            $page .= '.php';
        }

        $url = sprintf('/index.php?q=/modules/%1$s/%2$s', $module, $page);

        if (!empty($params)) {
            $url .= '&'.http_build_query($params);
        }

        return $this->amOnPage($url);
    }

    public function createIssueForMyself()
    {
        $I = $this;
        $I->clickNavigation('Create');
        $I->seeBreadcrumb('Create Record Of Work');
        $I->fillField('issueName', 'Test Record Of Work');
        $I->fillField('description', '<p>Test Description</p>');
        $I->selectFromDropdown('subcategoryID', -2);
        $I->selectFromDropdown('gibbonSpaceID', 2);
        $I->selectFromDropdown('priority', -1);
        $I->click('Submit');
        $I->seeSuccessMessage();
        $I->seeBreadcrumb('Create Record Of Work');
    }
     public function createIssueForMyselfSimple()
    {
        $I = $this;
        $I->clickNavigation('Create');
        $I->seeBreadcrumb('Create Record Of Work');
        $I->fillField('issueName', 'Test Record Of Work');
        $I->fillField('description', '<p>Test Description</p>');
        $I->selectFromDropdown('category', 2);
        $I->selectFromDropdown('gibbonSpaceID', 2);
        $I->selectFromDropdown('priority', -1);
        $I->click('Submit');
        $I->seeSuccessMessage();
        $I->seeBreadcrumb('Create Record Of Work');
    }
    
    public function createIssueOnBehalf()
    {
        $I = $this;
        $I->clickNavigation('Create');
        $I->seeBreadcrumb('Create Record Of Work');
        $I->fillField('issueName', 'Test Record Of Work');
        $I->fillField('description', '<p>Test Description</p>');
        $I->selectFromDropdown('subcategoryID', -2);
        $I->selectFromDropdown('gibbonSpaceID', 2);
        $I->selectFromDropdown('createFor', -1); 
        $I->selectFromDropdown('priority', -1);
        $I->click('Submit');
        $I->seeSuccessMessage();
        $I->seeBreadcrumb('Create Record Of Work');
    }
    
    public function createIssueOnBehalfSimple()
    {
        $I = $this;
        $I->clickNavigation('Create');
        $I->seeBreadcrumb('Create Record Of Work');
        $I->fillField('issueName', 'Test Record Of Work');
        $I->fillField('description', '<p>Test Description</p>');
        $I->selectFromDropdown('category', 2);
        $I->selectFromDropdown('gibbonSpaceID', 2);
        $I->selectFromDropdown('createFor', -1); 
        $I->selectFromDropdown('priority', -1);
        $I->click('Submit');
        $I->seeSuccessMessage();
        $I->seeBreadcrumb('Create Record Of Work');
    }
    
    public function acceptRecords($workrecordID)
    {
        $I = $this;
        $I->amOnModulePage('Records Of Work', 'workRecord_discussView.php', ['workrecordID' => $workrecordID]);
        $I->seeBreadcrumb('Discuss Record Of Work');

        $I->see('Test Record Of Work');
        $I->see('Test Description');

        $I->click('Accept');
        $I->seeSuccessMessage();
        $I->seeBreadcrumb('Discuss Record Of Work');
    }
    
     public function viewIssueError($workrecordID)
    {
        $I = $this;
        $I->amOnModulePage('Records Of Work', 'workRecord_discussView.php', ['workrecordID' => $workrecordID]);
        $I->seeErrorMessage();
    }
    
    public function assignRecords($workrecordID)
    {
        $I = $this;
        $I->amOnModulePage('Records Of Work', 'workRecord_assign.php', ['workrecordID' => $workrecordID]);
        $I->seeBreadcrumb('Reassign Record Of Work');

        $I->selectFromDropdown('technician', 2);
        $I->click('Submit');
        $I->seeSuccessMessage();
        $I->seeBreadcrumb('Discuss Record Of Work');
    }
    
    public function discussIssue($workrecordID)
    {
        $I = $this;
        $I->amOnModulePage('Records Of Work', 'workRecord_discussView.php', ['workrecordID' => $workrecordID]);
        $I->seeBreadcrumb('Discuss Record Of Work');

        $I->click('.comment');
        $I->fillField('comment', '<p>Discuss Test</p>');
        $I->click('Submit');
        $I->seeSuccessMessage();
        $I->seeBreadcrumb('Discuss Record Of Work');
    }
    
    public function recordsChecked($workrecordID)
    {
        $I = $this;
        $I->amOnModulePage('Records Of Work', 'workRecord_discussView.php', ['workrecordID' => $workrecordID]);
        $I->click('Resolve');
        $I->seeSuccessMessage();
        $I->seeBreadcrumb('Records');
    }
    
    public function undoRecordsChecked($workrecordID)
    {
        $I = $this;
        $I->amOnModulePage('Records Of Work', 'workRecord_discussView.php', ['workrecordID' => $workrecordID]);
        $I->click('Reincarnate');
        $I->seeSuccessMessage();
        $I->seeBreadcrumb('Discuss Record Of Work');
    }
    
    public function resolveIssueFromView($workrecordID)
    {
        $I = $this;
        $I->amOnModulePage('Records Of Work', 'workRecord_view.php');
        $I->click("Resolve", "//td[contains(text(),'".$workrecordID."')]//..");
        $I->seeSuccessMessage();
        $I->seeBreadcrumb('Records');
    }
    
    public function reincarnateIssueFromView($workrecordID)
    {
        $I = $this;
        $I->amOnModulePage('Records Of Work', 'workRecord_view.php');
        $I->click("Reincarnate", "//td[contains(text(),'".$workrecordID."')]//..");
        $I->seeSuccessMessage();
        $I->seeBreadcrumb('Discuss Record Of Work');
    }
    
    public function createDepartment()
    {
        $I = $this;
        $I->clickNavigation('Add');
        $I->seeBreadcrumb('Create Department');

        $I->fillField('schoolYearGroup', 'Test Department');
        $I->fillField('departmentDesc', 'Test Department Description');
        $I->selectOption('roles[]', array('001', '002', '003'));
        $I->click('Submit');

        $I->seeSuccessMessage();
        $I->seeBreadcrumb('Create Department');
    }
    
    public function addSubcategory($departmentID)
    {
        $I = $this;
        $I->amOnModulePage('Records Of Work', 'recordsOfWork_editDepartment.php', array('departmentID' => $departmentID));
        $I->clickNavigation('Create');
        $I->fillField('className', 'Test Subcategory');
        $I->click('Submit');
        $I->seeSuccessMessage();
    }
    
    public function deleteDepartment()
    {
        $I = $this;
        $I->amOnModulePage('Records Of Work', 'recordsOfWork_manageDepartments.php');
        $I->click("Delete", "//td[contains(text(),'Test Department')]//..");
        $I->click('Yes');
        $I->seeSuccessMessage();
    }
    
    public function editSubcategory($departmentID, $subcategoryID)
    {
        $I = $this;
        $I->click("Edit", "//td[contains(text(),'Test Subcategory')]//..");
        $I->fillField('className', 'Test Subcategory Edit');
        $I->click('Submit');
        $I->seeSuccessMessage();
    }
    public function deleteSubcategory($departmentID, $subcategoryID)
    {
        $I = $this;
        $I->click("Delete", "//td[contains(text(),'Test Subcategory')]//..");
        $I->click('Yes');
        $I->seeSuccessMessage();
    }
    
    public function changetoSimpleCategory()
    {
        $I = $this;
        $I->click('Logout');
        $I->loginAsAdmin();
        $I->amOnModulePage('Records Of Work', 'recordsOfWork_settings.php');
        $I->checkOption('simpleCategories');
        $I->click('Submit');
        $I->click('Logout');
    }
    
    public function changetoComplexCategory()
    {
        $I = $this;
        $I->click('Logout');
        $I->loginAsAdmin();
        $I->amOnModulePage('Records Of Work', 'recordsOfWork_settings.php');
        $I->uncheckOption('simpleCategories');
        $I->click('Submit');
        $I->click('Logout');
    }
    
    public function checkTeacherPermissions()
    {
        $I = $this;
        $I->dontSee('Reassign');
        $I->dontSee('Assign');
        //$I->dontSee('Accept');
    }
    
    public function checkTeacherPermissionsFromView($workrecordID)
    {
        $I = $this;
        $I->dontSee("Reassign", "//td[contains(text(),'".$workrecordID."')]//..");
        $I->dontSee("Assign", "//td[contains(text(),'".$workrecordID."')]//..");
        $I->dontSee("Accept", "//td[contains(text(),'".$workrecordID."')]//..");
    }
    
    public function checkTechPermissions()
    {
        $I = $this;
        $I->dontSee('Reassign');
        $I->dontSee('Assign');
    }
    
    public function checkTechPermissionsFromView($workrecordID)
    {
        $I = $this;
        $I->dontSee("Reassign", "//td[contains(text(),'".$workrecordID."')]//..");
        $I->dontSee("Assign", "//td[contains(text(),'".$workrecordID."')]//..");
    }
    
    
}
