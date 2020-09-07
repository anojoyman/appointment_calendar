<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200905054743 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX date_hour_unique');
        $this->addSql('CREATE TEMPORARY TABLE __temp__appointment AS SELECT id_appointment, for_date, for_hour, patient_info, created_at FROM appointment');
        $this->addSql('DROP TABLE appointment');
        $this->addSql('CREATE TABLE appointment (id_appointment INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, created_at CLOB NOT NULL COLLATE BINARY, for_date VARCHAR(255) NOT NULL, for_hour VARCHAR(255) NOT NULL, patient_info VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO appointment (id_appointment, for_date, for_hour, patient_info, created_at) SELECT id_appointment, for_date, for_hour, patient_info, created_at FROM __temp__appointment');
        $this->addSql('DROP TABLE __temp__appointment');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__appointment AS SELECT id_appointment, for_date, for_hour, patient_info, created_at FROM appointment');
        $this->addSql('DROP TABLE appointment');
        $this->addSql('CREATE TABLE appointment (id_appointment INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, created_at CLOB NOT NULL, for_date CLOB NOT NULL COLLATE BINARY, for_hour CLOB NOT NULL COLLATE BINARY, patient_info CLOB DEFAULT NULL COLLATE BINARY)');
        $this->addSql('INSERT INTO appointment (id_appointment, for_date, for_hour, patient_info, created_at) SELECT id_appointment, for_date, for_hour, patient_info, created_at FROM __temp__appointment');
        $this->addSql('DROP TABLE __temp__appointment');
        $this->addSql('CREATE UNIQUE INDEX date_hour_unique ON appointment (for_date, for_hour)');
    }
}
