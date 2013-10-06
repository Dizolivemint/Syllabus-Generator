<?php $classid = $_GET['sylledit']; ?>

<?php process_form($classid); ?>
<?php $data = get_class_details($classid); ?>
<?php 
if( $data['sectnum'] != "")
{
	$section = $data['sectnum'];
}
else { $section = "xx"; }
?>

<div class="frame">
	
    <h4>Course Information</h4>
	<p><strong>Course:</strong> <?php echo syll_info("coursenum", $classid); ?> <?php echo syll_info("course", $classid); ?><br />
    <strong>Instructor:</strong> <?php echo syll_info("fname", $classid); ?> <?php echo syll_info("lname", $classid); ?><br />
    <strong>Term / Year:</strong> <?php echo syll_info("term", $classid); ?> <?php echo syll_info("year", $classid); ?><br />
    <strong>Status:</strong> <span class="<?php echo syll_info("status", $classid); ?>"><?php echo syll_info("status", $classid); ?></span></p>
    
    <h4 class="fold">Course Details</h4>
    
    <?php $courseid = syll_info("courseid", $classid); ?>
    <div class="hide">
    	<p><strong>Total Hours:</strong> <?php echo course_item("totalhrs", $courseid); ?> Hours<br />
        <strong>Lecture Hours:</strong> <?php echo course_item("lecthrs", $courseid); ?> Hours<br />
        <strong>Lab Hours:</strong> <?php echo course_item("labhrs", $courseid); ?> Hours<br />
        <strong>Credits:</strong> <?php echo course_item("credit", $courseid); ?> Credits</p>
        <p><strong>Course Description:</strong><br /> 
        <?php echo course_item("desc", $courseid); ?></p>
        <p><strong>Learning Objectives</strong></p>
        <?php output_core_competencies($courseid); ?>
        
        <p><strong>Course Prerequisites</strong></p>
        <?php output_prerequisites($courseid); ?>
    </div>

</div>

	<form method="post" action="index.php?sylledit=<?php echo $classid; ?>">
    <input type="hidden" name="classid" value="<?php echo $classid; ?>" />
	<div class="frame">
    <?php output_status(check_syllabus_details($classid)); ?>
	<h4 class="fold">Syllabus Details</h4>
    <div class="hide">
    
    <?php edit_meeting_times($classid); ?>
    
    <label for="sectday">Section Number</label> 
    <p class="example">i.e. A2 Would be Monday at 1 pm. Mid-Quarter classes add first day section only.</p>
    
    <div id="sectcontrols">
    <select name="sectday" id="sectday">
    	<option value="">----</option>
    	<option value="A" <?php parse_class_sectday($section, "A") ?>>A (Monday)</option>
        <option value="B" <?php parse_class_sectday($section, "B") ?>>B (Tuesday)</option>
        <option value="C" <?php parse_class_sectday($section, "C") ?>>C (Wednesday)</option>
        <option value="D" <?php parse_class_sectday($section, "D") ?>>D (Thursday)</option>
        <option value="E" <?php parse_class_sectday($section, "E") ?>>E (Friday)</option>
        <option value="F" <?php parse_class_sectday($section, "F") ?>>F (Saturday)</option>
        <option value="F" <?php parse_class_sectday($section, "G") ?>>G (Sunday)</option>
    </select>
    
    <select name="secttime" id="secttime">
    	<option value="">----</option>
    	<option value="1" <?php parse_class_secttime($section, "1") ?>>1 (8:00 am)</option>
        <option value="2" <?php parse_class_secttime($section, "2") ?>>2 (1:00 pm)</option>
        <option value="3" <?php parse_class_secttime($section, "3") ?>>3 (5:30 pm)</option>
        <option value="4" <?php parse_class_secttime($section, "4") ?>>4</option>
        <option value="5" <?php parse_class_secttime($section, "5") ?>>5</option>
        <option value="6" <?php parse_class_secttime($section, "6") ?>>6</option>
    </select> 
    </div>
    
    <label for="hwhrs">Estimated Homework Hours <span class="example">i.e. 4 hours per week</span></label>
    <input type="text" name="hwhrs" id="hwhrs" value="<?php echo $data['hwhrs']; ?>" /> 
    
    <label for="officehrs">Instructor Availability Outside of Class <br><span class="example">i.e. Wednesdays from 12 - 1 pm and by appointment</span></label>
    <input type="text" name="officehrs" id="officehrs" value="<?php echo $data['officehrs']; ?>" class="fullwidth" />
    
    <label for="materials">Materials Required for the Course</label>
    <textarea name="materials" class="tinymice" id="materials" cols=55 rows="5"><?php echo $data['materials']; ?></textarea>
    
    <label for="methods">Methods of Instruction</label>
    <textarea name="methods" class="tinymice" id="methods" cols=55 rows="5"><?php echo $data['methods']; ?></textarea>
    
    <label for="tech">Technology Required</label>
    <textarea name="tech" class="tinymice" id="tech" cols=55 rows="5"><?php echo $data['tech']; ?></textarea>
    </div><!-- end hide -->
    </div><!-- end frame -->
    
    <div class="frame">
    <?php output_status(check_eval_status($classid)); ?>
    <h4 class="fold">Evaluation Process</h4>
        <div class="hide">
        <p><span class="desclabel">Description</span> Percent</p>
        <?php edit_eval_process($classid) ?>
        <p class="rightjustify"><label>Total Percent <input type="text" name="totalpercent" id="totalpercent" readonly="readonly" /></label></p>
        </div>
    </div>
    
    <div class="frame">
    <?php check_books($classid, "yes"); ?>
    <h4 class="fold">Books</h4>
        <div class="hide">
        <?php edit_books($classid); ?>
        </div>
    </div>
    
    <div class="frame">
    <h4 class="fold">Course Focus (optional)</h4>
        <div class="hide">
        <p><span class="example">Use this section to add a descriptive focus for the course</span><br />
        <textarea name="focus" class="tinymice" id="focus" cols=55 rows="5"><?php echo $data['focus']; ?></textarea></p>
        </div>
    </div>
    
    <div class="frame">
    <h4 class="fold">Additional Requirements (optional)</h4>
        <div class="hide">
        <p><span class="example">Use this section to specify any additional requirements for students</span><br />
        <textarea name="additional" class="tinymice" id="additional" cols=55 rows="5"><?php echo $data['addreq']; ?></textarea></p>
        </div>
    </div>
    
    <div class="frame">
    <h4 class="fold">Additional Competetencies (optional)</h4>
        <div class="hide">
        <p><span class="example">Use this section to add competencies that go beyond the ones required for the course.</span></p>
        <?php edit_addtl_competencies($classid, "1"); ?>
        </div>
    </div>
    
    <div class="frame">
    <h4 class="fold">Additional Grading Policies (optional)</h4>
        <div class="hide">
        <p><span class="example">Use this section to add additional grading policies that go beyond the ones required for all courses.</span><br />
        <textarea name="policy" class="tinymice" id="policy" cols=55 rows="10"><?php echo addtn_grade_policies($classid, "1"); ?></textarea></p>
        </div>
    </div>
    
    <?php display_activity_form($classid); ?>
    
    <div id="updatebar">
    
    	<input type="submit" name="update" id="update" class="submitbttn" value="Save your progress!" />
        <?php add_submit_button($classid); ?>
    
    </div>
    
    </form>