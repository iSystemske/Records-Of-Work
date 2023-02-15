<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/
use Gibbon\Domain\DataSet;
use Gibbon\Forms\DatabaseFormFactory;
use Gibbon\Forms\Form;
use Gibbon\Services\Format;
use Gibbon\Tables\DataTable;
use Gibbon\Domain\User\UserGateway;
use Gibbon\Domain\School\FacilityGateway;
use Gibbon\Domain\School\SchoolYearGateway;
use Gibbon\Module\RecordsOfWork\Domain\DepartmentGateway;
use Gibbon\Module\RecordsOfWork\Domain\DepartmentPermissionsGateway;
use Gibbon\Module\RecordsOfWork\Domain\GroupDepartmentGateway;
use Gibbon\Module\RecordsOfWork\Domain\IssueGateway;
use Gibbon\Module\RecordsOfWork\Domain\SubcategoryGateway;
use Gibbon\Module\RecordsOfWork\Domain\TechGroupGateway;
use Gibbon\Module\RecordsOfWork\Domain\TechnicianGateway;
use Gibbon\Domain\System\SettingGateway;
use Gibbon\Domain\Timetable\CourseGateway;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

$page->breadcrumbs->add(__('Records'));

if (!isModuleAccessible($guid, $connection2)) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $gibbonPersonID = $session->get('gibbonPersonID');
    $moduleName = $session->get('module');

    $schoolYear = $container->get(SchoolYearGateway::class)->getByID($session->get('gibbonSchoolYearID'), ['firstDay', 'lastDay']);
    $startDate = isset($_GET['startDate']) ? Format::dateConvert($_GET['startDate']) : ($schoolYear['firstDay'] ?? null);
    $endDate = isset($_GET['endDate']) ? Format::dateConvert($_GET['endDate']) : ($schoolYear['lastDay'] ?? null);
    
    $relation = $_GET['relation'] ?? null;

    if (isset($_GET['workrecordID'])) {
        $page->return->setEditLink($session->get('absoluteURL') . '/index.php?q=/modules/' . $moduleName . '/workRecord_discussView.php&workrecordID=' . $_GET['workrecordID']);
    }

    $issueGateway = $container->get(IssueGateway::class);
    $techGroupGateway = $container->get(TechGroupGateway::class);
    $technicianGateway = $container->get(TechnicianGateway::class);
    $userGateway = $container->get(UserGateway::class);
    $settingsGateway = $container->get(SettingGateway::class);
    $groupDepartmentGateway = $container->get(GroupDepartmentGateway::class);

    $technician = $technicianGateway->getTechnicianByPersonID($gibbonPersonID);
    $isTechnician = $technician->isNotEmpty();
    $techGroup = $techGroupGateway->getByID($isTechnician ? $technician->fetch()['groupID'] : '');
    $techDepartments = $isTechnician ? $groupDepartmentGateway->selectGroupDepartments($techGroup['groupID'])->fetchAll() : [];
    $fullAccess = $techGroupGateway->getPermissionValue($gibbonPersonID, 'fullAccess');
    $techDeptFilter = $isTechnician && !empty($techDepartments) && !$fullAccess && ($relation != 'My Records');

    $criteria = $issueGateway->newQueryCriteria(true)
        ->searchBy($issueGateway->getSearchableColumns(), $_GET['search'] ?? '')
        ->filterBy('startDate', $startDate)
        ->filterBy('endDate', $endDate)
        ->sortBy('status', 'ASC')
        ->sortBy('workrecordID', 'DESC')
        ->fromPOST();

    //Set up Relation data
    $relations = [];

    if ($techGroupGateway->getPermissionValue($gibbonPersonID, 'viewRecords')) {
        $relations[] = 'All';
        $relation = $relation ?? 'All';
    }

    if ($isTechnician) {
        $relations[] = 'My Assigned';
        $relation = $relation ?? 'My Assigned';
    }

    $relations[] = 'My Records';

    if (!in_array($relation, $relations)) {
        $relation = 'My Records';
    }

    //Search Form
    $form = Form::create('searchForm', $session->get('absoluteURL') . '/index.php', 'get');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('q', '/modules/' . $moduleName . '/workRecord_view.php');
    $form->addHiddenValue('address', $session->get('address'));

    $form->setClass('noIntBorder fullWidth standardForm');
    $form->setTitle(__('Search & Filter'));

    $row = $form->addRow();
        $row->addLabel('search', __('Search'))
            ->description(__('Record Of Work ID, Name or Description.'));
        $row->addTextField('search')
            ->setValue($criteria->getSearchText());

    if (count($relations) > 1) {
        $row = $form->addRow()->addClass('advancedOptions hidden');
            $row->addLabel('relation', __('Relation'));
            $row->addSelect('relation')
                ->fromArray($relations)
                ->selected($relation);
    }

    $row = $form->addRow()->addClass('advancedOptions hidden');
        $row->addLabel('startDate', __('Start Date Filter'));
        $row->addDate('startDate')
            ->setDateFromValue($startDate)
            ->chainedTo('endDate')
            ->required();

    $row = $form->addRow()->addClass('advancedOptions hidden');
        $row->addLabel('endDate', __('End Date Filter'));
        $row->addDate('endDate')
            ->setDateFromValue($endDate)
            ->chainedFrom('startDate')
            ->required();

    $row = $form->addRow();
        $row->addContent('<a class="button rounded-sm" onclick="false" data-toggle=".advancedOptions">'.__('Advanced Options').'</a>')
                ->wrap('<span class="small">', '</span>')
                ->setClass('left');
        $row->addSearchSubmit($session, __('Clear Filters'));

    echo $form->getOutput();

    $simpleCategories = $settingsGateway->getSettingByScope($moduleName, 'simpleCategories');

    if ($simpleCategories || !$techDeptFilter) {
        $techDepartments = [];
    }

    $techViewIssueStatus = $techGroup['viewRecordsStatus'] ?? null;
    if ($fullAccess) {
        $techViewIssueStatus = null;
    }

    $recordOfWork = $issueGateway->queryRecords($criteria, $gibbonPersonID, $relation, $techViewIssueStatus, $techDepartments);

    $table = DataTable::createPaginated('records', $criteria);
    $table->setTitle('Records of Work');

    //FILTERS START
    $statusFilter = [
        'status:Submitted' => __('Status').': '.__('Submitted'),
        'status:InReview'    => __('Status').': '.__('InReview'),
        'status:completed'   => __('Status').': '.__('completed')
    ];

    /*
    Removed for simplicity
    if ($isTechnician) {
        switch($techGroupGateway->getPermissionValue($gibbonPersonID, 'viewRecordsStatus')) {
            case 'UP':
                unset($statusFilter['status:completed']);
                break;

            case 'PR':
                unset($statusFilter['status:Submitted']);
                break;

            case 'InReview':
                $statusFilter = [];
                break;
        }
    }
    */

    $table->addMetaData('filterOptions', $statusFilter);

    if ($simpleCategories) {
        $categoryFilters = explodeTrim($settingsGateway->getSettingByScope($moduleName, 'records0fWorkCategory'));
        foreach  ($categoryFilters as $category) {
            $table->addMetaData('filterOptions', [
                'category:'.$category => __('Category').': '.$category,
            ]);
        }
    } else {
        $departments = [];

        if ($isTechnician) {
            $departmentGateway = $container->get(DepartmentGateway::class);
            if ($techDeptFilter) {
                $departments = $techDepartments;
            } else {
                $departments = $departmentGateway->selectDepartments()->toDataSet();
            }
        } else {
            $gibbonRoleID = $session->get('gibbonRoleIDCurrent');
            $departmentPermissionGateway = $container->get(DepartmentPermissionsGateway::class);
            $departmentPermissionCriteria = $departmentPermissionGateway->newQueryCriteria()
                ->filterBy('gibbonRoleID', $gibbonRoleID);
                //->sortBy(['schoolYearGroup']);

            $departments = $departmentPermissionGateway->queryDeptPerms($departmentPermissionCriteria);
        }
/*
        $subcategoryGateway = $container->get(SubcategoryGateway::class);
        foreach ($departments as $department) {
            $table->addMetaData('filterOptions', [
                'departmentID:' . $department['departmentID'] => __('Department') . ': ' . $department['schoolYearGroup'],
            ]);

            $subcategoryCriteria = $subcategoryGateway->newQueryCriteria(true)
                ->filterBy('departmentID', $department['departmentID'])
                ->sortBy(['className']);

            $subcategories = $subcategoryGateway->querySubcategories($subcategoryCriteria);
            foreach ($subcategories as $subcategory) {
                $table->addMetaData('filterOptions', [
                    'subcategoryID:' . $subcategory['subcategoryID'] => '&emsp;' . __('Subcategory') . ': ' . $subcategory['schoolYearGroup'] . ' - ' . $subcategory['className'],
                ]);
            }
        }
        */
    }

    $priorityFilters = explodeTrim($settingsGateway->getSettingByScope($moduleName, 'recordsPriority', false));
    foreach  ($priorityFilters as $priority) {
        $table->addMetaData('filterOptions', [
            'priority:'.$priority => __('Priority').': '.$priority,
        ]);
    }
    //FILTERS END

    //Row Modifiers
    $table->modifyRows(function($issue, $row) {
        if ($issue['status'] == 'completed') {
            $row->addClass('current');
        } else if ($issue['status'] == 'Submitted') {
            $row->addClass('error');
        } else if ($issue['status'] == 'InReview') {
            $row->addClass('warning');
        }

        return $row;
    });

    //Header Actions
    if (isActionAccessible($guid, $connection2, '/modules/Records Of Work/workRecord_create.php')) {
        $table->addHeaderAction('add', __('Create'))
            ->setURL('/modules/' . $moduleName . '/workRecord_create.php')
            ->displayLabel();
    }
/*
    //Record Of Work ID column
    $table->addColumn('workrecordID', __('Record Of Work ID'))
            ->format(Format::using('number', ['workrecordID']));
*/
    //Subject & Description Column
    $table->addColumn('issueName', __('Week'))
          ->format(function ($issue) {
            return Format::bold($issue['issueName']) . '<br/>' . Format::small(Format::truncate(strip_tags($issue['description']), 100));
          });
    
    //add content display column
    $table->addColumn('contentCovered', __('Content Covered'))
    ->format(function ($issue){
      return Format::bold($issue['contentCovered']) . '<br/>' . Format::small(Format::truncate(strip_tags($issue['contentCovered']), 50));
    });
    //add classes
   $table->addColumn('gibbonCourseClassID', __('Classes'))
         ->format(function($row) use ($courseGateway,$page){
            //$courseGateway = $container->get(CourseGateway::class);
            //$gibbonC=$issue['rgibbonCourseClassID'];
            //$gibbonCourseClassID=$gibbonC['rgibbonCourseClassID'];
            if(array_key_exists('gibbonCourseClassID',$row)){
                //$gibbonCourseClassID=$issue['rgibbonCourseClassID'];
                $gibbonCourseClassID = $courseGateway->getCourseClassByID($row['gibbonCourseClassID']);
       $class= Format::courseClassName($gibbonCourseClassID['course'], $gibbonCourseClassID['class']);
                echo $class;
            } else {
                $page ->addError(__('No ID'));
                echo'no ID';
            }
            //lets go
//            return $gibbonCourseClassID;
// 
        });
//            {
  //          }*/
//           $classes = $issue['rgibbonCourseClassID'],
//            $out = Format::keyValue($issue, 'rgibbonCourseClassID', function($item){
 //               $output = Format::bold(Format::courseClassName($item['course'], $item['class']));
 //               return $output;            
  //          })
   //     );
            //$classes=$courseClassGateway->selectBy($issue['gibbonCourseClassID']);
            //}
            //return $output;
       // });

    //Owner & QA Column
/*    $table->addColumn('gibbonPersonID', __('To be checked By'))
                ->description(__('QA'))
                ->format(function ($row) use ($userGateway) {
                    $owner = $userGateway->getByID($row['gibbonPersonID']);
                    $output = Format::bold(Format::name($owner['title'], $owner['preferredName'], $owner['surname'], 'Staff')) . '<br/>';

                    $tech = $userGateway->getByID($row['techPersonID']);
                    if (!empty($tech)) {
                        $output .= Format::small(Format::name($tech['title'], $tech['preferredName'], $tech['surname'], 'Staff'));
                    }

                    return $output;
                              });
*/
    //Facility & Category Column
/*    $table->addColumn('facility', __('Facility'))
        ->description(__('Category'))
        ->format(function ($row) use ($simpleCategories) {
            $facility = $row['facility'] ?? 'N/A';

            $category = $row['category'];
            if (!$simpleCategories && !empty($row['className'])) {
                $category = $row['schoolYearGroup'] . ' - ' . $row['className'];
            }

            return  __(Format::bold($facility) . '<br/>'. Format::small($category));
        });

    //Priority Column
    if (!empty($priorityFilters)) {
        $table->addColumn('priority', __($settingsGateway->getSettingByScope($moduleName, 'recordsPriorityName')));
    }
*/
    //add teacher
    $table->addColumn('gibbonPersonID', __('Teacher'))
          ->format(function($row) use($userGateway){
            $teacher = $userGateway->getByID($row['gibbonPersonID']);
            return '<li>'.Format::name($teacher['title'], $teacher['preferredName'], $teacher['surname'], 'Staff').'</li>';
          });
    //Status & Date Column
    $table->addColumn('status', __('Status'))
          ->description(__('Date'))
          ->format(function ($issue) {
                return Format::bold(__($issue['status'])) . '<br/>' . Format::small(Format::date($issue['date']));
            });

    //Action Column
    $table->addActionColumn()
            ->addParam('workrecordID')
            ->format(function ($recordOfWork, $actions) use ($gibbonPersonID, $moduleName, $fullAccess, $techGroupGateway, $issueGateway) {
                $isPersonsIssue = $recordOfWork['gibbonPersonID'] == $gibbonPersonID;
                $related = $issueGateway->isRelated($recordOfWork['workrecordID'], $gibbonPersonID) || $fullAccess;

                $actions->addAction('view', __('Open'))
                        ->setURL('/modules/' . $moduleName . '/workRecord_discussView.php');

                if ($recordOfWork['status'] != 'completed') {
                    if ($recordOfWork['qualityassuaranceID'] == null) {
                        if (!$isPersonsIssue && $techGroupGateway->getPermissionValue($gibbonPersonID, 'acceptRecords')) {
                            $actions->addAction('accept', __('Accept'))
                                    ->directLink()
                                    ->setURL('/modules/' . $moduleName . '/workRecord_acceptProcess.php')
                                    ->setIcon('page_new');
                        }

                        if ($techGroupGateway->getPermissionValue($gibbonPersonID, 'assignRecords')) {
                            $actions->addAction('assign', __('Assign'))
                                    ->setURL('/modules/' . $moduleName . '/workRecord_assign.php')
                                    ->setIcon('attendance');
                        }
                    } else if ($techGroupGateway->getPermissionValue($gibbonPersonID, 'reassignRecords')) {
                        $actions->addAction('assign', __('Reassign'))
                                ->setURL('/modules/' . $moduleName . '/workRecord_assign.php')
                                ->setIcon('attendance');
                    }

                    if ($isPersonsIssue || ($related && $techGroupGateway->getPermissionValue($gibbonPersonID, 'recordsChecked'))) {
                        $actions->addAction('resolve', __('Mark Complete'))
                                ->directLink()
                                ->setURL('/modules/' . $moduleName . '/workRecord_resolveProcess.php')
                                ->setIcon('iconTick');
                    }
                } else {
                    if ($isPersonsIssue || ($related && $techGroupGateway->getPermissionValue($gibbonPersonID, 'undoRecordsChecked'))) {
                        $actions->addAction('reincarnate', __('Mark as Incomplete'))
                                ->directLink()
                                ->setURL('/modules/' . $moduleName . '/workRecord_reincarnateProcess.php')
                                ->setIcon('reincarnate');
                    }
                }
            });

    echo $table->render($recordOfWork);

}
?>
