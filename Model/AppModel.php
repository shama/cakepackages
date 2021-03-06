<?php
/**
 * Application Model class
 *
 * Add your application-wide methods to the class, your models will inherit them.
 *
 * @package	   app
 */

App::uses('DebugKitDebugger', 'DebugKit.Lib');
class AppModel extends Model {

/**
 * List of behaviors to load when the model object is initialized. Settings can be
 * passed to behaviors by using the behavior name as index. Eg:
 *
 * var $actsAs = array('Translate', 'MyBehavior' => array('setting1' => 'value1'))
 *
 * @var array
 * @link http://book.cakephp.org/view/1072/Using-Behaviors
 */
	public $actsAs = array(
		'CakeDjjob.CakeDjjob',
		'Containable',
	);

/**
 * Number of associations to recurse through during find calls. Fetches only
 * the first level by default.
 *
 * @var integer
 * @link http://book.cakephp.org/view/1057/Model-Attributes#recursive-1063
 */
	public $recursive = -1;

/**
 * Queries the datasource and returns a result set array.
 *
 * @param string $type Type of find operation (all / first / count / neighbors / list / threaded)
 * @param array $query Option fields (conditions / fields / joins / limit / offset / order / page / group / callbacks)
 * @return array Array of records
 * @link http://book.cakephp.org/2.0/en/models/deleting-data.html#deleteall
 */
	public function find($type = 'first', $query = array()) {
		DebugKitDebugger::startTimer($this->name . '::find(' . $type . ')');
		$results = parent::find($type, $query);
		DebugKitDebugger::stopTimer($this->name . '::find(' . $type . ')');
		return $results;
	}

/**
 * Custom Model::paginateCount() method to support custom model find pagination
 *
 * @param array $conditions
 * @param int $recursive
 * @param array $extra
 * @return array
 */
	public function paginateCount($conditions = array(), $recursive = 0, $extra = array()) {
		$parameters = compact('conditions');

		if ($recursive != $this->recursive) {
			$parameters['recursive'] = $recursive;
		}

		if (isset($extra['type'])) {
			$extra['operation'] = 'count';
			return $this->find($extra['type'], array_merge($parameters, $extra));
		} else {
			return $this->find('count', array_merge($parameters, $extra));
		}
	}

/**
 * Removes 'fields' key from count query on custom finds when it is an array,
 * as it will completely break the Model::_findCount() call
 *
 * @param string $state Either "before" or "after"
 * @param array $query
 * @param array $data
 * @return int The number of records found, or false
 * @see Model::find()
 */
	public function _findCount($state, $query, $results = array()) {
		if ($state == 'before' && isset($query['operation'])) {
			if (!empty($query['fields']) && is_array($query['fields'])) {
				if (!preg_match('/^count/i', $query['fields'][0])) {
					unset($query['fields']);
				}
			}
		}
		return parent::_findCount($state, $query, $results);
	}

/**
 * Wrapper around _get* magic methods
 *
 * @param string $method method name
 * @return results of the _get call
 */
	public function get($method) {
		$params = func_get_args();
		array_shift($params);
		$method ='_get' . ucfirst($method);

		if (!method_exists($this, $method)) {
			return false;
		}

		return call_user_func_array(array(&$this, $method), $params);
	}

}