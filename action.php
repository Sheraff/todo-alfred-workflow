<?

//data
require_once('workflows.php');
$w = new Workflows();
$cache = $_SERVER['HOME']."/Library/Caches/com.runningwithcrayons.Alfred-2/Workflow Data/florian.todo";
$query = $argv[1];
$tasks = file_exists("$cache/tasks.md")?parse_tasks(file("$cache/tasks.md")):[];

if(strpos($query, "__check") === 0){
	$index = explode(" ", $query);
	$tasks[$index[1]]["project_items"][$index[2]]["item_compl"] = !$tasks[$index[1]]["project_items"][$index[2]]["item_compl"];
} elseif (strpos($query, "__clear") === 0) {
	for ($i=0, $l=count($tasks); $i < $l; $i++) {
		$task = $tasks[$i];
		for ($j=0, $m=count($task["project_items"]); $j < $m; $j++) {
			$item = $task["project_items"][$j];
			if($item["item_compl"]){
				unset($tasks[$i]["project_items"][$j]);
			}
		}
		if(count($tasks[$i]["project_items"])==0){
			unset($tasks[$i]);
		}
	}
	var_dump($tasks);

} else {
	$project = explode(": ", $query);
	$description = explode(", ",  count($project)>1 ? substr($query, strlen($project[0])+2) : $query);
	$descr = count($description)>1 ? substr((count($project)>1 ? substr($query, strlen($project[0])+2) : $query), strlen($description[0])+2) : (count($project)>1 ? substr($query, strlen($project[0])+2) : $query);
	$project = count($project)>1?$project[0]:"";
	$title = count($description)>1?$description[0]:"";

	if($project=="")
		$project = "Inbox";

	$found_project = false;
	for ($i=0, $l=count($tasks); $i < $l; $i++) {
		if($tasks[$i]["project_title"] == $project){
			$found_project = true;
			array_push($tasks[$i]["project_items"], [
				"item_title" => "$title",
				"item_descr" => "$descr",
				"item_compl" => false
			]);
			break;
		}
	}
	if(!$found_project){
		array_push($tasks, [
		    "project_title" => "$project",
			"project_items" => [[
				"item_title" => "$title",
				"item_descr" => "$descr",
				"item_compl" => false
			]]
		]);
	}
}

var_dump($tasks);

file_put_contents("$cache/tasks.md", tasks_in_md($tasks));

function tasks_in_md($tasks_object){
	$tasks_str = "";
	foreach ($tasks_object as $task) {
		$tasks_str.="\n# ".$task["project_title"];
		foreach ($task["project_items"] as $item) {
			var_dump($item);
			$tasks_str.="\n ".($item["item_compl"]?"+":"-")." ".($item["item_title"]?"**".$item["item_title"]."**: ":"").$item["item_descr"];
		}
	}
	return $tasks_str;
}

function parse_tasks($tasks_array){
	$tasks_object = [];
	for ($i=0, $l=count($tasks_array); $i < $l; $i++) {
		if(strpos($tasks_array[$i], "#") === 0){
			array_push($tasks_object, [
				"project_title" => rtrim(substr($tasks_array[$i], 2)),
				"project_items" => []
			]);
		} elseif (strpos($tasks_array[$i], " - ") === 0 || strpos($tasks_array[$i], " + ") === 0) {
			preg_match('/(?<=(\*\*)).+(?=(\*\*: ))/', $tasks_array[$i], $title);
			$title = count($title)>0?$title[0]:false;
			$descr = explode("**: ", $tasks_array[$i]);
			$descr = rtrim(count($descr)>1?$descr[1]:substr($tasks_array[$i], 3));
			$compl = substr($tasks_array[$i], 1, 1) === "+";
			if($compl){ global $some_are_done; $some_are_done++; }
			array_push($tasks_object[count($tasks_object)-1]["project_items"], [
				"item_title" => $title,
				"item_descr" => $descr,
				"item_compl" => $compl
			]);
		}
	}
	return $tasks_object;
}

?>