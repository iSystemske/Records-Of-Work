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
class IssueDiscussGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'recordsOfWorkDiscuss';
    private static $primaryKey = 'recordsOfWorkDiscussID';
    private static $searchableColumns = [];

    public function getIssueDiscussionByID($workrecordID) {
        $query = $this
            ->newSelect()
            ->cols(['recordsOfWorkDiscuss.*', 'gibbonPerson.title', 'gibbonPerson.surname', 'gibbonPerson.preferredName', 'gibbonPerson.image_240', 'gibbonPerson.username', 'gibbonPerson.email', 'schoolQA.qualityassuaranceID', '"Owner" AS type', '"Commented " AS action', '"recordsOfWorkClasses.gibbonCourseClassID" AS classes'])
            ->from('recordsOfWorkDiscuss')
            ->innerJoin('gibbonPerson', 'recordsOfWorkDiscuss.gibbonPersonID=gibbonPerson.gibbonPersonID')
            ->leftJoin('schoolQA', 'recordsOfWorkDiscuss.gibbonPersonID=schoolQA.gibbonPersonID')
            ->LeftJoin('recordsOfWorkclasses', 'recordsOfWorkDiscuss.workrecordID=recordsOfWorkclasses.workrecordID')
            ->where('recordsOfWorkDiscuss.workrecordID = :workrecordID')
            ->where('schoolQA.gibbonPersonID IS NULL')
            ->bindValue('workrecordID', $workrecordID);
            
        $query->union()
            ->cols(['recordsOfWorkDiscuss.*', 'gibbonPerson.title', 'gibbonPerson.surname', 'gibbonPerson.preferredName', 'gibbonPerson.image_240', 'gibbonPerson.username', 'gibbonPerson.email', 'schoolQA.qualityassuaranceID', '"QA" AS type', '"Commented " AS action', '"recordsOfWorkClasses.gibbonCourseClassID" as classes'])
            ->from('recordsOfWorkDiscuss')
            ->innerJoin('gibbonPerson', 'recordsOfWorkDiscuss.gibbonPersonID=gibbonPerson.gibbonPersonID')
            ->leftJoin('schoolQA', 'recordsOfWorkDiscuss.gibbonPersonID=schoolQA.gibbonPersonID')
            ->LeftJoin('recordsOfWorkclasses', 'recordsOfWorkDiscuss.workrecordID=recordsOfWorkclasses.workrecordID')
            ->where('recordsOfWorkDiscuss.workrecordID = :workrecordID')
            ->where('schoolQA.gibbonPersonID IS NOT NULL')
            ->bindValue('workrecordID', $workrecordID)
            ->orderBy(['timestamp DESC']);

        $result = $this->runSelect($query);

        return $result;
    }
}
