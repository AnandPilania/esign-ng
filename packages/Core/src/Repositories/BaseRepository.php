<?php

namespace App\Repositories;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

abstract class BaseRepository implements RepositoryInterface
{
    const PAGE_SIZE = 6;
    /**
     * @var $model
     */
    protected $model;

    /**
     * EloquentRepository constructor.
     * @param null $model // Model::class
     * @throws BindingResolutionException
     */
    public function __construct($model = null)
    {
        $this->setModel($model);
    }

    /**
     * set repository instant
     * @param null $model // Model::class
     * @throws BindingResolutionException
     */
    public function setModel($model = null)
    {
        if ($model) {
            $this->model = app()->make($model);
        } else {
            $this->model = app()->make(
                $this->getModel()
            );
        }
    }

    /**
     * @return mixed
     */
    public function query()
    {
        return $this->model;
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function getById($id, $columns = array('*'))
    {
        return $this->model->findOrFail($id, $columns);
    }

    /**
     * @param string $orderBy
     * @param int $perPage
     * @param bool $toArray
     * @param array $columns
     * @return mixed
     */
    public function all($orderBy = 'id desc', $perPage = 15, $toArray = false, $columns = array('*'))
    {
        $data = $this->model->orderByRaw($orderBy);
        if ($toArray) {
            return $data->paginate($perPage)->getCollection()->toArray();
        }
        return $data->get();
    }

    /**
     * @param string $orderBy
     * @param int $perPage
     * @param array $with
     * @return mixed
     */
    public function allPaginate($orderBy = 'id desc', $perPage = 15, $with = array())
    {
        return $this->model->orderByRaw($orderBy)->with($with)->paginate($perPage);
    }

    /**
     * @param int $perPage Per page
     * @param array $columns Array Columns
     * @return array Return data paginate
     */
    public function paginate($perPage = 15, $columns = array('*'))
    {
        return $this->model->paginate($perPage, $columns);
    }

    /**
     * @param array $data Array data
     * @return void
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * @param array $data Array data
     * @param $id
     * @param string $attribute Attribute
     * @return void
     */
    public function update(array $data, $id, $attribute = "id")
    {
        $record = $this->model->find($id);
        return $record->update($data);
//        return $this->model->where($attribute, '=', $id)->update($data);
    }


    /**
     * @param $id
     * @return void
     */
    public function delete($id)
    {
        return $this->model->destroy($id);
    }

    /**
     * @param $id
     * @return void
     */
    public function deleteFlag($id)
    {
        $record = $this->model->find($id);
        $record->delete_flag = true;
        return $record->save();
    }

    /**
     * @param $id
     * @param array $columns Columns
     * @return array Return array
     */
    public function find($id, $columns = array('*'))
    {
        return $this->model->find($id, $columns);
    }

    /**
     * @param array $columns Columns
     * @return array Return array
     */
    public function count($columns = array('*'))
    {
        return $this->model->select($columns)->count();
    }

    /**
     * @param $attribute
     * @param $value
     * @param array $with
     * @param array $columns Columns
     * @return array Array
     */
    public function findBy($attribute, $value, $with = array(), $columns = array('*'))
    {
        return $this->model->where($attribute, '=', $value)->with($with)->first($columns);
    }

    /**
     * @param $attribute
     * @param $value
     * @param string[] $columns
     * @param array $with
     * @return mixed
     */
    public function findByReturnArray($attribute, $value, $with = array(), $columns = array('*'))
    {
        return $this->model->where($attribute, '=', $value)->with($with)->get($columns);
    }

    /**
     * @param $attribute
     * @param $value
     * @param $pluck
     * @param string[] $columns
     * @return mixed
     */
    public function findByReturnArrayPlug($attribute, $value, $pluck, $columns = array('*'))
    {
        return $this->model->where($attribute, '=', $value)->get($columns)->pluck($pluck);
    }

    /**
     * @param array $whereData
     * @param array $with
     * @param string[] $columns
     * @return mixed
     */
    public function findByMultiConditionsLimit($whereData = array(), $with = array(), $columns = array('*'))
    {
        return $this->model->where($whereData)->with($with)->first($columns);
    }

    /**
     * @param array $whereData
     * @param array $with
     * @param string[] $columns
     * @return mixed
     */
    public function findByMultiConditionsArray($whereData = array(), $with = array(), $columns = array('*'))
    {
        return $this->model->where($whereData)->with($with)->select($columns)->get();
    }

    /**
     * @param array $whereData
     * @param array $with
     * @param string[] $columns
     * @return mixed
     */
    public function findByMultiConditionsGetFirst($whereData = array(), $with = array(), $columns = array('*'))
    {
        return $this->model->where($whereData)->with($with)->select($columns)->first();
    }

    /**
     * @param $tableName
     * @param array $whereData Array where data
     * @return Collection Return array
     */
    public function findByMultiConditions($tableName, $whereData = array())
    {
        return DB::table($tableName)->where($whereData)->get();
    }

    /**
     * @param $tableName
     * @param array $whereData Array where data
     * @param int $perPage
     * @param string $orderBy
     * @param string[] $columns
     * @return LengthAwarePaginator Return array
     */
    public function pagingWithMultiConditions($tableName, $whereData = array(), $perPage = 15, $orderBy = 'id desc', $columns = array('*'))
    {
        return DB::table($tableName)
            ->where($whereData)
            ->orderByRaw($orderBy)
            ->paginate($perPage, $columns);
    }

    public function delByMultiConditions($tableName, $whereData = array())
    {
        return DB::table($tableName)
            ->where($whereData)
            ->delete();
    }

    /**
     * @param array $whereData
     * @return mixed
     */
    public function delByMultiConditionsModel($whereData = array())
    {
        return $this->model
            ->where($whereData)
            ->delete();
    }

    /**
     * update records by multi conditions
     *
     * @param array $data Array data
     * @param array $whereData Array where data
     * @return void
     */
    public function updateByMultiConditionsModel(array $data, $whereData = array())
    {
        return $this->model
            ->where($whereData)
            ->update($data);
    }

    /**
     * @param $tableName
     * @param array $whereData
     * @return mixed
     */
    public function countByMultiConditions($tableName, $whereData = array())
    {
        return DB::table($tableName)
            ->where($whereData)
            ->count();
    }

    /**
     * @param array $whereData
     * @return mixed
     */
    public function countByMultiConditionsModel($whereData = array())
    {
        return $this->model
            ->where($whereData)
            ->count();
    }

    /**
     * @param array $whereData
     * @param string $orderBy
     * @param array $select
     * @return mixed
     */
    public function getByMultiConditionsModel($whereData = array(), $orderBy = "id desc", $select = array("*"), $with = array())
    {
        return $this->model
            ->select($select)
            ->where($whereData)
            ->orderByRaw($orderBy)
            ->with($with)
            ->get();
    }

    /**
     * @param string $tableName
     * @param array $whereData
     * @param string[] $select
     * @param string $orderBy
     * @param int $perpage
     * @return LengthAwarePaginator
     */
    public function getByMultiConditionsPagination(string $tableName, $whereData = array(), $select = array("*"), $orderBy = "id desc", $perpage = 10)
    {
        return DB::table($tableName)
            ->select($select)
            ->where($whereData)
            ->orderByRaw($orderBy)
            ->paginate($perpage);
    }

    /**
     * @param string $tableName
     * @param string $field
     * @param array $data
     * @param string[] $select
     * @param string $orderBy
     * @return Collection
     */
    public function getByInConditions(string $tableName, string $field, $data = array(), $select = array("*"), $orderBy = "id desc")
    {
        return DB::table($tableName)
            ->select($select)
            ->whereIn($field, $data)
            ->orderByRaw($orderBy)
            ->get();
    }


    /**
     * @param string $field
     * @param array $data
     * @param string $fieldNotIn
     * @param array $dataNotIn
     * @param string[] $select
     * @param string $orderBy
     * @return Collection
     */
    public function getByWhereIn(string $field, $whereIn = array(), $select = array("*"), $orderBy = "id desc")
    {
        return $this->model
            ->select($select)
            ->whereIn($field, $whereIn)
            ->orderByRaw($orderBy)
            ->get();
    }

    /**
     * @param string $field
     * @param array $data
     * @param string $fieldNotIn
     * @param array $dataNotIn
     * @param string[] $select
     * @param string $orderBy
     * @return Collection
     */
    public function getByInAndNotInConditions(string $field, $data = array(), string $fieldNotIn, $dataNotIn = array(), $select = array("*"), $orderBy = "id desc")
    {
        return $this->model
            ->select($select)
            ->whereIn($field, $data)
            ->whereNotIn($fieldNotIn, $dataNotIn)
            ->orderByRaw($orderBy)
            ->get();
    }

    /**
     * @param array $whereData
     * @param string[] $select
     * @param string $orderBy
     * @param array $with
     * @return mixed
     */
    public function getManyWhereOrder($whereData = array(), $select = array("*"), $orderBy = "id asc", $with = array())
    {
        return $this->model
            ->where($whereData)
            ->select($select)
            ->orderByRaw($orderBy)
            ->with($with)
            ->get();
    }

    /**
     * @param array $whereData
     * @param int $limit
     * @param string[] $select
     * @param string $orderBy
     * @param array $with
     * @return mixed
     */
    public function getManyWhereOrderLimitNumber($whereData = array(), $limit = 5, $select = array("*"), $orderBy = "id desc", $with = array())
    {
        return $this->model
            ->where($whereData)
            ->select($select)
            ->orderByRaw($orderBy)
            ->limit($limit)
            ->with($with)
            ->get();
    }

    /**
     * @param array $whereData
     * @param string[] $select
     * @param string $orderBy
     * @return mixed
     */
    public function getManyWhereOrderLimit($whereData = array(), $select = array("*"), $orderBy = "id desc")
    {
        return $this->model
            ->where($whereData)
            ->select($select)
            ->orderByRaw($orderBy)
            ->first();
    }

    /**
     * @param array $whereData
     * @param string[] $select
     * @param string $orderBy
     * @param int $perpage
     * @param array $with
     * @return mixed
     */
    public function getManyWhereOrderPagination($whereData = array(), $select = array("*"), $orderBy = "id desc", $perpage = 10, $with = array())
    {
        return $this->model
            ->where($whereData)
            ->select($select)
            ->orderByRaw($orderBy)
            ->with($with)
            ->paginate($perpage);
    }

    /**
     * @param $stringList
     * @param $columnSearch
     * @param string[] $select
     * @param array $with
     * @return mixed
     */
    public function searchItemInStringList($stringList, $columnSearch, $select = array("*"), $with = array())
    {
        return $this->model
            ->select($select)
            ->whereRaw("find_in_set(" . $columnSearch . ", '" . $stringList . "')")
            ->with($with)
            ->get();
    }

    /**
     * @param $stringList
     * @param $columnSearch
     * @param string[] $select
     * @param array $whereData
     * @param array $with
     * @return mixed
     */
    public function searchItemInStringListUnique($stringList, $columnSearch, $select = array("*"), $whereData = array(), $with = array())
    {
        return $this->model
            ->select($select)
            ->distinct()
            ->where($whereData)
            ->whereRaw("find_in_set(" . $columnSearch . ", '" . $stringList . "')")
            ->with($with)
            ->get();
    }

    /**
     * @param array $whereData
     * @return mixed
     */
    public function deleteByMultiConditions($whereData = array())
    {
        return $this->model
            ->where($whereData)
            ->delete();
    }

    /**
     * @param array $whereData
     * @param string $orderBy
     * @return mixed
     */
    public function findByMultiConditionsModelLimit($whereData = array(), $orderBy = "id asc")
    {
        return $this->model
            ->where($whereData)
            ->orderByRaw($orderBy)
            ->first();
    }

    /**
     * @param array $whereData
     * @param array $conditionSearch
     * @return mixed
     */
    private function multipleConditionSearch($whereData = array(), $conditionSearch = array())
    {
        $model = $this->model
            ->where($whereData);
        if (!empty($conditionSearch)) {
            $model = $model->where(function ($query) use ($conditionSearch) {
                foreach ($conditionSearch as $key => $condition) {
                    if ($key == 0) {
                        $query = $query->where([$condition]);
                    } else {
                        $query = $query->orWhere([$condition]);
                    }
                }
                return $query;
            });
        }

        return $model;
    }

    /**
     * @param array $whereData
     * @param array $conditionSearch
     * @param string[] $select
     * @param string $orderBy
     * @param array $with
     * @return mixed
     */
    public function searchMultipleWhere($whereData = array(), $conditionSearch = array(), $select = array("*"), $orderBy = "id desc", $with = array())
    {
        $model = $this->multipleConditionSearch($whereData, $conditionSearch);
        return $model
            ->select($select)
            ->orderByRaw($orderBy)
            ->with($with)
            ->get();
    }

    /**
     * @param array $whereData
     * @param array $conditionSearch
     * @param string[] $select
     * @param string $orderBy
     * @param int $perPage
     * @param array $with
     * @return mixed
     */
    public function searchMultipleWherePagination($whereData = array(), $conditionSearch = array(), $select = array("*"), $orderBy = "id desc", $perPage = 10, $with = array())
    {
        $model = $this->multipleConditionSearch($whereData, $conditionSearch);
        return $model
            ->select($select)
            ->orderByRaw($orderBy)
            ->with($with)
            ->paginate($perPage);
    }

    /**
     * @param $id
     * @param string $lang
     * @param string $withTranslation
     * @return mixed
     */
    public function filterLanguage($id, $lang = DEFAULT_LANG, $withTranslation = 'translation')
    {
        if (array_key_exists($lang, config('settings.lang')) && $lang != DEFAULT_LANG) {
            $data = $this->model->where('id', $id)->with([
                $withTranslation => function ($query) use ($lang) {
                    $query->where('language_id', $lang);
                }])->first();
            if (!empty($data->$withTranslation) && !$data->$withTranslation->isEmpty()) return $data->$withTranslation[0];
        }
        return null;
    }

    /**
     * @param array $conditionSearch
     * @param array $whereOrWhere
     * @param string $attribute
     * @param array $whereIn
     * @return mixed
     */
    private function multipleWhereOrWhereSearch($conditionSearch = array(), $whereOrWhere = array(),$whereIn = array(),$attribute = 'id' )
    {
        $model = $this->model;
        if (!empty($conditionSearch)) {
            $model = $model->where(function ($query) use ($conditionSearch) {
                foreach ($conditionSearch as $key => $condition) {
                    if ($key == 0) {
                        $query = $query->where([$condition]);
                    } else {
                        $query = $query->orWhere([$condition]);
                    }
                }
                return $query;
            });
        }
        if (!empty($whereOrWhere)) {
            foreach ($whereOrWhere as $conditions) {
                $model = $model->where(function ($query) use ($conditions) {
                    foreach ($conditions as $key => $condition) {
                        if ($key == 0) {
                            $query = $query->where([$condition]);
                        } else {
                            $query = $query->orWhere([$condition]);
                        }
                    }
                    return $query;
                });
            }
        }
        if (!empty($whereIn)) {
            foreach ($whereIn as $data) {
                if (!empty($data['whereIn']) && !empty($data['whereInOrWhere'])) {
                    $whereInData = $data['whereIn'];
                    $whereOrWhereData = $data['whereInOrWhere'];
                    $model = $model->where(function ($query) use ($whereInData, $attribute, $whereOrWhereData) {
                        $query = $query->whereIn($attribute, $whereInData);
                        foreach ($whereOrWhereData as $key => $condition) {
                            $query = $query->orWhere([$condition]);
                        }
                        return $query;
                    });
                } elseif(!empty($data['whereInOrWhere'])) {
                    $whereOrWhereData = $data['whereInOrWhere'];
                    $model = $model->where(function ($query) use ($whereOrWhereData) {
                        foreach ($whereOrWhereData as $key => $condition) {
                            if ($key == 0) {
                                $query = $query->where([$condition]);
                            } else {
                                $query = $query->orWhere([$condition]);
                            }
                        }});
                }
            }

        }


        return $model;
    }

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
                                               $whereOrWhere = array(), $whereIn = array(), $select = array("*"))
    {
        $model = $this->multipleWhereOrWhereSearch($conditionSearch, $whereOrWhere, $whereIn);

        return $model
            ->where($whereData)
            ->select($select)
            ->orderByRaw($orderBy)
            ->with($with)
            ->paginate($perPage);
    }
}
