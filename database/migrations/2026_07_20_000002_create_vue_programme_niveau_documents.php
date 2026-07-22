<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS vue_programme_niveaux');
        DB::statement('DROP VIEW IF EXISTS vue_programme_niveau_documents');

        DB::statement(<<<'SQL'
            CREATE VIEW vue_programme_niveau_documents AS
            SELECT
                p.id AS programme_id,
                p.nom AS programme_nom,
                pn.id AS programme_niveau_id,
                n.code AS niveau_code,
                n.libelle AS niveau_libelle,
                td.id AS type_document_id,
                td.code AS document_code,
                td.libelle AS document_libelle,
                pntd.obligatoire,
                pntd.ordre
            FROM programme_niveau_type_document AS pntd
            INNER JOIN programme_niveaux AS pn
                ON pn.id = pntd.programme_niveau_id
            INNER JOIN niveaux AS n
                ON n.id = pn.niveau_id
            INNER JOIN programmes AS p
                ON p.id = pn.programme_id
            INNER JOIN types_documents AS td
                ON td.id = pntd.type_document_id
            SQL);

        DB::statement(<<<'SQL'
            CREATE VIEW vue_programme_niveaux AS
            SELECT
                pn.id AS association_id,
                p.id AS programme_id,
                p.nom AS programme_nom,
                p.slug AS programme_slug,
                n.id AS niveau_id,
                n.code AS niveau_code,
                n.libelle AS niveau_libelle,
                pn.ordre,
                pn.actif
            FROM programme_niveaux AS pn
            INNER JOIN programmes AS p ON p.id = pn.programme_id
            INNER JOIN niveaux AS n ON n.id = pn.niveau_id
            SQL);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS vue_programme_niveaux');
        DB::statement('DROP VIEW IF EXISTS vue_programme_niveau_documents');
    }
};
