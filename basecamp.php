<?php

// For all the fetchin'
require_once 'HTTP/Request.php';
require_once 'XML/Serializer.php';
require_once 'XML/Unserializer.php';

class Basecamp {
	
	var $user;
	var $pass;
	
	var $base;

	function Basecamp($user, $pass, $base) {
		$this->user = $user;
		$this->pass = $pass;
		$this->base = $base;
	}

	/*======================/
	 * 	General
	/*=====================*/
	
	// Returns the info for the company referenced by id
	function company($id) {
		return $this->hook("/contacts/company/{$company_id}","company");
	}
	
	// This will return an alphabetical list of all message categories in the referenced project.
	function message_categories($project_id) {
		return $this->hook("/projects/{$project_id}/post_categories","post-category");
	}
	
	// This will return an alphabetical list of all file categories in the referenced project.
	function file_categories($project_id) {
		return $this->hook("/projects/{$project_id}/attachment_categories","attachment-category");
	}

	// This will return all of the people in the given company. 
	// If a project id is given, it will be used to filter the set of people 
	// that are returned to include only those that can access the given project. 
	function people($company_id) {
		return $this->hook("/contacts/people/{$company_id}","person");
	}
	
	// This will return all of the people in the given company that can access the given project.
	function people_per_project($project_id, $company_id) {
		return $this->hook("/projects/{$project_id}/contacts/people/{$company_id}","person");
	}

	// This will return information about the referenced person.
	function person($person_id) {
		return $this->hook("/contacts/person/{$person_id}","person");
	}

	// This will return a list of all active, 
	// on-hold, and archived projects that you have access to. The list is not ordered.
	function projects() {
	  	return $this->hook("/project/list","project");
	}


	/*======================/
	 * 	Messages and Comments
	/*=====================*/
	
	// Retrieve a specific comment by its id.
	function comment($comment_id) {
		return $this->hook("/msg/comment/{$comment_id}","comment");
	}

	// Return the list of comments associated with the specified message.
	function comments($comment_id) {
		return $this->hook("/msg/comments/{$message_id}","comment");
	}

	// Create a new comment, associating it with a specific message.
	function create_comment($comment) {
		return $this->hook("/msg/create_comment","comment",array("comment" => $comment));
	}
	
	// Creates a new message, optionally sending notifications to a selected list of people. 
	// Note that you can also upload files using this function, but you need to upload the 
	// files first and then attach them. See the description at the top of this document for more information.
	// notify should be an array of people_id's
	function create_message($project_id, $message, $notify = false) {
		$request['post'] = $message;
		if ($notify) {$request['notify'] = $notify;}
		return $this->hook("/projects/{$project_id}/msg/create","post",$request);
	}
	
	// Delete the comment with the given id.
	function delete_comment($comment_id) {
		return $this->hook("/msg/delete_comment/{$comment_id}","comment");
	}
	
	// Delete the message with the given id.
	function delete_message($message_id) {
		return $this->hook("/msg/delete/{$message_id}","post");
	}
	
	// This will return information about the referenced message. 
	// If the id is given as a comma-delimited list, one record will be 
	// returned for each id. In this way you can query a set of messages in a 
	// single request. Note that you can only give up to 25 ids per request--more than that will return an error.
	function message($message_ids) {
		return $this->hook("/msg/get/{$message_ids}","post");
	}
	
	// This will return a summary record for each message in a project. 
	// If you specify a category_id, only messages in that category will be returned. 
	// (Note that a summary record includes only a few bits of information about a post, not the complete record.)
	function message_archive($project_id,$category_id = false) {
		$request['post']['project-id']  = $project_id;
		if ($category_id) { $request['post']['category-id'] = $category_id; }
		return $this->hook("/projects/{$project_id}/msg/archive","post",$request);
	}
	
	// This will return a summary record for each message in a particular category. 
	// (Note that a summary record includes only a few bits of information about a post, not the complete record.)
	function message_archive_per_category($project_id,$category_id) {
		return $this->hook("/projects/{$project_id}/msg/cat/{$category_id}/archive","post");
	}
	
	
	// Update a specific comment. This can be used to edit the content of an existing comment.
	function update_comment($comment_id,$body) {
		$comment['comment_id'] = $comment_id;
		$comment['body'] = $body;
		return $this->hook("/msg/update_comment","comment",$comment);
	}
	
	// Updates an existing message, optionally sending notifications to a selected list of people. 
	// Note that you can also upload files using this function, but you have to format the request 
	// as multipart/form-data. (See the ruby Basecamp API wrapper for an example of how to do this.)
	function update_message($message_id,$message,$notify = false) {
		$request['post'] = $message;
		if ($notify) {$request['notify'] = $notify;}
		return $this->hook("/msg/update/{$message_id}","post",$request);
	}
	
	
	
	/*======================/
	 * 	Todo Lists and Items
	/*=====================*/
	
	// Marks the specified item as "complete". If the item is already completed, this does nothing. 
	function complete_item($item_id) {
		return $this->hook("/todos/complete_item/{$item_id}","todo-item");
	}
	
	
	// This call lets you add an item to an existing list. The item is added to the bottom of the list. 		
	// 	If a person is responsible for the item, give their id as the party_id value. If a company is 
	// 	responsible, prefix their company id with a 'c' and use that as the party_id value. If the item 
	// 	has a person as the responsible party, you can use the notify key to indicate whether an email 
	// 	should be sent to that person to tell them about the assignment.	
	function create_item($list_id, $item, $responsible_party = false, $notify_party = false) {
		$request['content'] = $item;
		if ($responsible_party) {
			$request['responsible_party'] = $responsible_party;
			$request['notify'] = ($notify_party)?"true":"false";
		}
		return $this->hook("/todos/create_item/{$list_id}","todo-item",$request);
	}
	
	// This will create a new, empty list. You can create the list explicitly, 
	// or by giving it a list template id to base the new list off of.
	function create_list($project_id,$list) {
		return $this->hook("/projects/{$project_id}/todos/create_list","todo-list",$list);
	}
	
	// Deletes the specified item, removing it from its parent list.
	function delete_item($item_id) {
		return $this->hook("/todos/delete_item/{$item_id}","todo-item");
	}
	
	// This call will delete the entire referenced list and all items associated with it. 
	// Use it with caution, because a deleted list cannot be restored!
	function delete_list($list_id) {
		return $this->hook("/todos/delete_list/{$list_id}","todo-list");
	}
	
	// This will return the metadata and items for a specific list.
	function list_items($list_id) {
		return $this->hook("/todos/list/{$list_id}","todo-list");
	}
	
	// This will return the metadata for all of the lists in a given project. 
	// You can further constrain the query to only return those lists that are "complete"
	// (have no uncompleted items) or "uncomplete" (have uncompleted items remaining).
	function lists($project_id, $complete = false) {
		$request['complete'] = ($complete)?"true":"false";
		return $this->hook("/projects/{$project_id}/todos/lists","todo-list", $request);
	}
	
	// Changes the position of an item within its parent list. It does not currently 
	// support reparenting an item. Position 1 is at the top of the list. Moving an 
	// item beyond the end of the list puts it at the bottom of the list.
	function move_item($item_id,$to) {
		return $this->hook("/todos/move_item/{$item_id}","todo-item",array('to' => $to));
	}
	
	// This allows you to reposition a list relative to the other lists in the project.
	// A list with position 1 will show up at the top of the page. Moving lists around lets 
	// you prioritize. Moving a list to a position less than 1, or more than the number of 
	// lists in a project, will force the position to be between 1 and the number of lists (inclusive).
	function move_list($list_id,$to) {
		return $this->hook("/todos/move_list/{$list_id}","todo-list",array('to' => $to));
	}
	
	// Marks the specified item as "uncomplete". If the item is already uncompleted, this does nothing. 
	function uncomplete_item($item_id) {
		return $this->hook("/todos/uncomplete_item/{$item_id}","todo-item");
	}
	
	// Modifies an existing item. 
	// The values work much like the "create item" operation, so you should refer to that for a more detailed explanation.
	function update_item($item_id, $item, $responsible_party = false, $notify_party = false) {
		$request['item']['content'] = $item;
		if ($responsible_party) {
			$request['responsible_party'] = $responsible_party;
			$request['notify'] = ($notify_party)?"true":"false";
		}
		return $this->hook("/todos/update_item/{$item_id}","todo-item",$request);
	}
	
	// With this call you can alter the metadata for a list.
	function update_list($project_id,$list) {
		return $this->hook("/todos/update_list/{$list_id}","todo-list",array('list' => $list));
	}
	
	/*======================/
	 * 	Milestones
	/*=====================*/
	
	// Marks the specified milestone as complete.
	function complete_milestone($milestone_id) {
		return $this->hook("/milestones/complete/{$milestone_id}","milestone");
	}
	
	// Creates a single or multiple milestone(s). 
	function create_milestones($project_id,$milestones) {
		return $this->hook("/projects/{$project_id}/milestones/create","milestone",array("milestone" => $milestone));
	}
	
	// Deletes the given milestone from the project.
	function delete_milestone($milestone_id) {
		return $this->hook("/milestones/delete/{$milestone_id}","milestone");
	}
	
	// This lets you query the list of milestones for a project. 
	// You can either return all milestones, or only those that are late, completed, or upcoming.
	function list_milestones($project_id, $find = "all") {
		return $this->hook("/projects/{$project_id}/milestones/list","milestone",array('find' => $find));
	}
	
    // Modifies a single milestone. You can use this to shift the deadline of a single milestone, 
	// and optionally shift the deadlines of subsequent milestones as well. 
	function uncomplete_milestone($milestone_id) {
		return $this->hook("/milestones/uncomplete/{$milestone_id}","milestone");
	}
	
	// Creates a single or multiple milestone(s). 
	function update_milestone($milestone_id,$milestone,$move_upcoming_milestones = true,$move_upcoming_milestones_off_weekends = true) {
		$request['milestone'] = $milestone;
		$request['move-upcoming-milestones'] = $move_upcoming_milestones;
		$request['move-upcoming-milestones-off-weekends'] = $move_upcoming_milestones_off_weekends;
		return $this->hook("/milestones/update/{$milestones_id}","milestone",array("milestone" => $milestone));
	}
	
	
	/*===================/
	 * 	The Worker Bees  
	/*===================*/
	
	function hook($url,$expected,$send = false) {
		$returned = $this->unserialize($this->request($url,$send));
		$placement = $expected;
		if (isset($returned->{$expected})) {
			$this->{$placement} = $returned->{$expected};	
			return $returned->{$expected};
		} else {
			$this->{$placement} = $returned;
			return $returned;
		}
	}
	
	function request($url, $params = false) {
		//do the connect to a server thing
		$req =& new HTTP_Request($this->base . $url);
		//authorize
		$req->setBasicAuth($this->user, $this->pass);
		//set the headers
		$req->addHeader("Accept", "application/xml");
		$req->addHeader("Content-Type", "application/xml");
		//if were sending stuff
		if ($params) {
			//serialize the data
			$xml = $this->serialize($params);
			//print_r($xml);
			($xml)?$req->setBody($xml):false;
			$req->setMethod(HTTP_REQUEST_METHOD_POST);
		}
		$response = $req->sendRequest();
		//print_r($req->getResponseHeader());
		//echo $req->getResponseCode() .	"\n";
		
		if (PEAR::isError($response)) {
		    return $response->getMessage();
		} else {
			//print_r($req->getResponseBody());
		    return $req->getResponseBody();
		}
	}
	
	function serialize($data) {
		$options = array(	XML_SERIALIZER_OPTION_MODE => XML_SERIALIZER_MODE_SIMPLEXML,
						 	XML_SERIALIZER_OPTION_ROOT_NAME   => 'request',
							XML_SERIALIZER_OPTION_INDENT => '  ');
		$serializer = new XML_Serializer($options);
		$result = $serializer->serialize($data);
		return ($result)?$serializer->getSerializedData():false;
	}
	
	function unserialize($xml) {
		$options = array (XML_UNSERIALIZER_OPTION_COMPLEXTYPE => 'object');
		$unserializer = &new XML_Unserializer($options);
		$status = $unserializer->unserialize($xml);    
	    $data = (PEAR::isError($status))?$status->getMessage():$unserializer->getUnserializedData();
		return $data;
	}
}

// match comments
// \/\/ .+


?>