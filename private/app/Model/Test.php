<?php

    /**
    * Модель для работы с данными тестов.
    */
    class Model_Test extends Model_Base {
        const TIME_PER_QUESTION = 120;

        /**
        * Создание экземпляра модели.
        *
        * @return Model_Test
        */
        public static function create() {
            return new self();
        }

        public function add(
            $theme, $num_questions, /*$time_limit, */$errors_limit,
            $attempts_limit
        ) {
            $sql = '
                INSERT INTO ' . $this->_tables['tests']  . '
                (theme, num_questions, time_limit, errors_limit, attempts_limit)
                VALUES (
                    :theme, :num_questions, :time_limit, :attempts_limit,
                    :errors_limit
                )
            ';

            $values = array(
                ':theme'          => $theme,
                ':num_questions'  => $num_questions,
                ':time_limit'     => $num_questions * self::TIME_PER_QUESTION,
                ':errors_limit'   => $errors_limit,
                ':attempts_limit' => $attempts_limit
            );

            $stmt = $this->prepare($sql);
            $stmt->execute($values);

            return $this->lastInsertId();
        }

        public function update(
            $test_id, $theme, $num_questions, /*$time_limit, */$errors_limit,
            $attempts_limit
        ) {
            $sql = '
                UPDATE ' . $this->_tables['tests']  . '
                SET theme          = :theme,
                    num_questions  = :num_questions,
                    time_limit     = :time_limit,
                    errors_limit   = :errors_limit,
                    attempts_limit = :attempts_limit
                WHERE test_id = :tid
            ';

            $values = array(
                ':tid'            => $test_id,
                ':theme'          => $theme,
                ':num_questions'  => $num_questions,
                ':time_limit'     => $num_questions * self::TIME_PER_QUESTION,
                ':errors_limit'   => $errors_limit,
                ':attempts_limit' => $attempts_limit
            );

            $stmt = $this->prepare($sql);
            $stmt->execute($values);

            $row_count = $stmt->rowCount();
            return $row_count > 0;
        }

        public function get($test_id) {
            $sql = '
                SELECT *
                FROM ' . $this->_tables['tests'] . '
                WHERE test_id = ?
                LIMIT 1
            ';

            $stmt = $this->prepare($sql);
            $stmt->execute(array($test_id));

            return $stmt->fetch(Db_Pdo::FETCH_ASSOC);
        }

        /**
        * Добавление нескольких вопросов в тест.
        *
        * @param  mixed $test_id   Идентификатор теста, к которому добавляется вопрос.
        * @param  mixed $questions Список контейнеров вопросов.
        * @return //
        */
        public function addQuestions($test_id, array $questions) {
            $sql = '
                INSERT INTO ' . $this->_tables['questions'] . '
                (test_id, type, data)
                VALUES (:tid, :type, :data)
            ';

            $stmt = $this->prepare($sql);

            $values = array(
                ':tid'  => $test_id,
                ':type' => '',
                ':data' => ''
            );

            $new_ids = new stdClass();

            foreach ($questions as $q) {
                $q = (object) $q;

                $values[':type'] = $q->obj->getType();
                $values[':data'] = $q->obj->freeze();
                $stmt->execute($values);

                /**
                * Performing string convertion in order to save all keys in
                * JSON.
                */
                $new_ids->{$q->tmp_id} = $this->lastInsertId();
            }

            return $new_ids;
        }

        public function editQuestions(array $questions) {
            $sql = '
                UPDATE ' . $this->_tables['questions'] . '
                SET data = :data
                WHERE question_id = :qid
            ';

            $stmt = $this->prepare($sql);

            foreach ($questions as $q)
            {
                $q = (object) $q;

                $values = array(
                    ':qid'  => $q->question_id,
                    ':data' => $q->obj->freeze()
                );
                $stmt->execute($values);
            }
        }

        /**
        * Получение списка всех вопросов к тесту.
        *
        * @param  int     $test_id Идентификатор теста.
        * @param  boolean $thaw    Создавать ли объекты вопросов или оставить сырые данные.
        * @return array array(идентификатор_вопроса => объект_вопроса).
        */
        public function getQuestions($test_id, $thaw = true) {
            $sql = '
                SELECT *
                FROM ' . $this->_tables['questions'] . '
                WHERE test_id = ?
            ';

            $stmt = $this->prepare($sql);
            $stmt->execute(array($test_id));

            $questions = array();

            while ($q = $stmt->fetch(Db_Pdo::FETCH_OBJ))
            {
                if ($thaw) {
                    $q_data = Model_Question_Abstract::thaw($q->type, $q->data);
                } else {
                    $q_data = unserialize($q->data);
                    $q_data['type'] = $q->type;
                    /**
                    * @todo Cast to stdClass
                    */
                    $q_data['question_id'] = $q->question_id;
                }

                $questions[] = $q_data;
            }

            return $questions;
        }

        protected function _getExamQuestions($test_id, $limit) {
            $sql = '
                SELECT *
                FROM ' . $this->_tables['questions'] . '
                WHERE test_id = ?
                ORDER BY RAND()
                LIMIT ' . $limit . '
            ';

            $stmt = $this->prepare($sql);
            $stmt->execute(array($test_id));

            $questions = array();
            $question_ids = array();

            while ($q = $stmt->fetch(Db_Pdo::FETCH_OBJ))
            {
                $obj = Model_Question_Abstract::thaw($q->type, $q->data);
                $data = $obj->getExamData();
                $data['type'] = $q->type;

                $questions[$q->question_id] = $data;
            }

            return $questions;
        }

        protected function _getQuestionsById($ids) {
            $ids = implode(', ', $ids);
            $ids = $this->quote($ids);
            $ids = trim($ids, "'");

            $sql = '
                SELECT *
                FROM ' . $this->_tables['questions'] . '
                WHERE question_id IN (' . $ids . ')
            ';

            $stmt = $this->prepare($sql);
            $stmt->execute();

            $questions = array();

            while ($q = $stmt->fetch(Db_Pdo::FETCH_OBJ)) {
                $obj = Model_Question_Abstract::thaw($q->type, $q->data);
                $questions[$q->question_id] = $obj;
            }

            return $questions;
        }

        public function start($test_id, $num_questions) {
            $questions = $this->_getExamQuestions($test_id, $num_questions);

            if (sizeof($questions) < $num_questions) {
                $code = Model_Test_Exception::NOT_ENOUGH_QUESTIONS;
                throw new Model_Test_Exception($code);
            }

            $session = Resources_Abstract::getInstance()->session;
            $auth    = Resources_Abstract::getInstance()->auth;

            $timer = Timer::create()->start();
            $question_ids = array_keys($questions);
            $sec_code = $auth->getExamSecurityCode($test_id, $question_ids);

            $session->exams[$test_id] = array(
                'sec_code' => $sec_code,
                'timer'    => $timer
            );

            return $questions;
        }

        public function stop($test_id, $answers) {
            $session = Resources_Abstract::getInstance()->session;

            $exams = & $session->exams[$test_id];

            if (!isset($exams)) {
                $code = Model_Test_Exception::EXAM_IS_NOT_REGISTERED;
                throw new Model_Test_Exception($code);
            }

            $auth = Resources_Abstract::getInstance()->auth;
            $question_ids = array_keys($answers);
            $sec_code = $auth->getExamSecurityCode($test_id, $question_ids);

            if ($sec_code != $exams['sec_code']) {
                $code = Model_Test_Exception::EXAM_QUESTIONS_MISMATCHED;
                throw new Model_Test_Exception($code);
            }

            $questions = $this->_getQuestionsById($question_ids);

            $results = (object) array(
                'correct'    => array(),
                'incorrect'  => array(),
                'unanswered' => array(),
                'time'       => $exams['timer']->stop(),
                'passed'     => null
            );

            unset($exams, $session->exams[$test_id]);

            foreach ($answers as $q_id => $answer)
            {
                $q = $questions[$q_id];

                if (null === $answer) {
                    $results->unanswered[] = $q_id;
                    continue;
                }

                if ($q->isCorrectAnswer($answer)) {
                    $results->correct[] = $q_id;
                } else {
                    $results->incorrect[] = $q_id;
                }
            }

            $test = (object) $this->get($test_id);
            $num_errors = sizeof($results->incorrect) +
                          sizeof($results->unanswered);
            $allowable_errors = self::calcAllowableErrors($test->num_questions,
                                                          $test->errors_limit);

            $passed  = ($num_errors > $allowable_errors ? false : true);
            $passed &= ($results->time > $test->time_limit ? false : true);

            $results->passed = $passed;

            $this->_saveExamResults($test_id, $num_errors,
                                    $results->time, $results->passed);

            return $results;
        }

        protected function _saveExamResults(
            $test_id, $num_errors, $time, $passed
        ) {
            $sql = '
                INSERT INTO ' . $this->_tables['examinations'] . '
                (user_id, test_id, time, num_errors, passed)
                VALUES (:uid, :tid, :time, :num_errors, :passed)
            ';

            $user = Model_User::create();
            $udata = (object) $user->getAuth();

            $values = array(
                'uid'        => $udata->user_id,
                'tid'        => $test_id,
                'time'       => $time,
                'num_errors' => $num_errors,
                'passed'     => ($passed ? 'true' : 'false')
            );

            $stmt = $this->prepare($sql);
            $stmt->execute($values);

            return $this->lastInsertId();
        }

        public function attemptsRemains($test_id, $attempts_limit) {
            $sql = '
                SELECT COUNT(*)
                FROM ' . $this->_tables['examinations'] . '
                WHERE test_id = :tid AND
                      user_id = :uid
            ';

            $user = Model_User::create();
            $udata = (object) $user->getAuth();

            $values = array(
                'tid' => $test_id,
                'uid' => $udata->user_id
            );

            $stmt = $this->prepare($sql);
            $stmt->execute($values);

            $attempts_used = $stmt->fetchColumn();
            $attempts_remaining = $attempts_limit - $attempts_used;

            return $attempts_remaining;
        }

        public static function calcAllowableErrors($num_questions, $errors_limit) {
            return round($num_questions / 100 * $errors_limit);
        }
    }

?>