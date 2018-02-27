<?php
	if (!IN_SHIFTSMITH) die();

	// Core Controller class for all controller objects to extend
	class Controller {
		public $models = array();
		public $views = array();
		public $injected_views = array();
		public static $subviews = array();
		public $index;
		public $user;
		public $db;
		
		// Add model data directly
		/*
			usage: addModel('form1', 'title', 'mytitle') // with namespace
		*/
		
		function __construct() {
			// setup user helper class
			$this->user = new User();
			
			// setup database helper class
			$this->db = new Database();
			$this->db->connect();
		}

		function clearModels() {
			$this->models = array();
		}
		
		function addModel($namespace, $name, $data='') {
			if (!is_array($namespace) && is_array($name) && $data=='') {
				$this->models[$namespace] = $name;
				return;
			}
			if (is_array($namespace)) {
				foreach ($namespace as $n) {
					$this->addModel($n, $name, $data);
				}
				return;
			}
			if (is_array($name)) {
				foreach ($name as $n) {
					$this->addModel($namespace, $n, $data);
				}
				return;
			}
			if (is_array($data)) {
				foreach ($data as $d) {
					$this->addModel($namespace, $name, $d);
				}
				return;
			}
			$this->models[$namespace.'.'.$name] = $data;
		}

		function stackModel($namespace, $name, $data='') {
			$path = $this->getModel($namespace, $name);
			if ($path != '') {
					if (!is_array($path)) {
						$this->addModel($namespace, array($name => $data));
					} else {
						$this->addModel($namespace, array_merge(array($name => $data), $path));
					}
			} else {
				$this->addModel($namespace, $name, $data);
			}
			return true;
		}
		
		/* deprecated */
		function setModel($namespace, $name, $data) {
			$this->addModel($namespace, $name, $data);
		}
		
		/* add models from kvp query */
		public function loadkvp($res) {
				if (count($res) > 0) {
				foreach ($res as $id => $array) {
					if (is_array($array)) {
						foreach ($array as $k => $v) {
							$k = explode('.', $k);
							$main_k = array_shift($k);
							$more_k = implode('.', $k);
							if (is_array($v)) {
								$main_main_k = array_shift($k);
								$more_k = implode('.', $k);
								foreach ($v as $kk => $vv) {
									$this->addModel($main_k.'.'.$main_main_k, $kk."[".$more_k."]", $vv);
								}
							} else {
								$this->addModel($main_k, $more_k, $v);
							}
						}
					}
				}
			}
		}
		
		
		function modResultsModel(&$results, $keys, $function, $new_col=null) {
			if ($new_col==null) $newcol=$keys;
			if (is_array($results)) {
				foreach ($results as $key_row => $row) {
					foreach ($row as $key => $data) {
						if ((is_array($keys) && in_array($key, $keys)) || (!is_array($keys) && $keys == $key)) {
							$results[$key_row][$new_col] = $function($data);
						}
					}
				}
			}
		}
		// get model usually variable
		/*
			usage: getModel('form1', 'title') // with namespace
		*/
		function getModel($namespace, $name) {
			if (isset($this->models[$namespace.'.'.$name])) {
				return $this->models[$namespace.'.'.$name];
			}
			return '';
		}
		
		// Load model from a controller
		function loadModel($varOrNamespace, $varOrController, $controller=null) {
			if ($model == null) {
				if (in_array($varOrNamespace, explode(',',PROTECTED_UNIT))) return;
					//throw new Exception ('Invalid model name: '.$form_name.' reserved for server-side access');
				$model = new $varOrNamespace;
				$this->models[$varOrNamespace] = $varOrController->execute();
			}
			if (in_array($controller, explode(',',PROTECTED_UNIT))) return;
				//throw new Exception ('Invalid model name: '.$form_name.' reserved for server-side access');
			$model = new $controller;
			$this->models[$varOrNamespace.'.'.$varOrController] = $controller->execute();
			
		}

		/* Sample data:
			$this->cacheForm('page', array(
				'content.body' => '',
				'tags' => array(array('name'=>'page'), array('name'=>'block'))
				//'custom[]' => array(array('header' => '', 'value'=>''), array('header' => 'a', 'value'=>'b'))
			));
		 */
		
		// Save & Load form data into cache
		function cacheForm($form_name = 'page', $default_values = array(), $options = 'N') {
			// prevent admin access (configurable)
			if (in_array($form_name, explode(',', PROTECTED_UNIT))) {
				return false;
			}

			// set form array if not created
			if (!isset($_SESSION[$form_name])) $_SESSION[$form_name] = array();

			// set default values
			foreach ($default_values as $key => $var) {
				if ((!isset($_SESSION[$form_name][$key])) || ($options == 'FORCE.CACHE')) {
					if (in_array($key, explode(',', PROTECTED_UNIT))) {
						unset($_SESSION[$form_name][$key]);
					} else {
						/* 'tags' => array() */
						if (is_array($var)) {
							/* array(0 => array('name' => 'xyz') )*/
							foreach ($var as $arr_key => $arr_val) {
								/* 0 => arr */
								if (is_numeric($arr_key) && (is_array($arr_val))) {
									// form key val
									if (!isset($_SESSION[$form_name])) $_SESSION[$form_name] = array();
									if (!isset($_SESSION[$form_name][$arr_key])) $_SESSION[$form_name][$arr_key] = array();
									foreach ($arr_val as $kk => $vv) {
										$_SESSION[$form_name.'.'.$key][$arr_key][$kk] = $vv;
									}
								} else {
									$_SESSION[$form_name][$key] = $var;
								}
							}
						} else {
							$_SESSION[$form_name][$key] = $var;
						}
					}
				}
			}

			// override previous values when form is posted
			if ($options != 'FETCH.ONLY') {
				$allkeys = input();
				foreach ($allkeys as $key => $var) {
					if (strpos($key, '.') !== false) {
						$key_explosion = explode('.', $key);
						$forekey = array_shift($key_explosion);
						if (in_array($forekey, explode(',', PROTECTED_UNIT))) {
							unset($_SESSION[$forekey]);
						} else {
							$_SESSION[$form_name][$forekey.'.'.implode('.', $key_explosion)] = $var;
						}
					} else {
						if (in_array($key, explode(',', PROTECTED_UNIT))) {
							unset($_SESSION[$form_name][$key]);
						} else {
							$_SESSION[$form_name][$key] = $var;
						}
					}
				}
			}
			
			// save data to session
			foreach ($_SESSION as $var => $value) {
				if (is_array($value)) {
					foreach ($value as $key => $val) {
						if (is_array($val)) {
							foreach ($val as $k => $v) {
								$this->addModel($var, $key.'['.$k.']', $v, 'add');
							}
						} else {
							$this->addModel($var, $key, $val);
						}
					}
				} else {
					$this->addModel($form_name, $var, $value);
				}
				
			}

			// return form cache
			return true;
		}

		// save form data to database
		function saveForm($id='new', $acceptedNamespaces = array('content', 'trigger', 'post', 'page', 'tag', 'tags', 'custom')) {
			// get database
			$db = new Database();
			$db->connect();

			// initialize db structure and get new id
			$init_shift = $id;
			if ($id=='new') {
				$init_shift = $db->getShift();
			}
				
			// clear all model data
			$this->clearModels();
			
			// cache info 
			$this->cacheForm();

			// delete previous data
			if (is_numeric($init_shift)) {
				$id = (int) $init_shift;
				$del_sql = "DELETE FROM `shiftsmith` WHERE `id`=".$id;
				$shiftroot = $db->query($del_sql);
			}

			
			$sql = array();
			foreach ($this->models as $key => $value) {
				$key_explosion = explode('.', $key);
				$forekey = array_shift($key_explosion);
				if (!in_array($forekey, explode(',', PROTECTED_UNIT)) && in_array($forekey, $acceptedNamespaces)) {
					if ($db->param($forekey) != '' && $db->param(implode('.', $key_explosion)) != '' && (trim($value) != '')) {
						$new_key = array();
						foreach ($key_explosion as $k) {
							// remove array square brackets
							$k = preg_replace('/\[[^\]]*\]/', '', $k); 
							if (!in_array($k, explode(',', PROTECTED_UNIT))) {
								$new_key[] = $k;
							}
						}
						// id, 						namespace, 					key, 										value
						$row_sql = "(".$init_shift.", '".$db->param($forekey)."' , '".$db->param(implode('.', $key_explosion))."', '".$db->param($value)."')";
						$sql[] = $row_sql;
					}
				}
			
			}
			//save new data

			$fullQuery = "INSERT INTO shiftsmith (`id`, `namespace`, `key`, `value`) VALUES ".implode(', ', $sql);

			$shiftroot = $db->query($fullQuery);
			if ($shiftroot != false) {
				foreach ($acceptedNamespaces as $namespace) {
					unset($_SESSION[$namespace]);
				}
				redirect('/admin/edit/'.q(2).'/'.$init_shift);
			}
		}
		
		function clearcache($eraseNamespaces = array('content', 'trigger', 'page', 'item', 'tag', 'tags')) {
			foreach($eraseNamespaces as $name) {
				if (isset($_SESSION[$name])) {
					unset($_SESSION[$name]);
				}
				
			}
		}
		
		
		/*
		function clearCache() {
			foreach ($_SESSION as $key => cache) {
				if (!in_array($key, explode(',', PROTECTED_UNIT))) {
					unset($_SESSION[$key]);
				}
			}
		}
		*/
		
		// loadById - load information from the db for a single item.
		function loadById($id) {
			// ensure id is numeric
			$id = (int) $id;
			
			$db = new Database();
			$db->connect();
			
			// fetch all id related models
			$data = $db->queryResults("SELECT `namespace`, `key`, `value`
										FROM shiftsmith
										WHERE id=".$id."
										ORDER BY `namespace`, `key`;");
										
			foreach ($data as $value) {
				if (!in_array($value['namespace'], explode(',', PROTECTED_UNIT))) {
					if (!in_array($value['key'], explode(',', PROTECTED_UNIT))) {
						$this->setModel($value['namespace'], $value['key'], $value['value']);
					}
				}
			}		
		}
			
		
		// secure single property storage not accessible with formcache nor models
		function setcache($namespace, $name, $value) {
			// only admin access (configurable)
			if (in_array($namespace, explode(',',PROTECTED_UNIT))) {
				// set form array if not created
				if (!isset($_SESSION[$namespace])) $_SESSION[$namespace] = array();
				
				// store cashe
				$_SESSION[trim($namespace)][$name] = $value;
			}
		}

		// secure single property fetch not accessible with formcache nor models
		function getcache($namespace, $name) {
			// only admin access (configurable)
			if (in_array($namespace, explode(',',PROTECTED_UNIT))) {
				// set form array if not created
				if (!isset($_SESSION[$namespace])) return '';
				if (!isset($_SESSION[$namespace][$name])) return '';
				// store cashe
				return $_SESSION[trim($namespace)][$name];
			}
		}
		
		// Load view template from filename
		function loadView($view_filename) {
			if (is_array($view_filename)) {
				foreach($view_filename as $filename) {
					$this->views[$filename] = $filename;
				}
			} else {
				$this->views[$view_filename] = $view_filename;
			}
		}


		// inject resource
		function procure($params) {

			// set default values
			$defaults = array(
				"content" => "",			// content to inject
				"file_contents" => "",		// ajax content to inject, overrides content
				"placeholder" => "body",	// selector: #id.class
				"display" => "html",		// or text
				"filter" => "",				// selector: #id.class
				"mode" => "append",			// or prepend, replace, return
				"javascript" => "no"		// enable js render
			);

			// override default values with provided parameters
			$params = array_merge($defaults, $params);
			
			if ($params['mode'] == 'return') {
				$docX = phpQuery::newDocument("");

					$content = $params["content"];
					$file_contents = $params["file_contents"];
					$placeholder = $params["placeholder"];
					$display = $params["display"];
					$mode = $params["mode"];
					$filter = $params["filter"];
					$js = $params["javascript"];
					
					$injected_output = '';
					$docZ = '';
					if (trim($file_contents) != '') {
						// fetch ajax
						$docZ = phpQuery::newDocument(@file_get_contents($file_contents));
					} elseif (trim($content) != '') {
						// fetch simple content
						$docZ = phpQuery::newDocument($content);
					}
					
					if (trim($filter) != '') {
						// selector filter of response
						$mydoc = $docZ[$filter];
					} else {
						// no filter
						$mydoc = $docZ;
					}
					
					if ($display == 'html') {
						$injected_output = $mydoc->html();
					} else {
						$injected_output = $mydoc->text();
					}
					
					return $injected_output;
				

			} else {
				$this->injected_views[] = $params;
			}
		}
			
		
		function loadViewAsJSON($omitted_namespaces, $ommited_models) {
			$JSON = array();
			foreach ($this->models as $modelOrNamespace => $thisJSON) {
				if (!in_array($modelOrNamespace, $ommited_models) && !is_array($thisJSON)) {
					$JSON[$modelOrNamespace] = $thisJSON;
				} elseif (is_array($thisJSON) && !in_array($modelOrNamespace, $ommited_namespaces)) {
					$block = array();
					foreach ($thisJSON as $namespace => $model) {
						if (!in_array($namespace, $ommited_models)) {
							$block[$namespace] = $model;
						}
					}
					$JSON[$modelOrNamespace] = $block;
				}
			}
			echo json_encode($JSON);
		}
		
		function loadJSON() {
			$this->loadViewAsJSON();
		}

}