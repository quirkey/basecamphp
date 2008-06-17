<?php

require 'basecamp.php';

class Customcamp extends Basecamp {

	/*======================/
	 * 	Text Find
	/*=====================*/
	function find_by_id($id,$items) {
		for ($i=0;$i<count($items);$i++) {
			if ($items[$i]->id == $id) {
				return $items[$i];
			}
		}
		
		return false;
	}
	
	
	function find_by_name($item_type, $item_name) {
		$results = array();
		$pattern = "/{$item_name}/i";
		$search_in = ($item_type == 'post' || $item_type == 'milestone')?"title":"name";
		if (isset($this->{$item_type})) {
			if (is_array($this->{$item_type})) {
				foreach ($this->{$item_type} as $item) {
					if (preg_match($pattern,$item->{$search_in})) {
						$results[] = $item;
					}
				}
			} else {
				if (preg_match($pattern,$this->{$item_type}->{$search_in})) {
					$results[] = $item;
				}
			}
		}
		
		return $results;
	}

	
	function load_projects_with_lists() {
		$projects = $this->projects();
		for ($i=0;$i < count($projects);$i++) {
			$lists = $this->lists($projects[$i]->id);
			$projects[$i]->lists = (is_array($lists))?$lists:array($lists);
		}
		return $projects;
	}
	
	
	function find_all_uncomplete_todos($in_project = false) {
		if ($in_project) {
			$projects[] = $in_project;
		} else {
			$projects = $this->projects();
			//print_r($projects);
		}		
		$to_dos = array();
		$i=0;
		foreach ($projects as $project) {
			$lists = $this->lists($project->id);
			foreach ($lists as $list) {
				$list_data = $this->list_items($list->id);
				if ($list_data->{"todo-items"}->{"todo-item"}) {
					foreach ($list_data->{"todo-items"}->{"todo-item"} as $todo) {
						if ($todo->completed == "false") {
							$todo->in_project = $project;
							$todo->in_list = $list;
							// calculate rank
							$todo->rank = $list->position * $todo->position * ($this->convert_ruby_timestamp($project->{"last-modified-on"}) / time(+1));
							//this is just so they all have unique keys
							$todo->rank += (rand(0,50) * .000001);
							if ($i) { print_r($todo); $i = false; }
							$to_dos["$todo->rank"] = $todo;
						}
					}
				}
			}
		}
		ksort($to_dos);
		return $to_dos;
	}
	
	// ruby timestamp ex
	// 2006-04-27T03:49:31Z
	function convert_ruby_timestamp($timestamp) {
		$chopped = str_replace(array('T','Z'),array(' ',' GMT'),$timestamp);
		return (strtotime($chopped));
	}
	
	
	
}

//testing


?>