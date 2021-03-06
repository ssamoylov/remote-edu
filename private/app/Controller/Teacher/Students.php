<?php

    /* $Id:  $ */

    /**
    * Контроллер для управления слушателями преподавателя.
    */
    class Controller_Teacher_Students extends Mvc_Controller_Abstract {

        /**
        * Отображение списка слушателей преподавателя.
        */
        public function action_index() {
            $model_education_students = Model_Education_Students::create();
            $model_user = Model_User::create();

            $udata = (object) $model_user->getAuth();
            $listeners = $model_education_students->getListenerList($udata->user_id);

            $this->set('listeners', $listeners);
            $this->render('teacher_students/index');
        }

        /**
        * Отображение списка дисциплин слушателя.
        *
        * @params['student_id'] Идентификатор студента.
        */
        public function action_disciplines($params) {
            $model_education_students = Model_Education_Students::create();
            $model_user = Model_User::create();

            $disciplines = $model_education_students->getDisciplines($params['student_id']);
            $disciplines_programs = $model_education_students->getDisciplinesPrograms($params['student_id']);

            $user_info = $model_user->getUserInfo($params['student_id']);

            $this->set('disciplines', $disciplines);            
            $this->set('user_id', $params['student_id']);
            $this->set('user_info', $user_info);
            
            //print_r ($disciplines_programs);
            
            $res = array();
            foreach ($disciplines_programs as $val) {
                $r = array();
                $r['d_title'] = $val['d_title'];
                $r['id'] = $val['id'];
                $res[$val['p_title']][] = $r;
            }
            
            $this->set('disciplines_programs', $res);
        
            $this->render('teacher_students/disciplines');
        }

        /**
        * Отображение куратору успеваемости слушателя по дисциплине.
        *
        * @params['student_id'] Идентификатор студента.
        * @params['discipline_id'] Идентификатор дисциплины.
        */
        public function action_discipline($params) {

            $model_credit = Model_Credit::create();
            $model_control_work = Model_ControlWork::create();
            $model_education_programs = Model_Education_Programs::create();
            $model_user = Model_User::create();

            $model_education_programs->getDiscipline($params['discipline_id'], $title, $labourIntensive, $coef);
            
            $this->set('test_results', $model_control_work->getStudentTestResultsByDiscipline($params['student_id'], $params['discipline_id']));
            $this->set('credits', $model_credit->getStudentCreditsByDiscipline($params['student_id'], $params['discipline_id']));
            $this->set('control_works', $model_control_work->getStudentMarksByDiscipline($params['student_id'], $params['discipline_id']));
            $this->set('control_names_map', $model_control_work->getControlNamesMap());
            $this->set('mark_names_map', $model_control_work->getMarkNamesMap());
            $this->set('discipline_title', $title);
            $this->set('sections', $model_education_programs->getSectionsByDiscipline($params['discipline_id']));

            $this->set('user_id', $params['student_id']);
            $this->set('user_info', $model_user->getUserInfo($params['student_id']));

            $this->render('teacher_students/discipline');
        }
        
    	/*
        public function action_discipline($params) {
            $model_checkpoint = Model_Checkpoint::create();
            $model_education_programs = Model_Education_Programs::create();
            $model_education_students = Model_Education_Students::create();
            $model_user = Model_User::create();

            $checkpoints = $model_checkpoint->getCheckpointsSectionsByDiscipline($params);
            $model_education_programs->getDiscipline($params['discipline_id'], $title, $labourIntensive, $coef);
            $user_info = $model_user->getUserInfo($params['student_id']);

            $this->set('discipline_title', $title);
            $this->set('checkpoints', $checkpoints);
            $this->set('students', $model_education_programs->getStudentsByDiscipline($params['discipline_id']));
            $this->set('sections', $model_education_programs->getSectionsByDiscipline($params['discipline_id']));

            $this->render('teacher_students/discipline');
        }
	*/

    }
