<?php
	/* ������, ��������� ������������, ���������� �� ���� �������*/	    
	$educationProgramsAction = array(
    );
	
	/* ������, ��������� ������ ������*/	        
    $admin_educationProgramsAction = array(
    );

	/* ������, ��������� ������ �������*/	        
    $teacher_educationProgramsAction = array(
    );

	/* ������, ��������� ������ ���������*/	        
    $student_educationProgramsAction = array(
    );
	
    //Wtfi
    $cur_ctrl = $_SERVER['REQUEST_URI'];
	$prefix = '/educational_programs/';

	$user = Model_User::create();
	$udata = (object) $user->getAuth();
	
	/* ����� ����� ������� ����*/
		  foreach ($educationProgramsAction as $title => $controller): ?>
		<?php if ($prefix. $controller == strtolower ($cur_ctrl)): ?>
			<li class="headli active"><?php echo $title; ?></li>
		<?php else: ?>
			<li class="headli">
				<a href="<?=$prefix.$controller ?>"><?=$title ?></a>
			</li>
		<?php endif; ?>
	<?php endforeach;
	
	if (isset($udata->role))
	{
		if (Model_User::ROLE_TEACHER == $udata->role)
		{
			$items = 'teacher_educationProgramsAction';
		}elseif (Model_User::ROLE_ADMIN == $udata->role)
		{
			$items = 'admin_educationProgramsAction';	
		}elseif (Model_User::ROLE_STUDENT == $udata->role)
		{
			$items = 'student_educationProgramsAction';	
		}
	/* ����� ������� ����, ������������� ��� ������������� ������������ */
		  foreach (${$items} as $title => $controller): ?>
		<?php if ($controller == strtolower ($cur_ctrl)): ?>
			<li class="active"><?php echo $title; ?></li>
		<?php else: ?>
			<li class="headli">
				<a href="<?=$prefix.$controller ?>"><?=$title ?></a>
			</li>
		<?php endif; ?>
	<?php endforeach; 	
}												  
?>