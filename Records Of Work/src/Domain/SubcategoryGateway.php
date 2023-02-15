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
class SubcategoryGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'recordsOfWorkclasses';
    private static $primaryKey = 'classID';
    private static $searchableColumns = [];

    public function queryRecordsClasses($criteria) {
        $query = $this
            ->newQuery()
            ->from('recordsOfWorkclasses')
            ->cols(['classID', 'className', 'recordsOfWorkclasses.departmentID', 'schoolYearGroup', 'departmentDesc'])
            ->leftjoin('qualityAssuaranceDepartments', 'recordsOfWorkclasses.departmentID=qualityAssuaranceDepartments.departmentID');

        $criteria->addFilterRules([
            'classID' => function ($query, $classID) {
                return $query
                    ->where('recordsOfWorkclasses.classID = :classID')
                    ->bindValue('classID', $classID);
            },
            'departmentID' => function ($query, $departmentID) {
                return $query
                    ->where('recordsOfWorkclasses.departmentID = :departmentID')
                    ->bindValue('departmentID', $departmentID);
            },
            'gibbonRoleID' => function ($query, $gibbonRoleID) {
                return $query
                    ->where('qualityAssuaranceDepartments.departmentID IN (SELECT departmentID FROM qualityAssuaranceDepartmentPermissions WHERE gibbonRoleID = :gibbonRoleID)')
                     ->bindValue('gibbonRoleID', $gibbonRoleID);
            },
            
            
        ]);

        return $this->runQuery($query, $criteria);
    }

    public function deleteSubcategory($classID) {
        $this->db()->beginTransaction();

        $query = $this
            ->newUpdate()
            ->table('recordsOfWork')
            ->set('classID', NULL)
            ->where('classID = :classID')
            ->bindValue('classID', $classID);

        $this->runUpdate($query);

        if (!$this->db()->getQuerySuccess()) {
            $this->db()->rollBack();
            return false;
        }

        $this->delete($classID);

        if (!$this->db()->getQuerySuccess()) {
            $this->db()->rollBack();
            return false;
        }

        $this->db()->commit();
        return true;
    }
    
}
