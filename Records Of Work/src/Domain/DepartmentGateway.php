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
class DepartmentGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'qualityAssuaranceDepartments';
    private static $primaryKey = 'departmentID';
    private static $searchableColumns = [];
    
    public function selectDepartments() {
        $select = $this
            ->newSelect()
            ->from('qualityAssuaranceDepartments')
            ->cols(['departmentID', 'schoolYearGroup', 'departmentDesc'])
            ->orderBy(['departmentID']);

        return $this->runSelect($select);
    }
    
    public function deleteDepartment($departmentID) {
        $this->db()->beginTransaction();

        //Update records to remove subcategories to be deleted
        $query = $this
            ->newUpdate() 
            ->table('recordsOfWork')
            ->set('subcategoryID', NULL)
            ->where('subcategoryID IN (SELECT subcategoryID FROM recordsOfWorkclasses WHERE departmentID = :departmentID)')
            ->bindValue('departmentID', $departmentID);

        $this->runUpdate($query);

        if (!$this->db()->getQuerySuccess()) {
            $this->db()->rollBack();
            return false;
        }

        //Delete subcategories
        $query = $this
            ->newDelete()
            ->from('recordsOfWorkclasses')
            ->where('departmentID = :departmentID')
            ->bindValue('departmentID', $departmentID);

        $this->runDelete($query);

        if (!$this->db()->getQuerySuccess()) {
            $this->db()->rollBack();
            return false;
        }

        //Delete group departments
        $query = $this
            ->newDelete()
            ->from('qualityAssuaranceGroupDepartment')
            ->where('departmentID = :departmentID')
            ->bindValue('departmentID', $departmentID);

        $this->runDelete($query);

        if (!$this->db()->getQuerySuccess()) {
            $this->db()->rollBack();
            return false;
        }

        $query = $this
            ->newDelete()
            ->from('qualityAssuaranceDepartmentPermissions')
            ->where('departmentID = :departmentID')
            ->bindValue('departmentID', $departmentID);

        $this->runDelete($query);

        if (!$this->db()->getQuerySuccess()) {
            $this->db()->rollBack();
            return false;
        }

        //Delete Department
        $this->delete($departmentID);

        if (!$this->db()->getQuerySuccess()) {
            $this->db()->rollBack();
            return false;
        }


        $this->db()->commit();
        return true;
    }
}
