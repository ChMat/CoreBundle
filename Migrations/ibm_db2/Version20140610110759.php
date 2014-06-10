<?php

namespace Claroline\CoreBundle\Migrations\ibm_db2;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/10 11:08:01
 */
class Version20140610110759 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_activity_past_evaluation 
            ADD COLUMN log_id INTEGER DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity_past_evaluation 
            ADD CONSTRAINT FK_F1A76182EA675D86 FOREIGN KEY (log_id) 
            REFERENCES claro_log (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_F1A76182EA675D86 ON claro_activity_past_evaluation (log_id)
        ");
        $this->addSql("
            ALTER TABLE claro_activity_evaluation 
            ADD COLUMN log_id INTEGER DEFAULT NULL ALTER user_id user_id INTEGER NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity_evaluation 
            ADD CONSTRAINT FK_F75EC869EA675D86 FOREIGN KEY (log_id) 
            REFERENCES claro_log (id) 
            ON DELETE SET NULL
        ");
        $this->addSql("
            CREATE INDEX IDX_F75EC869EA675D86 ON claro_activity_evaluation (log_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX user_activity_unique_evaluation ON claro_activity_evaluation (user_id, activity_parameters_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_activity_evaluation 
            DROP COLUMN log_id ALTER user_id user_id INTEGER DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_activity_evaluation 
            DROP FOREIGN KEY FK_F75EC869EA675D86
        ");
        $this->addSql("
            DROP INDEX IDX_F75EC869EA675D86
        ");
        $this->addSql("
            DROP INDEX user_activity_unique_evaluation
        ");
        $this->addSql("
            ALTER TABLE claro_activity_past_evaluation 
            DROP COLUMN log_id
        ");
        $this->addSql("
            ALTER TABLE claro_activity_past_evaluation 
            DROP FOREIGN KEY FK_F1A76182EA675D86
        ");
        $this->addSql("
            DROP INDEX IDX_F1A76182EA675D86
        ");
    }
}