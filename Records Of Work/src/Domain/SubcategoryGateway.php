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
    private static $primaryKey = 'subcategoryID';
    private static $searchableColumns = [];

    public function querySubcategories($criteria) {
        $query = $this
            ->newQuery()
            ->from('recordsOfWorkclasses')
            ->cols(['subcategoryID', 'className', 'recordsOfWorkclasses.departmentID', 'schoolYearGroup', 'departmentDesc'])
            ->leftjoin('qualityAssuaranceDepartments', 'recordsOfWorkclasses.departmentID=qualityAssuaranceDepartments.departmentID');

        $criteria->addFilterRules([
            'subcategoryID' => function ($query, $subcategoryID) {
                return $query
                    ->where('recordsOfWorkclasses.subcategoryID = :subcategoryID')
                    ->bindValue('subcategoryID', $subcategoryID);
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

    public function deleteSubcategory($subcategoryID) {
        $this->db()->beginTransaction();

        $query = $this
            ->newUpdate()
            ->table('recordsOfWork')
            ->set('subcategoryID', NULL)
            ->where('subcategoryID = :subcategoryID')
            ->bindValue('subcategoryID', $subcategoryID);

        $this->runUpdate($query);

        if (!$this->db()->getQuerySuccess()) {
            $this->db()->rollBack();
            return false;
        }

        $this->delete($subcategoryID);

        if (!$this->db()->getQuerySuccess()) {
            $this->db()->rollBack();
            return false;
        }

        $this->db()->commit();
        return true;
    }
    
}
