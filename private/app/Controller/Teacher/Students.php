<?php

    /* $Id:  $ */

    /**
    * ���������� ��� ���������� ����������� �������������.
    */
    class Controller_Teacher_Students extends Mvc_Controller_Abstract {

        /**
        * ����������� ������ ���������� �������������.
        */
        public function action_index() {
            $model_education_students = Model_Education_Students::create();
            $user = Model_User::create();
            $udata = (object) $user->getAuth();
            $listeners = $model_education_students->getListenerList($udata->user_id);
            $this->set('listeners', $listeners);
            $this->render('teacher_students/index');
        }

        /**
        * ����������� ������ ��������� ���������.
        */
        public function action_disciplines($params) {
            $model_education_students = Model_Education_Students::create();

            $disciplines = $model_education_students->getDisciplines($params['student_id']);
            $disciplines_programs = $model_education_students->getDisciplinesPrograms($params['student_id']);

            $this->set('disciplines', $disciplines);
            $this->set('disciplines_programs', $disciplines_programs);
            $this->render('teacher_students/disciplines');
        }

    }