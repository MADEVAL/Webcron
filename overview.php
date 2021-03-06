<?php

/* 
 * The MIT License
 *
 * Copyright 2017-2018 Jeroen De Meerleer <me@jeroened.be>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

require_once "include/initialize.inc.php";

$message = "";
if (isset($_GET['action'])) {
    $jobID = $_GET['jobID'];
    if ($_GET['action'] == "delete") {
        $deletestmt = $db->prepare("DELETE FROM jobs WHERE jobID = ? ");
        $deletestmt->execute(array($jobID));
        $delete2stmt = $db->prepare("DELETE FROM runs WHERE job = ? ");
        $delete2stmt->execute(array($jobID));
        $message = "Job was sucessfully deleted";
    }
}
    
if (isset($_GET["message"])) {
    $message = "";
    switch ($_GET["message"]) {
        case "edited":
            $message = "The cronjob has been edited"; break;
    }
}

$allJobs = $db->prepare("SELECT * FROM jobs WHERE user = ? ORDER BY name ASC");
$allJobs->execute(array($_SESSION["userID"]));
$allJobsResult = $allJobs->fetchAll(PDO::FETCH_ASSOC);

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array('cache' => 'cache', "debug" => true));

//var_dump($alljobsResult);
//exit;

$allJobsRendered = array();$count = 0;
foreach($allJobsResult as $key=>$value) {
    $allJobsRendered[$count]["jobID"] = $value["jobID"];
    $allJobsRendered[$count]["name"] = $value["name"];
    $allJobsRendered[$count]["host"] = $value["host"];
    $allJobsRendered[$count]["nextrun"] = date("d/m/Y H:i:s", $value["nextrun"]);
    $allJobsRendered[$count]["delay"] = secondsToInterval($value["delay"]);
    
    $count++;
}

$twig_vars = array('jobs' => $allJobsRendered, 'message' => $message);

echo $twig->render('overview.html.twig', $twig_vars);


require_once 'include/finalize.inc.php';

function secondsToInterval($time) {
    $days = floor($time / (60 * 60 * 24));
    $time -= $days * (60 * 60 * 24);

    $hours = floor($time / (60 * 60));
    $time -= $hours * (60 * 60);

    $minutes = floor($time / 60);
    $time -= $minutes * 60;

    $seconds = floor($time);
    $time -= $seconds;

    return "{$days}d {$hours}h {$minutes}m {$seconds}s";
}