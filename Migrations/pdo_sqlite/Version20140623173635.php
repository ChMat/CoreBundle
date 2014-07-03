<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/23 05:36:36
 */
class Version20140623173635 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_general_facet_preference (
                id INTEGER NOT NULL, 
                role_id INTEGER NOT NULL, 
                baseData BOOLEAN NOT NULL, 
                mail BOOLEAN NOT NULL, 
                phone BOOLEAN NOT NULL, 
                sendMail BOOLEAN NOT NULL, 
                sendMessage BOOLEAN NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_38AACF88D60322AC ON claro_general_facet_preference (role_id)
        ");
        $this->addSql("
            DROP INDEX IDX_F1A76182A76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_F1A76182896F55DB
        ");
        $this->addSql("
            DROP INDEX IDX_F1A76182EA675D86
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_activity_past_evaluation AS 
            SELECT id, 
            log_id, 
            activity_parameters_id, 
            user_id, 
            last_date, 
            evaluation_type, 
            evaluation_status, 
            duration, 
            score, 
            score_num, 
            score_min, 
            score_max, 
            evaluation_comment, 
            details 
            FROM claro_activity_past_evaluation
        ");
        $this->addSql("
            DROP TABLE claro_activity_past_evaluation
        ");
        $this->addSql("
            CREATE TABLE claro_activity_past_evaluation (
                id INTEGER NOT NULL, 
                log_id INTEGER DEFAULT NULL, 
                activity_parameters_id INTEGER DEFAULT NULL, 
                user_id INTEGER DEFAULT NULL, 
                evaluation_type VARCHAR(255) DEFAULT NULL, 
                evaluation_status VARCHAR(255) DEFAULT NULL, 
                duration INTEGER DEFAULT NULL, 
                score VARCHAR(255) DEFAULT NULL, 
                score_num INTEGER DEFAULT NULL, 
                score_min INTEGER DEFAULT NULL, 
                score_max INTEGER DEFAULT NULL, 
                evaluation_comment VARCHAR(255) DEFAULT NULL, 
                details CLOB DEFAULT NULL, 
                evaluation_date DATETIME DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_F1A76182EA675D86 FOREIGN KEY (log_id) 
                REFERENCES claro_log (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_F1A76182896F55DB FOREIGN KEY (activity_parameters_id) 
                REFERENCES claro_activity_parameters (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_F1A76182A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_activity_past_evaluation (
                id, log_id, activity_parameters_id, 
                user_id, evaluation_date, evaluation_type, 
                evaluation_status, duration, score, 
                score_num, score_min, score_max, 
                evaluation_comment, details
            ) 
            SELECT id, 
            log_id, 
            activity_parameters_id, 
            user_id, 
            last_date, 
            evaluation_type, 
            evaluation_status, 
            duration, 
            score, 
            score_num, 
            score_min, 
            score_max, 
            evaluation_comment, 
            details 
            FROM __temp__claro_activity_past_evaluation
        ");
        $this->addSql("
            DROP TABLE __temp__claro_activity_past_evaluation
        ");
        $this->addSql("
            CREATE INDEX IDX_F1A76182A76ED395 ON claro_activity_past_evaluation (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F1A76182896F55DB ON claro_activity_past_evaluation (activity_parameters_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F1A76182EA675D86 ON claro_activity_past_evaluation (log_id)
        ");
        $this->addSql("
            DROP INDEX user_activity_unique_evaluation
        ");
        $this->addSql("
            DROP INDEX IDX_F75EC869A76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_F75EC869896F55DB
        ");
        $this->addSql("
            DROP INDEX IDX_F75EC869EA675D86
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_activity_evaluation AS 
            SELECT id, 
            log_id, 
            activity_parameters_id, 
            user_id, 
            last_date, 
            evaluation_type, 
            evaluation_status, 
            duration, 
            score, 
            score_num, 
            score_min, 
            score_max, 
            evaluation_comment, 
            details, 
            total_duration, 
            attempts_count 
            FROM claro_activity_evaluation
        ");
        $this->addSql("
            DROP TABLE claro_activity_evaluation
        ");
        $this->addSql("
            CREATE TABLE claro_activity_evaluation (
                id INTEGER NOT NULL, 
                log_id INTEGER DEFAULT NULL, 
                activity_parameters_id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                evaluation_type VARCHAR(255) DEFAULT NULL, 
                evaluation_status VARCHAR(255) DEFAULT NULL, 
                duration INTEGER DEFAULT NULL, 
                score VARCHAR(255) DEFAULT NULL, 
                score_num INTEGER DEFAULT NULL, 
                score_min INTEGER DEFAULT NULL, 
                score_max INTEGER DEFAULT NULL, 
                evaluation_comment VARCHAR(255) DEFAULT NULL, 
                details CLOB DEFAULT NULL, 
                total_duration INTEGER DEFAULT NULL, 
                attempts_count INTEGER DEFAULT NULL, 
                lastest_evaluation_date DATETIME DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_F75EC869EA675D86 FOREIGN KEY (log_id) 
                REFERENCES claro_log (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_F75EC869896F55DB FOREIGN KEY (activity_parameters_id) 
                REFERENCES claro_activity_parameters (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_F75EC869A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_activity_evaluation (
                id, log_id, activity_parameters_id, 
                user_id, lastest_evaluation_date, 
                evaluation_type, evaluation_status, 
                duration, score, score_num, score_min, 
                score_max, evaluation_comment, details, 
                total_duration, attempts_count
            ) 
            SELECT id, 
            log_id, 
            activity_parameters_id, 
            user_id, 
            last_date, 
            evaluation_type, 
            evaluation_status, 
            duration, 
            score, 
            score_num, 
            score_min, 
            score_max, 
            evaluation_comment, 
            details, 
            total_duration, 
            attempts_count 
            FROM __temp__claro_activity_evaluation
        ");
        $this->addSql("
            DROP TABLE __temp__claro_activity_evaluation
        ");
        $this->addSql("
            CREATE UNIQUE INDEX user_activity_unique_evaluation ON claro_activity_evaluation (user_id, activity_parameters_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F75EC869A76ED395 ON claro_activity_evaluation (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F75EC869896F55DB ON claro_activity_evaluation (activity_parameters_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F75EC869EA675D86 ON claro_activity_evaluation (log_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_general_facet_preference
        ");
        $this->addSql("
            DROP INDEX IDX_F75EC869A76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_F75EC869896F55DB
        ");
        $this->addSql("
            DROP INDEX IDX_F75EC869EA675D86
        ");
        $this->addSql("
            DROP INDEX user_activity_unique_evaluation
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_activity_evaluation AS 
            SELECT id, 
            user_id, 
            activity_parameters_id, 
            log_id, 
            lastest_evaluation_date, 
            evaluation_type, 
            evaluation_status, 
            duration, 
            score, 
            score_num, 
            score_min, 
            score_max, 
            evaluation_comment, 
            details, 
            total_duration, 
            attempts_count 
            FROM claro_activity_evaluation
        ");
        $this->addSql("
            DROP TABLE claro_activity_evaluation
        ");
        $this->addSql("
            CREATE TABLE claro_activity_evaluation (
                id INTEGER NOT NULL, 
                user_id INTEGER NOT NULL, 
                activity_parameters_id INTEGER NOT NULL, 
                log_id INTEGER DEFAULT NULL, 
                evaluation_type VARCHAR(255) DEFAULT NULL, 
                evaluation_status VARCHAR(255) DEFAULT NULL, 
                duration INTEGER DEFAULT NULL, 
                score VARCHAR(255) DEFAULT NULL, 
                score_num INTEGER DEFAULT NULL, 
                score_min INTEGER DEFAULT NULL, 
                score_max INTEGER DEFAULT NULL, 
                evaluation_comment VARCHAR(255) DEFAULT NULL, 
                details CLOB DEFAULT NULL, 
                total_duration INTEGER DEFAULT NULL, 
                attempts_count INTEGER DEFAULT NULL, 
                last_date DATETIME DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_F75EC869A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_F75EC869896F55DB FOREIGN KEY (activity_parameters_id) 
                REFERENCES claro_activity_parameters (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_F75EC869EA675D86 FOREIGN KEY (log_id) 
                REFERENCES claro_log (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_activity_evaluation (
                id, user_id, activity_parameters_id, 
                log_id, last_date, evaluation_type, 
                evaluation_status, duration, score, 
                score_num, score_min, score_max, 
                evaluation_comment, details, total_duration, 
                attempts_count
            ) 
            SELECT id, 
            user_id, 
            activity_parameters_id, 
            log_id, 
            lastest_evaluation_date, 
            evaluation_type, 
            evaluation_status, 
            duration, 
            score, 
            score_num, 
            score_min, 
            score_max, 
            evaluation_comment, 
            details, 
            total_duration, 
            attempts_count 
            FROM __temp__claro_activity_evaluation
        ");
        $this->addSql("
            DROP TABLE __temp__claro_activity_evaluation
        ");
        $this->addSql("
            CREATE INDEX IDX_F75EC869A76ED395 ON claro_activity_evaluation (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F75EC869896F55DB ON claro_activity_evaluation (activity_parameters_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F75EC869EA675D86 ON claro_activity_evaluation (log_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX user_activity_unique_evaluation ON claro_activity_evaluation (user_id, activity_parameters_id)
        ");
        $this->addSql("
            DROP INDEX IDX_F1A76182A76ED395
        ");
        $this->addSql("
            DROP INDEX IDX_F1A76182896F55DB
        ");
        $this->addSql("
            DROP INDEX IDX_F1A76182EA675D86
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_activity_past_evaluation AS 
            SELECT id, 
            user_id, 
            activity_parameters_id, 
            log_id, 
            evaluation_date, 
            evaluation_type, 
            evaluation_status, 
            duration, 
            score, 
            score_num, 
            score_min, 
            score_max, 
            evaluation_comment, 
            details 
            FROM claro_activity_past_evaluation
        ");
        $this->addSql("
            DROP TABLE claro_activity_past_evaluation
        ");
        $this->addSql("
            CREATE TABLE claro_activity_past_evaluation (
                id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                activity_parameters_id INTEGER DEFAULT NULL, 
                log_id INTEGER DEFAULT NULL, 
                evaluation_type VARCHAR(255) DEFAULT NULL, 
                evaluation_status VARCHAR(255) DEFAULT NULL, 
                duration INTEGER DEFAULT NULL, 
                score VARCHAR(255) DEFAULT NULL, 
                score_num INTEGER DEFAULT NULL, 
                score_min INTEGER DEFAULT NULL, 
                score_max INTEGER DEFAULT NULL, 
                evaluation_comment VARCHAR(255) DEFAULT NULL, 
                details CLOB DEFAULT NULL, 
                last_date DATETIME DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_F1A76182A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_F1A76182896F55DB FOREIGN KEY (activity_parameters_id) 
                REFERENCES claro_activity_parameters (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, 
                CONSTRAINT FK_F1A76182EA675D86 FOREIGN KEY (log_id) 
                REFERENCES claro_log (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_activity_past_evaluation (
                id, user_id, activity_parameters_id, 
                log_id, last_date, evaluation_type, 
                evaluation_status, duration, score, 
                score_num, score_min, score_max, 
                evaluation_comment, details
            ) 
            SELECT id, 
            user_id, 
            activity_parameters_id, 
            log_id, 
            evaluation_date, 
            evaluation_type, 
            evaluation_status, 
            duration, 
            score, 
            score_num, 
            score_min, 
            score_max, 
            evaluation_comment, 
            details 
            FROM __temp__claro_activity_past_evaluation
        ");
        $this->addSql("
            DROP TABLE __temp__claro_activity_past_evaluation
        ");
        $this->addSql("
            CREATE INDEX IDX_F1A76182A76ED395 ON claro_activity_past_evaluation (user_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F1A76182896F55DB ON claro_activity_past_evaluation (activity_parameters_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F1A76182EA675D86 ON claro_activity_past_evaluation (log_id)
        ");
    }
}