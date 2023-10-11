<?php

namespace App\Repositories;
/**
 * Description of RepositoryInterface
 *
 * @author BaoDo
 */
interface RepositoryInterface
{
    /**
     * @return mixed
     */
    public function query();
    /**
     * Get all column
     * @param string $orderBy
     * @param int $perPage
     * @param bool $toArray
     * @param array $columns
     */
    public function all($orderBy = 'id desc', $perPage = 15, $toArray = false, $columns = array('*'));

    /**
     * @param string $orderBy
     * @param int $perPage
     * @param array $with
     * @return mixed
     */
    public function allPaginate($orderBy = 'id desc', $perPage = 15, $with = array());

    /**
     * paginate data
     * @param integer $perPage
     * @param array $columns
     */
    public function paginate($perPage = 15, $columns = array('*'));

    /**
     * create data
     * @param array $data
     */
    public function create(array $data);

    /**
     * update data
     * @param array $data
     * @param integer $id
     */
    public function update(array $data, int $id);

    /**
     * delete data
     * @param integer $id
     */
    public function delete(int $id);

    /**
     * delete data
     * @param integer $id
     */
    public function deleteFlag(int $id);

    /**
     * find data
     * @param integer $id
     * @param array $columns
     */
    public function find(int $id, $columns = array('*'));

    /**
     * count total record data
     * @param array $columns
     */
    public function count($columns = array('*'));

    /**
     * findBy column
     * @param $attribute
     * @param $value
     * @param array $columns
     */
    public function findBy($attribute, $value, $with = array(), $columns = array('*'));

    /**
     * @param $attribute
     * @param $value
     * @param string[] $columns
     * @param array $with
     * @return mixed
     */
    public function findByReturnArray($attribute, $value, $with = array(), $columns = array('*'));

    /**
     * findBy column
     * @param array $whereData
     * @param array $with
     * @param array $columns
     */
    public function findByMultiConditionsLimit($whereData = array(), $with = array(), $columns = array('*'));

    /**
     * @param array $whereData
     * @param array $with
     * @param string[] $columns
     * @return mixed
     */
    public function findByMultiConditionsArray($whereData = array(), $with = array(), $columns = array('*'));

    /**
     * Get by id
     * @param integer $id
     */
    public function getById($id, $columns = array('*'));

    /**
     * findByMultiConditions
     * @param string $tableName
     * @param array $whereData
     */
    public function findByMultiConditions(string $tableName, $whereData = array());

    /**
     * paging With Multi Conditions
     * @param string $tableName
     * @param array $whereData
     * @param integer $perPage
     * @param array $columns
     */
    public function pagingWithMultiConditions(string $tableName, $whereData = array(), $perPage = 15, $columns = array('*'));

    /**
     * del By Multi Conditions
     * @param string $tableName
     * @param array $whereData
     */
    public function delByMultiConditions(string $tableName, $whereData = array());

    /**
     * @param array $whereData
     */
    public function delByMultiConditionsModel($whereData = array());

    /**
     * @param array $data
     * @param array $whereData
     */
    public function updateByMultiConditionsModel(array $data, $whereData = array());

    /**
     * @param string $tableName
     * @param array $whereData
     */
    public function countByMultiConditions(string $tableName, $whereData = array());

    /**
     * @param array $whereData
     */
    public function countByMultiConditionsModel($whereData = array());

    /**
     * @param array $whereData
     * @param string $orderBy
     * @param string[] $select
     * @param array $with
     */
    public function getByMultiConditionsModel($whereData = array(), $orderBy = "id desc", $select = array("*"), $with = array());

    /**
     * @param string $tableName
     * @param array $whereData
     * @param string[] $select
     * @param string $orderBy
     * @param int $perpage
     */
    public function getByMultiConditionsPagination(string $tableName, $whereData = array(), $select = array("*"), $orderBy = "id desc", $perpage = 10);

    /**
     * @param string $tableName
     * @param string $field
     * @param array $data
     * @param string[] $select
     * @param string $orderBy
     */
    public function getByInConditions(string $tableName, string $field, $data = array(), $select = array("*"), $orderBy = "id desc");

    /**
     * @param string $field
     * @param array $data
     * @param string $fieldNotIn
     * @param array $dataNotIn
     * @param string[] $select
     * @param string $orderBy
     * @return Collection
     */
    public function getByInAndNotInConditions(string $field, $data = array(), string $fieldNotIn, $dataNotIn = array(), $select = array("*"), $orderBy = "id desc");

    /**
     * @param array $whereData
     * @param string[] $select
     * @param string $orderBy
     * @param array $with
     */
    public function getManyWhereOrder($whereData = array(), $select = array("*"), $orderBy = "id asc", $with = array());

    /**
     * @param array $whereData
     * @param int $limit
     * @param string[] $select
     * @param string $orderBy
     * @param array $with
     */
    public function getManyWhereOrderLimitNumber($whereData = array(), $limit = 5, $select = array("*"), $orderBy = "id desc", $with = array());

    /**
     * @param array $whereData
     * @param string[] $select
     * @param string $orderBy
     */
    public function getManyWhereOrderLimit($whereData = array(), $select = array("*"), $orderBy = "id desc");

    /**
     * @param array $whereData
     * @param string[] $select
     * @param string $orderBy
     * @param int $perpage
     * @param array $with
     */
    public function getManyWhereOrderPagination($whereData = array(), $select = array("*"), $orderBy = "id desc", $perpage = 10, $with = array());

    /**
     * @param $stringList
     * @param $columnSearch
     * @param string[] $select
     * @param array $with
     */
    public function searchItemInStringList($stringList, $columnSearch, $select = array("*"), $with = array());

    /**
     * @param $stringList
     * @param $columnSearch
     * @param string[] $select
     * @param array $whereData
     * @param array $with
     */
    public function searchItemInStringListUnique($stringList, $columnSearch, $select = array("*"), $whereData = array(), $with = array());

    /**
     * @param array $whereData
     */
    public function deleteByMultiConditions($whereData = array());

    /**
     * @param array $whereData
     * @param string $orderBy
     */
    public function findByMultiConditionsModelLimit($whereData = array(), $orderBy = "id asc");

    /**
     * @param array $whereData
     * @param array $conditionSearch
     * @param string[] $select
     * @param string $orderBy
     * @param array $with
     * @return mixed
     */
    public function searchMultipleWhere($whereData = array(), $conditionSearch = array(), $select = array("*"), $orderBy = "id desc", $with = array());

    /**
     * @param array $whereData
     * @param array $conditionSearch
     * @param string[] $select
     * @param string $orderBy
     * @param int $perPage
     * @param array $with
     * @return mixed
     */
    public function searchMultipleWherePagination($whereData = array(), $conditionSearch = array(), $select = array("*"), $orderBy = "id desc", $perPage = 10, $with = array());

    /**
     * @param $id
     * @param string $lang
     * @param string $withTranslation
     * @return mixed
     */
    public function filterLanguage($id, $lang = DEFAULT_LANG, $withTranslation = 'translation');

    /**
     * @param array $whereData
     * @param array $conditionSearch
     * @param string $orderBy
     * @param int $perPage
     * @param array $with
     * @param array $whereOrWhere
     * @param array $whereIn
     * @param string[] $select
     * @return mixed
     */
    public function searchMultipleWhereOrWhere($whereData = array(), $conditionSearch = array(), $orderBy = "id desc", $perPage = 10, $with = array(),
                                               $whereOrWhere = array(), $whereIn = array(), $select = array("*"));
}
