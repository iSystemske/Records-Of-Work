<?php
namespace Gibbon\Module\RecordsOfWork\Domain;

use Gibbon\Domain\Traits\TableAware;
use Gibbon\Domain\QueryCriteria;
use Gibbon\Domain\QueryableGateway;

/**
 * QA Gateway
 *
 * @version v20
 * @since   v20
 */
class IssueGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'recordsOfWork';
    private static $primaryKey = 'workrecordID';
    private static $searchableColumns = ['workrecordID', 'weekNumber', 'contentCovered'];

    public function selectActiveIssueByTechnician($qualityassuaranceID) {
        $select = $this
            ->newSelect()
            ->from('recordsOfWork')
            ->cols(['workrecordID', 'weekNumber'])
            ->where('qualityassuaranceID = :qualityassuaranceID')
            ->bindValue('qualityassuaranceID', $qualityassuaranceID)
            ->where('status = \'InReview\'')
            ->orderBy(['workrecordID']);
            
        return $this->runSelect($select);
    }

    public function getIssueByID($workrecordID) {
        $criteria = $this->newQueryCriteria(false)
            ->filterBy('workrecordID', $workrecordID);

        $results = $this->queryRecords($criteria);
        return $results->getRow(0);
    }      
    
    public function queryRecords($criteria, $gibbonPersonID = null, $relation = null, $viewRecordsStatus = null, $techDepartments = null) {      
        $query = $this
            ->newQuery()
            ->from('recordsOfWork')
            ->cols(['recordsOfWork.*', 'schoolQA.gibbonPersonID AS techPersonID', 'recordsOfWorkclasses.className', 'recordsOfWorkclasses.gibbonCourseClassID as rgibbonCourseClassID'])
            ->leftJoin('recordsOfWorkclasses', 'recordsOfWork.workrecordID=recordsOfWorkclasses.workrecordID')
            ->leftJoin('schoolQA AS schoolQA', 'recordsOfWork.qualityassuaranceID=schoolQA.qualityassuaranceID');
            //->leftJoin('qualityAssuaranceDepartments', 'recordsOfWorkclasses.gibbonCourseClassID=qualityAssuaranceDepartments.departmentID')
            //->leftJoin('gibbonSpace', 'recordsOfWork.gibbonSpaceID=gibbonSpace.gibbonSpaceID')
//, 'gibbonSpace.name AS facility'
        
        if ($relation == 'My Records') {
            $query->where('recordsOfWork.gibbonPersonID = :gibbonPersonID')
                ->bindValue('gibbonPersonID', $gibbonPersonID);
        } else {
            if ($viewRecordsStatus == 'PR') {
                $query->where('recordsOfWork.status <> "Submitted"');
            } else if ($viewRecordsStatus == 'UP') {
                $query->where('recordsOfWork.status <> "completed"');
            } else if ($viewRecordsStatus == 'InReview') {
                $query->where('recordsOfWork.status = "InReview"');
            }

            if (is_array($techDepartments) && !empty($techDepartments)) {

                $inClause = '';
                foreach ($techDepartments as $key => $department) {
                    $bind = 'department' . $key;
                    $inClause .= ($key > 0 ? ',' : '') . ':' . $bind;
                    $query->bindValue($bind, $department['departmentID']);
                }

                $query->where('recordsOfWorkclasses.gibbonCourseClassID IN (' . $inClause . ')');
            }

            if ($relation == 'My Assigned') {
                $query->where('schoolQA.gibbonPersonID=:techPersonID')
                    ->bindValue('techPersonID', $gibbonPersonID);
            }
        }

        $criteria->addFilterRules([
            'workrecordID' => function($query, $workrecordID) {
                return $query
                    ->where('recordsOfWork.workrecordID = :workrecordID')
                    ->bindValue('workrecordID', $workrecordID);
            },
            'status' => function ($query, $status) {
                return $query
                    ->where('recordsOfWork.status = :status')
                    ->bindValue('status', $status);
            },
            'createdByID' => function ($query, $createdByID) {
                return $query
                    ->where('recordsOfWork.category = :category')
                    ->bindValue('category', $createdByID);
            },
/*
            'category' => function ($query, $category) {
                return $query
                    ->where('recordsOfWork.category = :category')
                    ->bindValue('category', $category);
            },
            'priority' => function ($query, $priority) {
                return $query
                    ->where('recordsOfWork.priority = :priority')
                    ->bindValue('priority', $priority);
            },
            'subcategoryID' => function ($query, $subcategoryID) {
                return $query
                    ->where('recordsOfWork.subcategoryID = :subcategoryID')
                    ->bindValue('subcategoryID', $subcategoryID);
            },
            'departmentID' => function ($query, $departmentID) {
                return $query
                    ->where('recordsOfWorkclasses.gibbonCourseClassID = :departmentID')
                    ->bindValue('departmentID', $departmentID);
            },*/
             'startDate' => function ($query, $startDate) {
                return $query
                    ->where('date >= :startDate')
                    ->bindValue('startDate', $startDate);
            },
            'endDate' => function ($query, $endDate) {
                return $query
                    ->where('date <= :endDate')
                    ->bindValue('endDate', $endDate);
            }
        ]);

       return $this->runQuery($query, $criteria);
    }
    
    public function isRelated($workrecordID, $gibbonPersonID) {
        $query = $this
            ->newQuery()
            ->from('recordsOfWork')
            ->cols(['recordsOfWork.gibbonPersonID', 'schoolQA.gibbonPersonID AS techPersonID', 'recordsOfWork.createdByID','recordsOfWorkclasses.gibbonCourseClassID as rgibbonCourseClassID'])
            ->leftJoin('schoolQA AS schoolQA', 'recordsOfWork.qualityassuaranceID=schoolQA.qualityassuaranceID')
            ->leftJoin('recordsOfWorkclasses', 'recordsOfWork.workrecordID=recordsOfWorkclasses.workrecordID')
            ->where('recordsOfWork.workrecordID = :workrecordID')
            ->bindValue('workrecordID', $workrecordID);

        $issue = $this->runSelect($query);

        return $issue->isNotEmpty() ? in_array($gibbonPersonID, $issue->fetch()) : false;
    }

    //This can probably be simplfied, however, for now it works.
    public function getPeopleInvolved($workrecordID) {
        $people = [];

        $query = $this
            ->newQuery()
            ->from('recordsOfWork')
            ->cols(['recordsOfWork.gibbonPersonID', 'schoolQA.gibbonPersonID AS techPersonID'])
            ->leftJoin('schoolQA AS schoolQA', 'recordsOfWork.qualityassuaranceID=schoolQA.qualityassuaranceID')
            ->where('recordsOfWork.workrecordID = :workrecordID')
            ->bindValue('workrecordID', $workrecordID);

        $result = $this->runSelect($query);

        if ($result->isNotEmpty()) {
            foreach ($result->fetch() as $person) {
                if (!empty($person)) {
                    $people[] = $person;
                }
            }
        }

        $query = $this
            ->newQuery()
            ->distinct()
            ->from('recordsOfWorkDiscuss')
            ->cols(['recordsOfWorkDiscuss.gibbonPersonID', 'qualityAssuaranceGroups.fullAccess'])
            ->leftJoin('schoolQA', 'recordsOfWorkDiscuss.gibbonPersonID=schoolQA.gibbonPersonID')
            ->leftJoin('qualityAssuaranceGroups', 'schoolQA.groupID=qualityAssuaranceGroups.groupID')
            ->where('recordsOfWorkDiscuss.workrecordID = :workrecordID')
            ->bindValue('workrecordID', $workrecordID)
            ->where('qualityAssuaranceGroups.fullAccess IS NOT null');
        
        $result = $this->runSelect($query);
        
        while ($person = $result->fetch()) {
            if (!in_array($person['gibbonPersonID'], $people) && $person['fullAccess']) {
                $people[] = $person['gibbonPersonID'];
            }
        }

        return $people;
    }

    
}
