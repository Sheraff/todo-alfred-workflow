<?

//globals
$some_are_done = 0;

//data
require_once('workflows.php');
$w = new Workflows();
$cache = $_SERVER['HOME']."/Library/Caches/com.runningwithcrayons.Alfred-2/Workflow Data/florian.todo";
$query = $argv[1];
$tasks = file_exists("$cache/tasks.md")?parse_tasks(file("$cache/tasks.md")):[];

if(count($tasks)==0 && strlen($query)==0){
	$w->result( '', '', "Nothing to do on your list...", "type to add an item to your todo list", 'icon.png', "NO", '' );
}

if(strlen($query)>0){
	$project = explode(": ", $query);
	$description = explode(", ",  count($project)>1?substr($query, strlen($project[0])+2):$query);
	$subtitle = "create new item".(count($project)>1?" in project \"$project[0]\"":"").(count($description)>1?" with title \"$description[0]\"":"").(count($project)<2&&count($description)<2?" (format as \"project: title, text\")":"");
	$title = count($description)>1?substr((count($project)>1?substr($query, strlen($project[0])+2):$query), strlen($description[0])+2):(count($project)>1?substr($query, strlen($project[0])+2):$query);
	$w->result( '', $query, ($title==""?"...":$title), $subtitle, 'icons_plus.png', "YES", '' );
}

if($some_are_done>0)
	$w->result('', "__clear", "Clear all completed tasks", "removing $some_are_done task".($some_are_done>1?"s":""), 'icons_close.png', "YES", '' );

for ($i=0, $l=count($tasks); $i < $l; $i++) {
	$task = $tasks[$i];
	for ($j=0, $m=count($task["project_items"]); $j < $m; $j++) {
		$item = $task["project_items"][$j];
		//filter by query
		$w->result( '', "__check $i $j", $task["project_title"].($item["item_title"]?": ".$item["item_title"]:""), $item["item_descr"], ($item["item_compl"]?"icon.png":"icons_empty.png"), "YES", $task["project_title"].": " );
	}
}
echo $w->toxml();


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