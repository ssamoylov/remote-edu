<?php

    /* $Id$ */

    /**
    * Модель для работы с разделами дисциплин.
    */
    class Model_Section extends Model_Base {
        /**
        * Создание нового экземпляра модели.
        *
        * @return Model_Section
        */
        public static function create() {
            return new self();
        }

        /**
        * Получение списка всех разделов по идентификатору дисциплины.
        *
        * @param  int $disc_id Идентификатор дисциплины.
        * @return array
        */
	
        public function getAllByDiscipline($disc_id) {
            $sql = '
                SELECT s.*, c.test_id, t.theme AS test_theme
                FROM ' . $this->_tables['sections'] . ' s
                LEFT JOIN ' . $this->_tables['checkpoints'] . ' c
                    ON s.section_id = c.section_id AND c.active = 1
                LEFT JOIN ' . $this->_tables['tests'] . ' t
                    ON t.test_id = c.test_id
                WHERE discipline_id = ?
                ORDER BY s.number ASC
            ';

            $stmt = $this->prepare($sql);
            $stmt->execute(array($disc_id));

            $sections = $stmt->fetchAll(Db_Pdo::FETCH_ASSOC);
            return $sections;
        }
	

        /**
        * Получение списка всех разделов по идентификатору дисциплины.
        *
        * @param  int $disc_id Идентификатор дисциплины.
        * @return array
        */
        public function getSectionsIdByDisciplineId($program_id) {
            $sql = '
                SELECT section_id
                FROM ' . $this->_tables['sections'] . '
                WHERE discipline_id = ?
            ';

            $stmt = $this->prepare($sql);
            $stmt->execute(array($program_id));

            $discs = $stmt->fetchAll(Db_Pdo::FETCH_ASSOC);
            return $discs;
        }
    }

?>
