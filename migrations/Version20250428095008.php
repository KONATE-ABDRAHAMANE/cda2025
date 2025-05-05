<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250428095008 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE commande ADD client_id INT NOT NULL, ADD adresse_id INT DEFAULT NULL, ADD employe_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D4DE7DC5C FOREIGN KEY (adresse_id) REFERENCES adresse (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D1B65292 FOREIGN KEY (employe_id) REFERENCES employe (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6EEAA67D19EB6921 ON commande (client_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6EEAA67D4DE7DC5C ON commande (adresse_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6EEAA67D1B65292 ON commande (employe_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ligne_commande ADD commande_id INT NOT NULL, ADD produit_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ligne_commande ADD CONSTRAINT FK_3170B74B82EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ligne_commande ADD CONSTRAINT FK_3170B74BF347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3170B74B82EA2E54 ON ligne_commande (commande_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3170B74BF347EFB ON ligne_commande (produit_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE produit ADD categorie_id INT DEFAULT NULL, ADD promotion_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27139DF194 FOREIGN KEY (promotion_id) REFERENCES promotion (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_29A5EC27BCF5E72D ON produit (categorie_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_29A5EC27139DF194 ON produit (promotion_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67D19EB6921
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67D4DE7DC5C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67D1B65292
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_6EEAA67D19EB6921 ON commande
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_6EEAA67D4DE7DC5C ON commande
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_6EEAA67D1B65292 ON commande
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande DROP client_id, DROP adresse_id, DROP employe_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ligne_commande DROP FOREIGN KEY FK_3170B74B82EA2E54
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ligne_commande DROP FOREIGN KEY FK_3170B74BF347EFB
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_3170B74B82EA2E54 ON ligne_commande
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_3170B74BF347EFB ON ligne_commande
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ligne_commande DROP commande_id, DROP produit_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27BCF5E72D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27139DF194
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_29A5EC27BCF5E72D ON produit
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_29A5EC27139DF194 ON produit
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE produit DROP categorie_id, DROP promotion_id
        SQL);
    }
}
