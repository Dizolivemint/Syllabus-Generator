<?php

function generate_exec_sum_select_list($link)
{
	$user_id = $_SESSION['id'];
	$term_codes = array('', 'WI', 'SP', 'SU', 'FA' );
	
	$query = "SELECT
	classes.id,
	terms.term,
	terms.year,
	class_details.sectnum,
	courses.coursenum,
	courses.name
FROM
	classes,
	terms,
	class_details,
	courses
WHERE
	classes.id = class_details.class_id AND
	classes.course_id = courses.id AND
	classes.term_id = terms.id AND
	classes.user_id = '$user_id' AND
	classes.status = 2 AND
	classes.exec_sum = 0
order by terms.year desc, terms.term desc";

	$result = mysqli_query($link, $query);
	$numrows = mysqli_num_rows($result);
	
	if( $numrows > 0 )
	{
		print "<select name='courseselect'>";
		print "<option value=''>Please Select A Class</option>";
		while( $row = mysqli_fetch_row($result) )
		{
			list($class_id, $term_num, $term_yr, $sectnum, $coursenum, $coursename) = $row;
			$the_term = $term_codes[$term_num];
			print "<option value='$class_id'>$the_term $term_yr $coursenum $sectnum $coursename</option>";
		}
		print "</select>";
		print "<input type='submit' name='create-exec-sum' value='Create Executive Summary'>";
	}
	else
	{
		print "<p>You don't have any classes available.</p>";
	}
}

function get_class_id()
{
	if( isset($_POST['courseselect']) )
	{
		$class_id = $_POST['courseselect'];
		return $class_id;
	}
	elseif( isset( $_GET['view'] ) )
	{
		$class_id = $_GET['view'];
		return $class_id;
	}
	elseif( isset( $_GET['edit'] ) )
	{
		$class_id = $_GET['edit'];
		return $class_id;
	}
}

function exec_sum_owner($link, $class_id)
{
	$user_id = $_SESSION['id'];
	$query = "select user_id from classes where id = '$class_id'";
	$result = mysqli_query($link, $query);
	$row = mysqli_fetch_row($result);
	if( $row[0] == $user_id ) { return TRUE; }
	else { return FALSE; }
}

function class_details($link, $item, $class_id)
{
	$term_names = array('', 'Winter', 'Spring', 'Summer', 'Fall');
	
	$query="SELECT
	classes.id,
	courses.coursenum,
	courses.name,
	courses.description,
	terms.term,
	terms.year,
	class_details.sectnum
FROM
	classes,
	courses,
	terms,
	class_details
WHERE
	classes.course_id = courses.id AND
	classes.term_id = terms.id AND
	classes.id = class_details.class_id AND
	classes.id = '$class_id'";
	
	$result = mysqli_query($link, $query);
	
	$row = mysqli_fetch_row($result);
	list($class_id, $coursenum, $coursename, $coursedescript, $termnum, $year, $sectnum) = $row;
	$term = $term_names[$termnum];
	
	$row = array($class_id, $coursenum, $coursename, $coursedescript, $term, $year, $sectnum);
	
	$field_names = array("class_id", "coursenum", "course", "description", "term", "year", "section");
	$syllabus_info = array_combine($field_names, $row);
	$the_item = $syllabus_info[$item];
	return $the_item;
	
}

function add_exec_summary($link)
{
	if( isset($_POST['add-summary']) )
	{
		if( !empty( $_POST['summary'] ) || 
		!empty( $_POST['strengths'] ) || 
		!empty( $_POST['challenges'] )|| 
		!empty( $_POST['grades']))
		{
			$priv_pub = mysql_prep($link, $_POST['priv_pub']);
			$class_id = mysql_prep($link, $_POST['classid']);
			$summary = mysql_prep($link, $_POST['summary']);
			$strengths = mysql_prep($link, $_POST['strengths']);
			$challenges = mysql_prep($link, $_POST['challenges']);
			$grades = mysql_prep($link, $_POST['grades']);
			
			$query = "select count(id) from classes where id = '$class_id' and exec_sum > 0";
			$result = mysqli_query($link, $query);
			$row = mysqli_fetch_row($result);
			
			if( $row[0] == 0 )
			{
				$query = "insert into exec_sum values('', '$class_id', '$priv_pub', '$summary', 
				'$strengths', '$challenges', '$grades')";
				mysqli_query($link, $query);
				
				$lastid = mysqli_insert_id($link);
				
				$query = "update classes set exec_sum = '$priv_pub' where id = '$class_id'";
				mysqli_query($link, $query);
				
				if( $_POST['email-summary'] != '' )
				{
					email_exec_summary($link, $lastid);
				}
			}
		}
	}
}

function update_exec_summary($link)
{
	if( isset($_POST['update-summary']) )
	{
		if( !empty( $_POST['summary'] ) || 
		!empty( $_POST['strengths'] ) || 
		!empty( $_POST['challenges'] )|| 
		!empty( $_POST['grades']))
		{
			$exec_sum_id = mysql_prep($link, $_POST['execsumid']);
			$priv_pub = mysql_prep($link, $_POST['priv_pub']);
			$class_id = mysql_prep($link, $_POST['classid']);
			$summary = mysql_prep($link, $_POST['summary']);
			$strengths = mysql_prep($link, $_POST['strengths']);
			$challenges = mysql_prep($link, $_POST['challenges']);
			$grades = mysql_prep($link, $_POST['grades']);
			
			$query = "select count(id) from classes where id = '$class_id' and exec_sum > 0";
			$result = mysqli_query($link, $query);
			$row = mysqli_fetch_row($result);
			
			if( $row[0] == 1 )
			{
				$query = "update exec_sum set priv_pub = '$priv_pub', summary_txt = '$summary', strengths_txt = '$strengths', 
				challenges_txt = '$challenges', grades_txt = '$grades' where id = '$exec_sum_id'";
				//print $query;
				mysqli_query($link, $query);
				
				$query = "update classes set exec_sum = '$priv_pub' where id = '$class_id'";
				mysqli_query($link, $query);
				
				if( $_POST['email-summary'] != '' )
				{
					email_exec_summary($link, $exec_sum_id);
				}
			}
		}
	}
}

function email_exec_summary($link, $id)
{
	if( isset( $_POST['email-summary'] ) )
	{
		if( $_POST['email-summary'] != '' )
		{
			$raw_emails = mysql_prep($link, $_POST['email-summary']);
			$emails = explode(',', $raw_emails);
			$trimmed_emails = array_map('trim', $emails);
			
			$query = "SELECT
			exec_sum.id,
			exec_sum.summary_txt,
			exec_sum.strengths_txt,
			exec_sum.challenges_txt,
			exec_sum.grades_txt,
				courses.coursenum,
				courses.name,
				terms.term,
				terms.year,
				users.fname,
				users.lname,
				users.email
			FROM
				exec_sum,
				classes,
				courses,
				terms,
				users
			WHERE
			exec_sum.class_id = classes.id AND
				classes.course_id = courses.id AND
				classes.user_id = users.id AND
				classes.term_id = terms.id AND
			exec_sum.id = '$id'";
			
		$result = mysqli_query($link, $query);
		$num_rows = mysqli_num_rows($result);
		
		if( $num_rows == 1 )
		{
			$row = mysqli_fetch_row($result);
			list($exec_sum_id, $summary_txt, $strengths_txt, $challenges_txt, $grades_txt, 
			$coursenum, $course, $term, $year, $fname, $lname, $email) = $row;
			
			$term_names = array('', 'Winter', 'Spring', 'Summer', 'Fall');
			$full_term = $term_names[$term];
			
			$subject = "Excutive Summary from the Syllabus Generator";
			
			$message = "<html><body>";
			
			$message .= "
<h3>Executive Summary for $coursenum &mdash; $course</h3>
<p><strong>Term:</strong> $full_term $year</p>
<p><strong>Taught by:</strong> $fname $lname</p>

";
			if($summary_txt != '')
			{
				$message .= "
<h3>Summary:</h3>
$summary_txt

";
			}
			
			if($strengths_txt != '')
			{
				$message .= "
<h3>Strengths:</h3>
$strengths_txt

";
			}
			
			if($challenges_txt != '')
			{
				$message .= "
<h3>Challenges:</h3>
$challenges_txt

";
			}
			
			if($grades_txt != '')
			{
				$message .= "
<h3>Grades:</h3>
$grades_txt

";
			}
			
				$message .= "</body></html>";
			
			foreach($trimmed_emails as $email_address)
			{
				if( preg_match(RE_EMAIL, $email_address) )
				{
					email_user($email_address, $subject, $message, $email);
				}
			}
			
		}//end if one row
	
		}//end if empty
	}//end if isset
}// end function


function exec_sum_list($link, $user_id)
{
	$term_codes = array('', 'WI', 'SP', 'SU', 'FA' );
	
	$query = "SELECT
		classes.id,
		courses.coursenum,
		courses.name,
		terms.term,
		terms.year,
		class_details.sectnum,
		exec_sum.id,
		exec_sum.priv_pub
	FROM
		classes,
		courses,
		terms,
		class_details,
		exec_sum
	WHERE
		classes.id = exec_sum.class_id AND
		classes.course_id = courses.id AND
		classes.term_id = terms.id AND
		classes.id = class_details.class_id AND
		classes.user_id = '$user_id' AND
		classes.exec_sum > 0
	order by year desc, term desc";
	
	$result = mysqli_query($link, $query);
	$numrows = mysqli_num_rows($result);
	
	if($numrows > 0 )
	{
		
		print "<h3>Your Executive Summaries</h3>";
		print "<ol class='execsumlist'>";
		while($row = mysqli_fetch_row($result))
		{
			list($class_id, $coursenum, $coursename, $term, $year, $sectnum, $exec_sum_id, $priv_pub) = $row;
			$full_term = $term_codes[$term];
			
			//print "<article class='frame'>";
			print "<li><a href='execsum.php?view=$class_id&execsum=$exec_sum_id'>$full_term $year &mdash; $coursenum $sectnum  &mdash; $coursename</a> ";
			if($priv_pub == 1) { print "<span class='example'>(private)</span>"; }
			else { print "<span class='example'>(public)</span>"; }
			print "</li>";
			//print "</article>";
		}
		print "</ol>";	
	}
}

function exec_summary_details($link, $item, $class_id)
{
	$query = "select id, class_id, priv_pub, summary_txt, strengths_txt, challenges_txt, grades_txt from exec_sum where class_id = '$class_id'";
	$result = mysqli_query($link, $query);
	$row = mysqli_fetch_row($result);
	
	$field_names = array("exec_sum_id", "class_id", "priv_pub", "summary", "strengths", "challenges", "grades");
	$exec_sum_info = array_combine($field_names, $row);
	$the_item = $exec_sum_info[$item];
	return $the_item;
}

function update_priv_pub_status($link, $exec_sum_id, $class_id)
{
	if( isset( $_POST['change-status']) )
	{
		if( exec_sum_owner($link, $class_id) )
		{
			$priv_pub = $_POST['priv_pub'];
			
			$query = "update exec_sum set priv_pub = '$priv_pub' where id = '$exec_sum_id'";
			mysqli_query($link, $query);
			
			$query = "update classes set exec_sum = '$priv_pub' where id = '$class_id'";
			mysqli_query($link, $query);
		}
	}
}

function delete_exec_sum($link)
{
	if( isset( $_GET['deletesum'] ) )
	{
		$exec_sum_id = $_GET['execsum'];
		$class_id = $_GET['deletesum'];
		
		if( exec_sum_owner($link, $class_id) )
		{
			$query = "delete from exec_sum where id = '$exec_sum_id'";
			mysqli_query($link, $query);
			
			$query = "update classes set exec_sum = '0' where id = '$class_id'";
			mysqli_query($link, $query);
		}
	}
}

function display_public_exec_summaries($link)
{
	$term_codes = array('', 'WI', 'SP', 'SU', 'FA' );
	$query = "SELECT
			classes.id,
			terms.term,
			terms.year,
			users.lname,
			exec_sum.id,
			courses.coursenum,
			courses.name,
			class_details.sectnum
		FROM
			classes,
			terms,
			users,
			exec_sum,
			courses,
			class_details
		WHERE
			classes.id = exec_sum.class_id AND
			classes.id = class_details.class_id AND
			classes.course_id = courses.id AND
			classes.user_id = users.id AND
			classes.term_id = terms.id AND
			classes.exec_sum = 2
		ORDER BY
			terms.year desc,
			terms.term desc";
	
	$results = mysqli_query($link, $query);
	
	print "<ul>";
	
	while( $row = mysqli_fetch_row($results) )
	{
		list( $class_id, $term, $year, $lname, $exec_sum_id, $coursenum, $coursename, $sectnum ) = $row;
		print "<li class='frame'><a href='execsum.php?view=$class_id&execsum=$exec_sum_id'>";
		//print "<span class='ligsymbol'>&#xE020;</span>";
		print " $term_codes[$term] $year &mdash; $coursenum $sectnum <br>$coursename &mdash; $lname";
		print "</a></li>";
	}
	print "</ul>";
	
}

function display_subordinate_exec_summaries($link)
{
	$term_codes = array('', 'WI', 'SP', 'SU', 'FA' );
	$priv_pub_labels = array('', 'Private', 'Public');
	$user_id = $_SESSION['id'];
	$user_type = $_SESSION['type'];
	if( $user_type > 0)
	{
		$query = "SELECT
				classes.id,
				terms.term,
				terms.year,
				users.lname,
				exec_sum.id,
				exec_sum.priv_pub,
				courses.coursenum,
				courses.name,
				class_details.sectnum
			FROM
				classes,
				terms,
				users,
				exec_sum,
				courses,
				class_details
			WHERE
				classes.id = exec_sum.class_id AND
				classes.id = class_details.class_id AND
				classes.course_id = courses.id AND
				classes.user_id = users.id AND
				classes.term_id = terms.id AND
				classes.approvedby = '$user_id' AND
				users.id != '$user_id'
			ORDER BY
				users.lname asc,
				terms.year desc,
				terms.term desc";
				
		$results = mysqli_query($link, $query);
		$numrows = mysqli_num_rows($results);
		
		if( $numrows > 0 )
		{
			print "<h3>Subordinates&rsquo; Executive Summaries</h3>";
			print "<ol>";
			
			while( $row = mysqli_fetch_row($results) )
			{
				list($class_id, $term, $year, $lname, $exec_sum_id, $priv_pub, $coursenum, $coursename, $sectnum) = $row;
				print "<li><a href='execsum.php?view=$class_id&execsum=$exec_sum_id'>";
				print "$lname &mdash; $term_codes[$term] $year &mdash; $coursenum $sectnum <br>$coursename";
				print "</a> <span class='example'>$priv_pub_labels[$priv_pub]</span></li>";
			}
			print "</ol>";
		}
		
	}
}




?>