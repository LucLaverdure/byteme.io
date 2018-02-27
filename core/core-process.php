<?php
	if (!IN_SHIFTSMITH) die();

	global $global_models, $main_path;
	$global_models = array();

	include_once($main_path.'core/phpQuery/phpQuery-onefile.php');
//	include_once($main_path.'core/composer/vendor/autoload.php');

//	use JonnyW\PhantomJs\Client;
	
	class Core {

		private $obj_controllers; // Controllers Found
		
		private function endsWith($haystack, $needle) {
			$length = strlen($needle);
			if ($length == 0) {
				return true;
			}

			return (substr($haystack, -$length) === $needle);
		}
		
		private function getDirContents($dir, &$results = array()){
			$files = scandir($dir);

			foreach($files as $key => $value){
				$path = realpath($dir.DIRECTORY_SEPARATOR.$value);
				if(!is_dir($path)) {
					// on file check for allowed config extensions
					$allowed_extensions = explode(',', FILE_FILTER_CONTROLLERS);
					$allowed = false;
					foreach($allowed_extensions as $ext) {
						if ($this->endsWith($path, $ext)) {
							$allowed = true;
						}
					}
					// when allowed, add to array of controller pattern
					if ($allowed) $results[] = $path;
				} else if($value != "." && $value != "..") {
					// on directory, browse
					$this->getDirContents($path, $results);
				}
			}

			return $results;
		}

		
		public function __construct() {
			global $main_path;
			
			// Get php's core declared classes count
			$system_classes_count = count(get_declared_classes());
			$system_classes = get_declared_classes();
			
			$paths_to_load = array();
			// search for all files within the webapp directory
			
			$initial_dirs = explode(',', SEARCH_DIRECTORY_CONTROLLERS);
			
			foreach ($initial_dirs as $dir) {
				$paths_to_load[] = $this->getDirContents($dir);
			}
			
			foreach ($paths_to_load as $path) {
				foreach ($path as $path_within) {
					if ($path_within) include $path_within;
				}
			}
			
			// Remove all classes of php's core to identify webapp classes
			$custom_classes = array_slice(get_declared_classes(), $system_classes_count);

			// Instantiate each controller found (identified by class extended of Controller)
			foreach($custom_classes as $class) {
				if (in_array('Controller', class_parents($class))) {
					$this->obj_controllers[$class] = new $class;
				}
			}

		}

		// Views and subviews process
		public function process_view(&$controller, $view, $recursion_level = 0, $mode = 'append') {
			// global models, shared across all controllers
			global $global_models;
			global $main_path;
			
			// prevent infinite recursions
			if ($recursion_level > 999999)
				die("Template ".$filename." surpasses maximum recursion level. (Prevented infinite loop from crashing server)");
			
			
			// start output buffer
			ob_start();

			// view is string or file
			$view_output = ""; 
			if (substr($view, 0, 4 )=='http') {
				$view_output = @file_get_contents($view);
			} else if (file_exists("core/admin/views/".$view)) {
				$view_output = @file_get_contents("core/admin/views/".$view);
			} else if (file_exists("webapp/themes/".ACTIVE_THEME."/views/".$view)) {
				$view_output = @file_get_contents("webapp/themes/".ACTIVE_THEME."/views/".$view);
			} else {
				// when view is a file, fetch content
				$view_output = $view;
			}
	
			/* process subview arrays, syntax: 
			[for:blocks]
				[blocks.x]
				[blocks.y]
				[blocks.z]
				[blocks.t]
			[end:blocks]
			*/
			foreach ($global_models as $var => $data) {
				if ((strpos($var, '[') !== false) && (strpos($var, ']') !== false) && (strlen($var) >= 3) ) {
					$tmp_var = explode('[', $var);
					$new_var = array_shift($tmp_var);

					$var_ex = explode('.', $new_var);
					$var_prefix = '';
					$var_sufix = '';
					if (count($var_ex) == 3) {
						$var_prefix = $var_ex[0].'.'.$var_ex[1];
						$var_sufix = $var_ex[2];
					} elseif (count($var_ex) == 2) {
						$var_prefix = $var_ex[0];
						$var_sufix = $var_ex[1];
					} elseif (count($var_ex) == 1) {
						$var_prefix = $var_ex[0];
					}
					
					
					if (!isset($global_models[$var_prefix]) || !is_array($global_models[$var_prefix])) {
						$global_models[$var_prefix] = array();
					}
					$global_models[$var_prefix][$var_sufix][] = $data;
					unset($global_models[$var]);
				}
			}

			// process shared models (variables)
			foreach ($global_models as $var => $data) {
				// when model data is an array
				if (is_array($data)) {
					// fetch for blocks and render loops
					$forblocks = array();
					preg_match_all('/(?<block>\[for:'.$var.'\](?<content>[\s\S]+)\[end:'.$var.'\])/ix', $view_output, $forblocks, PREG_SET_ORDER);
					if (count($forblocks)) {
						foreach ($forblocks as $foundForBlock) {
							$block_content = array();
							foreach ($data as $mykey => $row) {
								// set model values within the loop, ex: blocks.x value
								foreach ($row as $subvar => $value) {
									if (is_numeric($subvar)) {
										if (!isset($block_content[$subvar])) $block_content[$subvar] = $foundForBlock['content'];
										if (!is_array($value)) {
											$block_content[$subvar] = str_replace('['.$var.'.'.$mykey.']', $value, $block_content[$subvar]);
										}
									} else {
										if (!is_array($value)) {
											if (!isset($block_content[$mykey])) $block_content[$mykey] = $foundForBlock['content'];
											$block_content[$mykey] = str_replace('['.$var.'.'.$subvar.']', $value, $block_content[$mykey]);
										}
									}
								}
								// append the parsed new block (of for loop) as processed view to render (ifs and setters for example)
							}
							$block_content = implode("\n", $block_content);
							$view_output = str_replace($foundForBlock['block'], $block_content, $view_output);
						}
					}
				} else {
					// simple model, replace model with value ex: "[stats.x]" by "18"
					$view_output = str_replace('['.$var.']', $data, $view_output);
				}
			}

/*

<?php

function getQuery($items) {
        $sql = "select
            id,\n";

        foreach ($items as $item) {
            $sql = $sql . "max(if(concat(namespace, '.', `key`) = '" . $item . "', `value`, null)) as `" . $item . "`,\n";
        }
        $sql = $sql . "from
            `data`
        group by
            id;";

        return $sql;
}

echo getQuery([
        "page.item.id",
        "page.content.title",
        "trigger.tag",
        "oddball.num"
]);

?>

*/
			/* process model setters, ex: [set:stats.x]18[endset] */
			$setvars = array();
			preg_match_all('~(?<block>\[set:(?<set_body>[^\[]+)\](?<set_content>[^\[.]+)\[endset\])~i', $view_output, $setvars, PREG_SET_ORDER);
			if (count($setvars) > 0) {
				foreach ($setvars as $key => $found) {
					if (isset($found['set_body']) && trim($found['set_body'])!='') {
						if (trim($found['set_content'])=='++') {
							// when setter is ++ increment value of model
							$controller->addModel(trim($found['set_body']), ((int)trim($controller->getModel(trim($found['set_body']))))+1);
							$global_models[trim($found['set_body'])] = ((int)trim($controller->getModel(trim($found['set_body']))))+1;
						} else {
							// otherwise, set value of model to content body
							$controller->addModel(trim($found['set_body']), $found['set_content']);
							$global_models[trim($found['set_body'])] = $found['set_content'];
						}
						// remove found block from output as model has been processed
						$view_output = str_replace($found['block'], '', $view_output);
						// re-render models as new models were added.
						$view_output = $this->process_view($controller, $view_output, $recursion_level + 1);
					} elseif(isset($found['set_body'])) {
						$controller->addModel(trim($found['set_body']), '');
						$global_models[trim($found['set_body'])] = '';
						// re-render models as new models were added.
						$view_output = $this->process_view($controller, $view_output, $recursion_level + 1);
					}
				}
			}
			
			// process if statements
			$ifsfound = array();
		    preg_match_all('~(?<block>\[if:(?<if_body>[^\]]+)\](?<body>.+)\[endif\])~siU', $view_output, $ifsfound, PREG_SET_ORDER);
			if (count($ifsfound) > 0) {
				foreach ($ifsfound as $found) {
					$eval_code = 'return ('.$found['if_body'].');';

					// MUST REMOVE EVAL - INPUT DANGER					
					if (eval($eval_code)) {
						//valid if statement
						$view_output = str_replace($found['block'], $found['body'], $view_output);
						// re-render everything within valid if statement
						$view_output = $this->process_view($controller, $view_output, $recursion_level + 1);
					} else {
						// invalid if statement, erase it from view output
						$view_output = str_replace($found['block'], '', $view_output);
					}
				}
			}
			
			// process translations, syntax: t[text]
			$matches = array();
			preg_match_all('/t\[[^\[]*[^\\\]\]/i', $view_output, $matches);
			foreach ($matches as $found_a) {
				foreach ($found_a as $found) {
					$translate = substr($found, 2, -1);
					$view_output = str_replace($found, t($translate), $view_output);
				}
			}



			
			// process subview paths, syntax: [filename]
			$matches = array();
			/*                 /\[[^\[]*[^\\\]\]		*/
			preg_match_all('/\[\S*\]/i', $view_output, $matches);
			foreach ($matches as $found_a) {
				foreach ($found_a as $found) {
					if (strlen($found)>=3) {
						$filename = trim(substr($found, 1, -1));
						if (substr($filename, 0, 4)=='http') {
							// file is a web fetch
							$view_output = str_replace($found, $this->process_view($controller, $filename, $recursion_level+1), $view_output);
						} else if (file_exists("core/admin/views/".$filename)) {
							// file is an admin file
							$view_output = str_replace($found, $this->process_view($controller, $filename, $recursion_level+1), $view_output);
						} else if (file_exists("webapp/themes/".ACTIVE_THEME."/views/".$filename)) {
							// file is an webapp file
							$view_output = str_replace($found, $this->process_view($controller, $filename, $recursion_level+1), $view_output);
						}
					}
				}
			}

			return $view_output;

		}

		/* Main process thread */
		public function process() {
			global $main_path;
			global $docX;
			global $global_models;
			global $view_complete_output;
			// validate and assign priorities to controllers
			$priority_controllers = array();
			foreach ($this->obj_controllers as $cname => $controller) {
				// ensure controller validates when a validation function is found
				if (method_exists($controller, 'validate')) {
					$validator_priority = $controller->validate();
					if ($validator_priority !== false) {
						// when controller validates
						if (!is_numeric($validator_priority)) $validator_priority = 0; // when priority isn't numeric assign zero value
						$priority_controllers[$cname] = $validator_priority;	// assign priority to unique class name
					}
				}
			}

			function prioritySorter($a, $b) {
				if ($a == $b) return 0;
				return ($a > $b) ? -1 : 1;
			}

			// sort controllers by highest priority to lowest
			uasort($priority_controllers, 'prioritySorter');

			// index of controllers
			$controller_index=0;
			
			$output_buffer = '';
			$injections = array();
			
			foreach ($priority_controllers as $cname => $priority) {
				
				// find controller of class name
				$controller = $this->obj_controllers[$cname];
				$controller->index = $controller_index;
				$controller_index++;

				// execute if executable is found
				if (method_exists($controller, 'execute')) $controller->execute();

				// add models to shared models
				foreach ($controller->models as $key => $model) {
					$global_models[$key] = $model;
				}
				
				// render each view of controller
				$view_complete_output .= '';
				foreach($controller->views as $view) {
					$view_complete_output .= $this->process_view($controller, $view); 
					$output_buffer .= $view_complete_output;
				}

				// re-add models to shared models
				foreach ($controller->models as $key => $model) {
					$global_models[$key] = $model;
				}
				
				foreach($controller->injected_views as $injected_view) {
					$injections[] = $injected_view;
				}
			}
			
			$docX = phpQuery::newDocument($output_buffer);
/*			
				"content" => "",			// content to inject
				"file_contents" => "",		// ajax content to inject, overrides content (url/filename)
				"placeholder" => "body",	// selector: #id.class
				"display" => "html",		// or text
				"filter" => "",				// selector: #id.class
				"mode" => "append"			// or prepend, replace
				"javascript" => "no"		// render js yes or no
*/

	foreach($injections as $injected_view) {
				$content = $injected_view["content"];
				$file_contents = $injected_view["file_contents"];
				$placeholder = $injected_view["placeholder"];
				$display = $injected_view["display"];
				$mode = $injected_view["mode"];
				$filter = $injected_view["filter"];
				$js = $injected_view["javascript"];
				
				$injected_output = '';
				$docZ = '';
				if (trim($file_contents) != '') {
					
					if ($js == "yes") {
						$client = Client::getInstance();
						
						$client->getEngine()->setPath($main_path.'core/composer/bin/phantomjs');
						
						$client->getEngine()->addOption('--ssl-protocol=any');
						$client->getEngine()->addOption('--ignore-ssl-errors=true');
						$client->getEngine()->addOption('--web-security=false');
						$client->getEngine()->addOption('--debug=true');
						$client->getEngine()->addOption('--local-to-remote-url-access=true');

						$request = $client->getMessageFactory()->createRequest("http://perdu.com");
						$response = $client->getMessageFactory()->createResponse();
						 
						$client->send($request, $response);
						var_dump($response);
						var_dump($response->getStatus());
						if($response->getStatus() === 200) {
							$resp = $response->getContent();
							var_dump($resp);
							$docZ = phpQuery::newDocument($resp);
						}
						
					} else {
						$docZ = phpQuery::newDocument(@file_get_contents($file_contents));
					}				
					// fetch ajax
				} elseif (trim($content) != '') {
					// fetch simple content
					$docZ = phpQuery::newDocument($content);
				}
				
				$mydoc = "";
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
				var_dump($injected_output);
				// mode of output
				switch ($mode) {
					case 'append':
						$docX[$placeholder]->append($injected_output);
						break;
					case 'prepend':
						$docX[$placeholder]->prepend($injected_output);
						break;
					case 'replace':
						$docX[$placeholder]->html($injected_output);
						break;
				}

			}
			
			echo $docX->getDocument();
		}
		
	}
	

	// Run Core
	global $core;
	$core = new Core();
	$core->process();
?>