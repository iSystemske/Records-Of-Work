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
class TechnicianGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'schoolQA';
    private static $primaryKey = 'qualityassuaranceID';
    private static $searchableColumns = [];

    public function selectTechnicians() {
        $query = $this
            ->newSelect()
            ->from('schoolQA')
            ->cols(['schoolQA.qualityassuaranceID', 'schoolQA.groupID', 'qualityAssuaranceGroups.groupName', 'gibbonPerson.gibbonPersonID', 'gibbonPerson.title', 'gibbonPerson.preferredName', 'gibbonPerson.surname'])
            ->leftJoin('gibbonPerson', 'gibbonPerson.gibbonPersonID=schoolQA.gibbonPersonID')
            ->leftJoin('qualityAssuaranceGroups', 'qualityAssuaranceGroups.groupID=schoolQA.groupID')
            ->where('gibbonPerson.status="Full"')
            ->orderBy(['schoolQA.qualityassuaranceID']);

        return $this->runSelect($query);
    }

    public function selectTechniciansByTechGroup($groupID) {
        $query = $this
            ->newSelect()
            ->from('schoolQA')
            ->cols(['schoolQA.qualityassuaranceID', 'gibbonPerson.gibbonPersonID', 'gibbonPerson.title', 'gibbonPerson.preferredName', 'gibbonPerson.surname'])
            ->leftJoin('gibbonPerson', 'gibbonPerson.gibbonPersonID=schoolQA.gibbonPersonID')
            ->where('schoolQA.groupID = :groupID')
            ->bindValue('groupID', $groupID)
            ->where('gibbonPerson.status = "Full"')
            ->orderBy(['schoolQA.qualityassuaranceID']);

        return $this->runSelect($query);
    }

    public function selectTechniciansByDepartment($departmentID) {
        $query = $this
            ->newSelect()
            ->distinct()
            ->from('schoolQA')
            ->cols(['gibbonPerson.gibbonPersonID'])
            ->leftJoin('gibbonPerson', 'gibbonPerson.gibbonPersonID=schoolQA.gibbonPersonID')
            ->leftJoin('qualityAssuaranceGroupDepartment', 'qualityAssuaranceGroupDepartment.groupID = schoolQA.groupID')
            ->where('(qualityAssuaranceGroupDepartment.departmentID = :departmentID')
            ->orWhere('qualityAssuaranceGroupDepartment.departmentID IS NULL)')
            ->bindValue('departmentID', $departmentID)
            ->where('gibbonPerson.status = "Full"')
            ->orderBy(['schoolQA.qualityassuaranceID']);

        return $this->runSelect($query);
    }

    public function getTechnician($qualityassuaranceID) {
        $query = $this
            ->newQuery()
            ->from('schoolQA')
            ->cols(['schoolQA.qualityassuaranceID', 'schoolQA.gibbonPersonID', 'schoolQA.groupID','gibbonPerson.title', 'gibbonPerson.surname', 'gibbonPerson.preferredName'])
            ->leftJoin('gibbonPerson', 'schoolQA.gibbonPersonID=gibbonPerson.gibbonPersonID')
            ->where('schoolQA.qualityassuaranceID = :qualityassuaranceID')
            ->bindValue('qualityassuaranceID', $qualityassuaranceID);

        return $this->runSelect($query); 
    }

    public function getTechnicianByPersonID($gibbonPersonID) {
         $query = $this
            ->newQuery()
            ->from('schoolQA')
            ->cols(['schoolQA.qualityassuaranceID', 'schoolQA.gibbonPersonID', 'schoolQA.groupID','gibbonPerson.title', 'gibbonPerson.surname', 'gibbonPerson.preferredName'])
            ->leftJoin('gibbonPerson', 'schoolQA.gibbonPersonID=gibbonPerson.gibbonPersonID')
            ->where('schoolQA.gibbonPersonID = :gibbonPersonID')
            ->bindValue('gibbonPersonID', $gibbonPersonID);

        return $this->runSelect($query);
    }

    public function deleteTechnician($qualityassuaranceID, $newqualityassuaranceID) {
        $this->db()->beginTransaction();

        //If there is no new tech, reset the InReview Records
        if (empty($newqualityassuaranceID)) {
            $newqualityassuaranceID = NULL;

            $query = $this
                ->newUpdate()
                ->table('recordsOfWork')
                ->set('qualityassuaranceID', $newqualityassuaranceID)
                ->set('status', '"Submitted"')
                ->where('qualityassuaranceID = :qualityassuaranceID')
                ->bindValue('qualityassuaranceID', $qualityassuaranceID)
                ->where('status = "InReview"');

            $this->runUpdate($query);

            if (!$this->db()->getQuerySuccess()) {
                $this->db()->rollBack();
                return false;
            }
        }

        //Change over the assigned records
        $query = $this
            ->newUpdate()
            ->table('recordsOfWork')
            ->set('qualityassuaranceID', $newqualityassuaranceID)
            ->where('qualityassuaranceID = :qualityassuaranceID')
            ->bindValue('qualityassuaranceID', $qualityassuaranceID);

        $this->runUpdate($query);

        if (!$this->db()->getQuerySuccess()) {
            $this->db()->rollBack();
            return false;
        }

        //Delete the tech
        $this->delete($qualityassuaranceID);

        if (!$this->db()->getQuerySuccess()) {
            $this->db()->rollBack();
            return false;
        }

        $this->db()->commit();
        return true;
    }

    public function selectNonTechnicians() {
        $select = $this
            ->newSelect()
            ->from('gibbonPerson')
            ->cols(['gibbonPerson.gibbonPersonID', 'title', 'surname', 'preferredName', 'username', 'gibbonRole.category'])
            ->leftJoin('gibbonRole', 'gibbonRole.gibbonRoleID=gibbonPerson.gibbonRoleIDPrimary')
            ->leftJoin('schoolQA', 'schoolQA.gibbonPersonID=gibbonPerson.gibbonPersonID')
            ->where('gibbonPerson.status = "Full"')
            ->where('schoolQA.gibbonPersonID IS NULL')
            ->orderBy(['surname', 'preferredName']);

        return $this->runSelect($select);
    }
}
